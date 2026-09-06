<?php

namespace App\Services\License;

use App\Models\SiteSetting;
use App\Services\License\ValueObjects\TokenClaims;

class LicenseStateService
{
    private const SETTING_KEY = 'license_state';

    private ?array $memoryCache = null;

    /**
     * Retrieve the stored raw license state from persistent database storage.
     *
     * @return array{
     *     status?: string,
     *     domain?: string,
     *     key_masked?: string,
     *     token?: string,
     *     jti?: string,
     *     token_expires_at?: int,
     *     license_expires_at?: ?int,
     *     customer?: string|array|null,
     *     activated_at?: string,
     *     last_verified_at?: string
     * }|null
     */
    public function getState(): ?array
    {
        if ($this->memoryCache !== null) {
            return $this->memoryCache;
        }

        try {
            $state = SiteSetting::get(self::SETTING_KEY, null);
            if (is_array($state) && !empty($state['token'])) {
                $this->memoryCache = $state;
                return $state;
            }
        } catch (\Throwable $e) {
            // Fault-tolerant fallback if database table is unavailable during bootstrap
            return null;
        }

        return null;
    }

    /**
     * Check if a license has been activated and stored in persistent storage.
     */
    public function isActivated(): bool
    {
        $state = $this->getState();
        return $state !== null && !empty($state['token']);
    }

    /**
     * Check if license state is currently ACTIVE.
     */
    public function isActive(): bool
    {
        $state = $this->getState();
        return $state !== null && ($state['status'] ?? '') === 'ACTIVE';
    }

    /**
     * Check if license is explicitly SUSPENDED.
     */
    public function isSuspended(): bool
    {
        $state = $this->getState();
        return $state !== null && ($state['status'] ?? '') === 'SUSPENDED';
    }

    /**
     * Check if license is explicitly REVOKED (terminal state).
     */
    public function isRevoked(): bool
    {
        $state = $this->getState();
        return $state !== null && ($state['status'] ?? '') === 'REVOKED';
    }

    /**
     * Check if license is explicitly EXPIRED.
     */
    public function isExpired(): bool
    {
        $state = $this->getState();
        return $state !== null && ($state['status'] ?? '') === 'EXPIRED';
    }

    /**
     * Save successful activation state atomically to persistent storage.
     *
     * @param TokenClaims $claims Validated token claims
     * @param string $tokenString Compact 3-part signed CLS-LIC-V1 token string
     * @param string|null $activatedAt ISO-8601 UTC timestamp of activation
     */
    public function saveActivationState(TokenClaims $claims, string $tokenString, ?string $activatedAt = null): void
    {
        $timestamp = $activatedAt ?? gmdate('Y-m-d\TH:i:s\Z');

        $state = [
            'status' => 'ACTIVE',
            'domain' => $claims->dom,
            'key_masked' => $claims->sub,
            'token' => $tokenString,
            'jti' => $claims->jti,
            'token_expires_at' => $claims->exp,
            'license_expires_at' => $claims->licExp,
            'customer' => $claims->customer,
            'activated_at' => $timestamp,
            'last_verified_at' => $timestamp,
        ];

        SiteSetting::set(self::SETTING_KEY, $state);
        $this->memoryCache = $state;
    }

    /**
     * Update verified token and rolling refresh state in persistent storage.
     *
     * @param TokenClaims $claims
     * @param string $tokenString
     */
    public function updateVerificationState(TokenClaims $claims, string $tokenString): void
    {
        $existing = $this->getState() ?? [];
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        $state = array_merge($existing, [
            'status' => 'ACTIVE',
            'domain' => $claims->dom,
            'key_masked' => $claims->sub,
            'token' => $tokenString,
            'jti' => $claims->jti,
            'token_expires_at' => $claims->exp,
            'license_expires_at' => $claims->licExp,
            'customer' => $claims->customer,
            'last_verified_at' => $timestamp,
        ]);

        SiteSetting::set(self::SETTING_KEY, $state);
        $this->memoryCache = $state;
    }

    /**
     * Touch verification timestamp without mutating token.
     */
    public function touchVerified(): void
    {
        $existing = $this->getState();
        if ($existing === null) {
            return;
        }

        $existing['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z');
        SiteSetting::set(self::SETTING_KEY, $existing);
        $this->memoryCache = $existing;
    }

    /**
     * Mark local state as SUSPENDED.
     */
    public function markSuspended(): void
    {
        $existing = $this->getState() ?? [];
        $existing['status'] = 'SUSPENDED';
        $existing['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z');

        SiteSetting::set(self::SETTING_KEY, $existing);
        $this->memoryCache = $existing;
    }

    /**
     * Mark local state as REVOKED (terminal state).
     */
    public function markRevoked(): void
    {
        $existing = $this->getState() ?? [];
        $existing['status'] = 'REVOKED';
        $existing['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z');

        SiteSetting::set(self::SETTING_KEY, $existing);
        $this->memoryCache = $existing;
    }

    /**
     * Mark local state as EXPIRED.
     */
    public function markExpired(): void
    {
        $existing = $this->getState() ?? [];
        $existing['status'] = 'EXPIRED';
        $existing['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z');

        SiteSetting::set(self::SETTING_KEY, $existing);
        $this->memoryCache = $existing;
    }

    /**
     * Clear all stored license state from storage and memory cache.
     */
    public function clear(): void
    {
        SiteSetting::where('key', self::SETTING_KEY)->delete();
        $this->memoryCache = null;
    }
}