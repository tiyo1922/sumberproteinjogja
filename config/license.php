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
];