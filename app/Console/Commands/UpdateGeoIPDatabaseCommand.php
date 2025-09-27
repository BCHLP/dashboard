<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GeoIpService;

class UpdateGeoIPDatabaseCommand extends Command
{
    protected $signature = 'geoip {--check} {--update}';
    protected $description = 'Update the local GeoIP database';

    public function handle(GeoIpService $geoipService)
    {

        if ($this->option('check')) {
            $this->checkIfUpdateRequired($geoipService);
            return;
        }

        if ($this->option('update')) {
            $this->update($geoipService);
            return;
        }

        $this->info("Please add --check or --update");
    }

    private function update(GeoIpService $geoipService) {
        $this->info('Updating GeoIP database...');

        if ($geoipService->updateDatabase(false)) {
            $this->info('GeoIP database updated successfully');
        } else {
            $this->error('Failed to update GeoIP database');
            return 1;
        }

        return 0;
    }

    private function checkIfUpdateRequired(GeoIpService $geoipService) {
        $info = $geoipService->getDatabaseInfo();

        if (!$info['exists']) {
            $this->error('GeoIP database not found');
            $this->info('Run "php artisan geoip:update" to download the database');
            return;
        }

        $this->info('GeoIP Database Status:');
        $this->line('Size: ' . number_format($info['size'] / 1024 / 1024, 2) . ' MB');
        $this->line('Last Modified: ' . date('Y-m-d H:i:s', $info['last_modified']));
        $this->line('Age: ' . number_format($info['age_days'], 1) . ' days');

        if ($info['age_days'] > 30) {
            $this->warn('Database is older than 30 days. Consider updating.');
        } else {
            $this->info('Database is current');
        }
    }
}
