<?php
declare(strict_types=1);

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpService
{
    protected $reader;
    protected $databasePath;

    public function __construct()
    {
        $this->databasePath = storage_path('app/geoip/GeoLite2-City.mmdb');
        $this->initializeReader();
    }

    /**
     * Initialize the GeoIP2 reader
     */
    protected function initializeReader()
    {
        if (file_exists($this->databasePath)) {
            try {
                $this->reader = new Reader($this->databasePath);
            } catch (\Exception $e) {
                Log::error('Failed to initialize GeoIP reader: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get location data for an IP address
     */
    public function getLocation(string $ip): ?array
    {
        // Skip local/private IPs
        if ($this->isPrivateIP($ip)) {
            return null;
        }

        if (!$this->reader) {
            return $this->fallbackToFreeAPI($ip);
        }

        try {
            $record = $this->reader->city($ip);

            return [
                'country' => $record->country->name,
                'country_code' => $record->country->isoCode,
                'region' => $record->mostSpecificSubdivision->name,
                'region_code' => $record->mostSpecificSubdivision->isoCode,
                'city' => $record->city->name,
                'postal_code' => $record->postal->code,
                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude,
                'timezone' => $record->location->timeZone,
                'accuracy_radius' => $record->location->accuracyRadius,
                'source' => 'local_database'
            ];
        } catch (AddressNotFoundException $e) {
            return $this->fallbackToFreeAPI($ip);
        } catch (\Exception $e) {
            Log::error('GeoIP lookup failed: ' . $e->getMessage(), ['ip' => $ip]);
            return null;
        }
    }

    /**
     * Fallback to free API services
     */
    protected function fallbackToFreeAPI(string $ip): ?array
    {
        // Try ip-api.com (free, no API key required, 1000 requests/month)
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'region_code' => $data['region'] ?? null,
                    'city' => $data['city'] ?? null,
                    'postal_code' => $data['zip'] ?? null,
                    'latitude' => $data['lat'] ?? null,
                    'longitude' => $data['lon'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'isp' => $data['isp'] ?? null,
                    'source' => 'ip-api'
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Free API fallback failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if IP is private/local
     */
    protected function isPrivateIP(string $ip): bool
    {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Download and update the GeoLite2 database
     */
    public function updateDatabase($force=true): bool
    {
        ray("update database");
        try {
            // You need to register for a free MaxMind account to get a license key
            $licenseKey = config('services.maxmind.license_key');

            if (!$licenseKey) {
                Log::error('MaxMind license key not configured');
                return false;
            }

            $tarPath = storage_path('app/geoip/geolite2-city.tar.gz');

            ray(" tar path = $tarPath");

            if (!file_exists($tarPath) || $force) {

                $url = "https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key={$licenseKey}&suffix=tar.gz";

                // Download the database
                $response = Http::timeout(120)->get($url);

                if (!$response->successful()) {
                    Log::error('Failed to download GeoIP database');
                    return false;
                }


                // Create directory if it doesn't exist
                $geoipDir = storage_path('app/geoip');
                if (!is_dir($geoipDir)) {
                    mkdir($geoipDir, 0755, true);
                }

                // Save the tar.gz file

                file_put_contents($tarPath, $response->body());

                ray("i put contents");
            }

            ray("start extracing");

            // Extract the database file
            $this->extractDatabase($tarPath);

            Log::info('GeoIP database updated successfully');
            return true;

        } catch (\Exception $e) {
            ray($e);
            Log::error('Failed to update GeoIP database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract the .mmdb file from the tar.gz archive
     */
    protected function extractDatabase(string $tarPath): void
    {

        ray("create phar data");
        $phar = new \PharData($tarPath);
        ray("generate storage path");
        $extractPath = storage_path('app/geoip/temp');

        ray("extractDatabase to $extractPath");

        // Extract to temp directory
        $phar->extractTo($extractPath);

        // Find the .mmdb file in the extracted directory
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath)
        );

        ray("about to iterate over files");
        foreach ($iterator as $file) {
            ray('Extracting ' . $file->getFilename());
            if ($file->getExtension() === 'mmdb') {
                ray("found mmdb file");
                // Move the .mmdb file to the final location
                rename($file->getPathname(), $this->databasePath);
                break;
            }
        }
        ray("finished");

        // Clean up
        $this->deleteDirectory($extractPath);
        unlink($tarPath);

        // Reinitialize reader with new database
        $this->initializeReader();
    }

    /**
     * Recursively delete a directory
     */
    protected function deleteDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object)) {
                        $this->deleteDirectory($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Check if database exists and is not too old
     */
    public function isDatabaseCurrent(): bool
    {
        if (!file_exists($this->databasePath)) {
            return false;
        }

        // Check if database is older than 30 days
        $fileAge = time() - filemtime($this->databasePath);
        return $fileAge < (30 * 24 * 60 * 60); // 30 days in seconds
    }

    /**
     * Get database information
     */
    public function getDatabaseInfo(): array
    {
        if (!file_exists($this->databasePath)) {
            return ['exists' => false];
        }

        return [
            'exists' => true,
            'size' => filesize($this->databasePath),
            'last_modified' => filemtime($this->databasePath),
            'age_days' => (time() - filemtime($this->databasePath)) / (24 * 60 * 60)
        ];
    }
}
