<?php

$trustedKeysJson = env('LICENSE_TRUSTED_PUBLIC_KEYS');
$trustedKeys = null;

if (is_string($trustedKeysJson) && trim($trustedKeysJson) !== '') {
    $decoded = json_decode($trustedKeysJson, true);
    if (is_array($decoded) && !empty($decoded)) {
        $trustedKeys = $decoded;
    }
}

$defaultKeyId = (string) env('LICENSE_SERVER_KEY_ID', 'cls-ed25519-2026-v1');
$defaultPubKey = env('LICENSE_SERVER_PUBLIC_KEY');

if ($trustedKeys === null && is_string($defaultPubKey) && trim($defaultPubKey) !== '') {
    $trustedKeys = [$defaultKeyId => trim($defaultPubKey)];
}

return [
    /*
    |--------------------------------------------------------------------------
    | Central License Server URL
    |--------------------------------------------------------------------------
    */
    'server_url' => env('LICENSE_SERVER_URL', 'https://license.katresnanku.com'),

    /*
    |--------------------------------------------------------------------------
    | Application Code
    |--------------------------------------------------------------------------
    | Fixed immutable identity registered on the Central License Server.
    */
    'app_code' => env('LICENSE_APP_CODE', 'SPJ22'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials (Server-Side Only)
    |--------------------------------------------------------------------------
    */
    'api_key_id' => env('LICENSE_API_KEY_ID'),
    'api_secret' => env('LICENSE_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Server Verification Public Key & Key ID (Ed25519)
    |--------------------------------------------------------------------------
    */
    'server_public_key' => $defaultPubKey,
    'server_key_id' => $defaultKeyId,

    /*
    |--------------------------------------------------------------------------
    | Trusted Public Keys Registry (Multi-KID Key Rotation Support)
    |--------------------------------------------------------------------------
    | Map of [key_id => base64_public_key].
    */
    'trusted_public_keys' => $trustedKeys ?? [],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Settings
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('LICENSE_HTTP_TIMEOUT', 5),
    'retry' => (int) env('LICENSE_HTTP_RETRY', 2),

    /*
    |--------------------------------------------------------------------------
    | Rolling Token Refresh — Threshold & Interval
    |--------------------------------------------------------------------------
    | refresh_threshold_percentage: fraction of token TTL remaining that
    |   triggers a proactive online refresh (0.30 = 30% remaining TTL).
    |
    | refresh_check_interval_seconds: minimum wall-clock seconds between
    |   consecutive online refresh attempts to avoid thundering-herd on
    |   every HTTP request (default: 3600 = 1 hour).
    */
    'refresh_threshold_percentage' => (float) env('LICENSE_REFRESH_THRESHOLD_PERCENTAGE', 0.30),
    'refresh_check_interval_seconds' => (int) env('LICENSE_REFRESH_CHECK_INTERVAL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Token-Expired Grace Period (Network-Error Fallback)
    |--------------------------------------------------------------------------
    | When a short-lived token is expired and the central server is
    | unreachable (network error / 5xx), the middleware allows the request
    | to continue for this many seconds past token expiry before blocking.
    | License lifetime (lic_exp) is unaffected — it is always authoritative.
    | Set to 0 to disable grace period.
    */
    'token_expired_grace_period_seconds' => (int) env('LICENSE_TOKEN_EXPIRED_GRACE_PERIOD', 86400),
];