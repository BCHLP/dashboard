<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use DateTimeZone;

class TotpService
{
    private string $secretKey;

    private int $digits;

    private int $timeStepSeconds;

    private string $hashAlgorithm;

    public function __construct(
        string $secretOrBytes,
        int $digits = 6,
        int $timeStepSeconds = 30,
        string $hashAlgorithm = 'SHA1',
        bool $isBase32 = true
    ) {
        $this->secretKey = $isBase32 ? self::base32Decode($secretOrBytes) : $secretOrBytes;
        $this->digits = $digits;
        $this->timeStepSeconds = $timeStepSeconds;
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * Create authenticator from base32 secret
     */
    public static function fromBase32(
        string $base32Secret,
        int $digits = 6,
        int $timeStepSeconds = 30,
        string $hashAlgorithm = 'SHA1'
    ): self {
        return new self($base32Secret, $digits, $timeStepSeconds, $hashAlgorithm, true);
    }

    /**
     * Create authenticator from raw bytes
     */
    public static function fromBytes(
        string $secretKey,
        int $digits = 6,
        int $timeStepSeconds = 30,
        string $hashAlgorithm = 'SHA1'
    ): self {
        return new self($secretKey, $digits, $timeStepSeconds, $hashAlgorithm, false);
    }

    /**
     * Generate TOTP code for current time
     */
    public function generateCode(?DateTime $timestamp = null): string
    {
        $timestamp = $timestamp ?? new DateTime('now', new DateTimeZone('UTC'));
        $timeCounter = $this->getTimeCounter($timestamp);

        return $this->generateCodeFromCounter($timeCounter);
    }

    /**
     * Validate TOTP code with tolerance for time drift
     */
    public function validateCode(string $code, ?DateTime $timestamp = null, int $toleranceSteps = 1): bool
    {
        $timestamp = $timestamp ?? new DateTime('now', new DateTimeZone('UTC'));
        $baseTimeCounter = $this->getTimeCounter($timestamp);

        // Check current time and surrounding time steps for clock drift tolerance
        for ($i = -$toleranceSteps; $i <= $toleranceSteps; $i++) {
            $testCounter = $baseTimeCounter + $i;
            $expectedCode = $this->generateCodeFromCounter($testCounter);

            if ($code === $expectedCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get remaining seconds until next code generation
     */
    public function getRemainingSeconds(): int
    {
        $currentTime = new DateTime('now', new DateTimeZone('UTC'));
        $timeCounter = $this->getTimeCounter($currentTime);
        $nextTimeStep = ($timeCounter + 1) * $this->timeStepSeconds;
        $currentTimestamp = $currentTime->getTimestamp();

        return $nextTimeStep - $currentTimestamp;
    }

    private function getTimeCounter(DateTime $timestamp): int
    {
        $unixTimestamp = $timestamp->getTimestamp();

        return intval(floor($unixTimestamp / $this->timeStepSeconds));
    }

    private function generateCodeFromCounter(int $counter): string
    {
        // Convert counter to 8-byte big-endian representation
        $counterBytes = pack('J', $counter);

        // Compute HMAC hash
        $hash = hash_hmac(
            strtolower($this->hashAlgorithm),
            $counterBytes,
            $this->secretKey,
            true
        );

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $binaryCode = ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF);

        $otp = $binaryCode % pow(10, $this->digits);

        return str_pad((string) $otp, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random Base32 secret for device pairing
     */
    public static function generateSecret(int $length = 32): string
    {
        $randomBytes = random_bytes($length);

        return self::base32Encode($randomBytes);
    }

    /**
     * Generate QR code URI for manual device setup
     */
    public function getQRCodeUri(string $issuer, string $account): string
    {
        $secret = self::base32Encode($this->secretKey);

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            urlencode($issuer),
            urlencode($account),
            $secret,
            urlencode($issuer),
            $this->hashAlgorithm,
            $this->digits,
            $this->timeStepSeconds
        );
    }

    /**
     * Base32 encode binary data
     */
    private static function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $index = ($buffer >> ($bitsLeft - 5)) & 0x1F;
                $result .= $alphabet[$index];
                $bitsLeft -= 5;
            }
        }

        if ($bitsLeft > 0) {
            $index = ($buffer << (5 - $bitsLeft)) & 0x1F;
            $result .= $alphabet[$index];
        }

        return $result;
    }

    /**
     * Base32 decode to binary data
     */
    private static function base32Decode(string $encoded): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = strtoupper(str_replace([' ', '-'], '', $encoded));

        $result = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($encoded); $i++) {
            $value = strpos($alphabet, $encoded[$i]);
            if ($value === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $value;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $result .= chr($buffer >> ($bitsLeft - 8));
                $bitsLeft -= 8;
            }
        }

        return $result;
    }
}
