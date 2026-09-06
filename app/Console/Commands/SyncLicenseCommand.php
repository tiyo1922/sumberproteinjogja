<?php

namespace App\Console\Commands;

use App\Services\License\Exceptions\LicenseNetworkException;
use App\Services\License\Exceptions\LicenseVerificationException;
use App\Services\License\LicenseClientService;
use App\Services\License\LicenseStateService;
use Illuminate\Console\Command;
use Throwable;

class SyncLicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:sync {--force : Force synchronization even if recently verified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and verify local license state with Central License Server.';

    /**
     * Execute the console command.
     */
    public function handle(
        LicenseClientService $clientService,
        LicenseStateService $stateService
    ): int {
        $this->info('[License Sync] Starting operational license synchronization...');

        if (! $stateService->isActivated()) {
            $this->warn('[License Sync] No active license found on this installation. Skipping synchronization.');
            return self::SUCCESS;
        }

        if ($stateService->isRevoked()) {
            $this->error('[License Sync] License has been permanently REVOKED. Online synchronization aborted.');
            return self::FAILURE;
        }

        try {
            $result = $clientService->verifyOnline();

            if (!empty($result['refreshed'])) {
                $this->info("[License Sync] SUCCESS: License verified and rolling token refreshed. (JTI: {$result['token_id']})");
            } else {
                $this->info('[License Sync] SUCCESS: License verified online. Local token is active and valid.');
            }

            return self::SUCCESS;
        } catch (LicenseNetworkException $e) {
            $this->warn("[License Sync] NETWORK WARNING: {$e->getMessage()}");
            $this->line('[License Sync] Local license state preserved.');
            return self::SUCCESS;
        } catch (LicenseVerificationException $e) {
            $this->error("[License Sync] VERIFICATION FAILED: {$e->getMessage()} (Code: {$e->getErrorCode()})");
            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error('[License Sync] UNEXPECTED ERROR: An error occurred during license synchronization.');
            return self::FAILURE;
        }
    }
}