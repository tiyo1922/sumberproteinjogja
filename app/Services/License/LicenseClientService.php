<?php

namespace App\Services\License;

use App\Services\License\Exceptions\LicenseActivationException;
use App\Services\License\Exceptions\LicenseNetworkException;
use App\Services\License\Exceptions\LicenseVerificationException;
use Illuminate\Support\Facades\Http;
use Throwable;

class LicenseClientService
{
    private Ed25519TokenVerifier $verifier;
    private LicenseStateService $stateService;
    private string $serverUrl;
    private string $apiKeyId;
    private string $apiSecret;
    private int $timeout;
    private int $retry;

    public function __construct(
        ?Ed25519TokenVerifier $verifier = null,
        ?LicenseStateService $stateService = null,
        ?string $serverUrl = null,
        ?string $apiKeyId = null,
        ?string $apiSecret = null,
        ?int $timeout = null,
        ?int $retry = null
    ) {
        $this->verifier = $verifier ?? app(Ed25519TokenVerifier::class);
        $this->stateService = $stateService ?? app(LicenseStateService::class);
        $this->serverUrl = $serverUrl ?? (string) config('license.server_url', 'https://license.katresnanku.com');
        $this->apiKeyId = $apiKeyId ?? (string) config('license.api_key_id', '');
        $this->apiSecret = $apiSecret ?? (string) config('license.api_secret', '');
        $this->timeout = $timeout ?? (int) config('license.timeout', 5);
        $this->retry = $retry ?? (int) config('license.retry', 0);
    }

    /**
     * Activate a license key for the canonical domain against Central License Server.
     *
     * @param string $licenseKey User-supplied activation code
     * @param string $rawDomain Raw or canonical client domain
     * @return array{
     *     success: bool,
     *     status: string,
     *     domain: string,
     *     key_masked: string,
     *     customer: string|array|null,
     *     expires_at: ?int,
     *     token_expires_at: int,
     *     jti: string
     * }
     *
     * @throws LicenseActivationException
     */
    public function activate(string $licenseKey, string $rawDomain): array
    {
        $cleanKey = trim($licenseKey);
        if ($cleanKey === '') {
            throw new LicenseActivationException('Kode lisensi tidak boleh kosong.', 'INVALID_LICENSE_KEY', 422);
        }

        $canonicalDomain = DomainCanonicalizer::canonicalize($rawDomain);
        $endpoint = rtrim($this->serverUrl, '/') . '/api/v1/license/activate';

        $httpClient = Http::timeout($this->timeout)->acceptJson()->withHeaders([
            'X-Api-Key-Id' => $this->apiKeyId,
            'X-Api-Secret' => $this->apiSecret,
        ]);

        if ($this->retry > 0) {
            $httpClient = $httpClient->retry($this->retry, 100, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            });
        }

        try {
            $response = $httpClient->post($endpoint, [
                'license_key' => $cleanKey,
                'domain' => $canonicalDomain,
            ]);
        } catch (Throwable $e) {
            throw new LicenseActivationException(
                'Gagal terhubung ke Central License Server. Silakan periksa koneksi internet Anda atau coba sesaat lagi.',
                'NETWORK_ERROR',
                503,
                $e
            );
        }

        // Handle error responses from Central Server
        if ($response->failed()) {
            $status = $response->status();
            $data = $response->json();
            $errorCode = is_array($data) ? ($data['error']['code'] ?? null) : null;

            $message = $this->mapErrorMessage($status, $errorCode);

            throw new LicenseActivationException($message, $errorCode ?? 'ACTIVATION_FAILED', $status);
        }

        $data = $response->json();
        if (!is_array($data) || empty($data['success']) || empty($data['data']['token'])) {
            throw new LicenseActivationException(
                'Respon dari Central License Server tidak valid.',
                'MALFORMED_SERVER_RESPONSE',
                502
            );
        }

        $tokenString = (string) $data['data']['token'];

        // Perform strict local Ed25519 cryptographic & claim verification
        try {
            $claims = $this->verifier->verify($tokenString, $canonicalDomain);
        } catch (Throwable $e) {
            // NEVER save state if local cryptographic verification fails
            throw new LicenseActivationException(
                'Verifikasi kriptografi token lisensi gagal. Token tidak sah.',
                'CRYPTO_VERIFICATION_FAILED',
                422,
                $e
            );
        }

        // Persist local state only after successful cryptographic verification
        $this->stateService->saveActivationState($claims, $tokenString);

        return [
            'success' => true,
            'status' => 'ACTIVE',
            'domain' => $canonicalDomain,
            'key_masked' => $claims->sub,
            'customer' => $claims->customer,
            'expires_at' => $claims->licExp,
            'token_expires_at' => $claims->exp,
            'jti' => $claims->jti,
        ];
    }

    /**
     * Perform online verification and rolling refresh synchronization with Central Server.
     *
     * @param string|null $rawToken Optional explicit token (defaults to stored local token)
     * @param string|null $rawDomain Optional explicit domain (defaults to stored canonical domain)
     * @return array{
     *     success: bool,
     *     status: string,
     *     domain: string,
     *     refreshed: bool,
     *     token_id: ?string,
     *     token_expires_at: ?string,
     *     expires_at: ?string,
     *     server_time: ?string
     * }
     *
     * @throws LicenseVerificationException
     * @throws LicenseNetworkException
     */
    public function verifyOnline(?string $rawToken = null, ?string $rawDomain = null): array
    {
        $token = $rawToken;
        $domain = $rawDomain;

        if ($token === null || $domain === null) {
            $state = $this->stateService->getState();
            if ($state === null || empty($state['token'])) {
                throw new LicenseVerificationException('Lisensi belum diaktivasi pada instalasi ini.', 'UNACTIVATED', 400);
            }

            $token = $token ?? (string) $state['token'];
            $domain = $domain ?? (string) ($state['domain'] ?? 'localhost');
        }

        $canonicalDomain = DomainCanonicalizer::canonicalize($domain);
        $endpoint = rtrim($this->serverUrl, '/') . '/api/v1/license/verify';

        $httpClient = Http::timeout($this->timeout)->acceptJson()->withHeaders([
            'X-Api-Key-Id' => $this->apiKeyId,
            'X-Api-Secret' => $this->apiSecret,
        ]);

        if ($this->retry > 0) {
            $httpClient = $httpClient->retry($this->retry, 100, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            });
        }

        try {
            $response = $httpClient->post($endpoint, [
                'token' => $token,
                'domain' => $canonicalDomain,
            ]);
        } catch (Throwable $e) {
            // NEVER clear or destroy active local state upon network failure
            throw new LicenseNetworkException(
                'Gagal terhubung ke Central License Server. Pemeriksaan lisensi ditunda.',
                503,
                $e
            );
        }

        // Handle error responses from Central Server
        if ($response->failed()) {
            $status = $response->status();
            $data = $response->json();
            $errorCode = is_array($data) ? ($data['error']['code'] ?? null) : null;

            // Handle lifecycle state transitions
            if ($status === 403) {
                if ($errorCode === 'LICENSE_SUSPENDED') {
                    $this->stateService->markSuspended();
                    throw new LicenseVerificationException('Lisensi ini sedang ditangguhkan. Silakan hubungi administrator.', 'LICENSE_SUSPENDED', 403);
                }

                if ($errorCode === 'LICENSE_REVOKED') {
                    $this->stateService->markRevoked();
                    throw new LicenseVerificationException('Lisensi ini telah dicabut permanen.', 'LICENSE_REVOKED', 403);
                }

                if ($errorCode === 'LICENSE_EXPIRED') {
                    $this->stateService->markExpired();
                    throw new LicenseVerificationException('Masa berlaku lisensi ini telah berakhir.', 'LICENSE_EXPIRED', 403);
                }
            }

            if ($status === 401) {
                throw new LicenseVerificationException('Autentikasi integrasi lisensi gagal. Periksa konfigurasi API server.', 'UNAUTHORIZED', 401);
            }

            if ($status === 429) {
                throw new LicenseVerificationException('Terlalu banyak permintaan verifikasi. Silakan tunggu beberapa saat.', 'RATE_LIMITED', 429);
            }

            if ($status >= 500) {
                // Central 5xx error: do not destroy local state
                throw new LicenseNetworkException('Central License Server sedang mengalami gangguan. Silakan coba kembali sesaat lagi.', $status);
            }

            $message = $this->mapErrorMessage($status, $errorCode);
            throw new LicenseVerificationException($message, $errorCode ?? 'VERIFICATION_FAILED', $status);
        }

        $data = $response->json();
        if (!is_array($data) || empty($data['success']) || !isset($data['data']['valid']) || !$data['data']['valid']) {
            throw new LicenseVerificationException('Respon verifikasi dari Central License Server tidak valid.', 'MALFORMED_SERVER_RESPONSE', 502);
        }

        $payload = $data['data'];
        $isRefreshed = !empty($payload['refreshed']);

        if ($isRefreshed && !empty($payload['token'])) {
            $newTokenString = (string) $payload['token'];

            // Cryptographically verify the refreshed token locally BEFORE mutating persistent state
            try {
                $newClaims = $this->verifier->verify($newTokenString, $canonicalDomain);
            } catch (Throwable $e) {
                throw new LicenseVerificationException('Verifikasi kriptografi token refreshed gagal. Token tidak sah.', 'CRYPTO_VERIFICATION_FAILED', 422, $e);
            }

            // Atomically replace local state with new token generation
            $this->stateService->updateVerificationState($newClaims, $newTokenString);
        } else {
            // Token verified without refresh: touch last_verified_at
            $this->stateService->touchVerified();
        }

        return [
            'success' => true,
            'status' => 'ACTIVE',
            'domain' => $canonicalDomain,
            'refreshed' => $isRefreshed,
            'token_id' => $payload['token_id'] ?? null,
            'token_expires_at' => $payload['token_expires_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'server_time' => $payload['server_time'] ?? null,
        ];
    }

    /**
     * Map HTTP status codes and central error codes to safe, generic Indonesian messages.
     */
    private function mapErrorMessage(int $status, ?string $errorCode): string
    {
        if ($status === 401) {
            return 'Autentikasi integrasi lisensi gagal. Periksa konfigurasi API server.';
        }

        if ($status === 429) {
            return 'Terlalu banyak percobaan aktivasi. Silakan tunggu beberapa saat sebelum mencoba kembali.';
        }

        return match ($errorCode) {
            'LICENSE_NOT_FOUND' => 'Kode lisensi tidak valid atau tidak ditemukan.',
            'LICENSE_ALREADY_BOUND', 'DOMAIN_MISMATCH' => 'Lisensi ini sudah terikat pada domain lain.',
            'LICENSE_SUSPENDED' => 'Lisensi ini sedang ditangguhkan. Silakan hubungi administrator.',
            'LICENSE_REVOKED' => 'Lisensi ini telah dicabut permanen.',
            'LICENSE_EXPIRED' => 'Masa berlaku lisensi ini telah berakhir.',
            'APPLICATION_INACTIVE' => 'Aplikasi lisensi tidak aktif pada sistem pusat.',
            'INVALID_LICENSE' => 'Format kode lisensi tidak valid.',
            default => $status >= 500
                ? 'Central License Server sedang mengalami gangguan. Silakan coba kembali sesaat lagi.'
                : 'Aktivasi lisensi gagal. Mohon periksa kembali kode lisensi Anda.',
        };
    }
}