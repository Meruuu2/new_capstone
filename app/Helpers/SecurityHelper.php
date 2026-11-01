<?php

/**
 * Security Helper Class for UA-FMS
 * 
 * Provides convenient methods for encryption, hashing, and token generation
 * using the security libraries installed in the project.
 * 
 * Available Libraries:
 * - defuse/php-encryption: Symmetric encryption
 * - web-token/jwt-core: JWT token generation
 * - phpseclib/phpseclib: Asymmetric encryption (RSA, etc.)
 * - hashids/hashids: ID obfuscation
 * - symfony/crypto: HMAC and random generation
 */

namespace App\Helpers;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use Hashids\Hashids;
use Illuminate\Support\Facades\Cache;

class SecurityHelper
{
    /**
     * Encrypt sensitive data using AES encryption
     * 
     * @param string $plaintext - Data to encrypt
     * @param string|null $keyString - Optional encryption key (uses app key by default)
     * @return string - Encrypted data (base64 encoded)
     * 
     * Usage: $encrypted = SecurityHelper::encrypt('sensitive data');
     */
    public static function encrypt(string $plaintext, ?string $keyString = null): string
    {
        try {
            $keyString = $keyString ?? base64_decode(env('APP_KEY'));
            $key = Key::loadFromAsciiSafeString($keyString);
            $encrypted = Crypto::encrypt($plaintext, $key);
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            \Log::error('Encryption failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Decrypt encrypted data
     * 
     * @param string $encryptedData - Base64 encoded encrypted data
     * @param string|null $keyString - Optional encryption key (uses app key by default)
     * @return string - Decrypted plaintext
     * 
     * Usage: $plaintext = SecurityHelper::decrypt($encrypted);
     */
    public static function decrypt(string $encryptedData, ?string $keyString = null): string
    {
        try {
            $keyString = $keyString ?? base64_decode(env('APP_KEY'));
            $key = Key::loadFromAsciiSafeString($keyString);
            $encrypted = base64_decode($encryptedData);
            return Crypto::decrypt($encrypted, $key);
        } catch (\Exception $e) {
            \Log::error('Decryption failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate an obfuscated ID using Hashids
     * 
     * @param int|array $ids - ID(s) to encode
     * @param string|null $salt - Optional salt for encoding (uses APP_KEY by default)
     * @return string - Obfuscated ID
     * 
     * Usage: $hashId = SecurityHelper::obfuscateId(123);
     */
    public static function obfuscateId($ids, ?string $salt = null): string
    {
        $salt = $salt ?? env('APP_KEY', '');
        $hashids = new Hashids($salt, 8); // 8 character minimum length
        return is_array($ids) ? $hashids->encode(...$ids) : $hashids->encode($ids);
    }

    /**
     * Decode an obfuscated ID back to its original value
     * 
     * @param string $hashId - Obfuscated ID
     * @param string|null $salt - Optional salt (must match encoding salt)
     * @return array - Decoded ID(s)
     * 
     * Usage: $ids = SecurityHelper::unobfuscateId('xY7zQ9');
     */
    public static function unobfuscateId(string $hashId, ?string $salt = null): array
    {
        $salt = $salt ?? env('APP_KEY', '');
        $hashids = new Hashids($salt, 8);
        return $hashids->decode($hashId);
    }

    /**
     * Generate a cryptographically secure random string
     * 
     * @param int $length - Length of the string
     * @return string - Random hex string
     * 
     * Usage: $token = SecurityHelper::generateRandomToken(32);
     */
    public static function generateRandomToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate a secure API key
     * 
     * @param int $length - Length of the key
     * @return string - Secure API key
     * 
     * Usage: $apiKey = SecurityHelper::generateApiKey();
     */
    public static function generateApiKey(int $length = 64): string
    {
        return 'UA-FMS-' . self::generateRandomToken($length - 7); // Reserve space for prefix
    }

    /**
     * Hash data using SHA-256 (useful for signatures, checksums)
     * 
     * @param string $data - Data to hash
     * @return string - SHA-256 hash
     * 
     * Usage: $hash = SecurityHelper::hashData('sensitive data');
     */
    public static function hashData(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Generate an HMAC for data integrity verification
     * 
     * @param string $data - Data to sign
     * @param string|null $key - Optional signing key (uses APP_KEY by default)
     * @return string - HMAC hex string
     * 
     * Usage: $hmac = SecurityHelper::generateHmac('api response data');
     */
    public static function generateHmac(string $data, ?string $key = null): string
    {
        $key = $key ?? env('APP_KEY', '');
        return hash_hmac('sha256', $data, $key);
    }

    /**
     * Verify an HMAC signature
     * 
     * @param string $data - Original data
     * @param string $hmac - HMAC to verify
     * @param string|null $key - Signing key (uses APP_KEY by default)
     * @return bool - True if HMAC is valid
     * 
     * Usage: $isValid = SecurityHelper::verifyHmac('data', $hmac);
     */
    public static function verifyHmac(string $data, string $hmac, ?string $key = null): bool
    {
        $key = $key ?? env('APP_KEY', '');
        $expectedHmac = hash_hmac('sha256', $data, $key);
        return hash_equals($expectedHmac, $hmac);
    }

    /**
     * Mask sensitive data for logging (shows only first and last 4 chars)
     * 
     * @param string $data - Data to mask
     * @return string - Masked data
     * 
     * Usage: $masked = SecurityHelper::maskSensitiveData('1234567890');
     */
    public static function maskSensitiveData(string $data): string
    {
        if (strlen($data) <= 8) {
            return '****';
        }
        return substr($data, 0, 4) . '***' . substr($data, -4);
    }
}
