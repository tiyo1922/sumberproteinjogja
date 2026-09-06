<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class CheckLicenseEnvironmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform local production readiness and preflight diagnostics for Central License Client (SPJ22).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->info('===============================================================');
        $this->info('  CENTRAL LICENSE CLIENT (SPJ22) - LOCAL PREFLIGHT DIAGNOSTICS');
        $this->info('===============================================================');
        $this->info('  (Read-only local system diagnostic. No external calls made.)');
        $this->info('');

        $hasFailure = false;
        $rows = [];

        // 1. PHP Version
        $phpVersion = PHP_VERSION;
        $phpPass = version_compare($phpVersion, '8.3.0', '>=');
        if (! $phpPass) {
            $hasFailure = true;
        }
        $rows[] = ['PHP Version', $phpVersion, $phpPass ? '<info>[PASS] >= 8.3</info>' : '<error>[FAIL] Requires PHP >= 8.3</error>'];

        // 2. Libsodium Extension
        $sodiumLoaded = extension_loaded('sodium');
        if (! $sodiumLoaded) {
            $hasFailure = true;
        }
        $rows[] = ['Sodium Extension (Ed25519)', $sodiumLoaded ? 'Loaded' : 'Missing', $sodiumLoaded ? '<info>[PASS]</info>' : '<error>[FAIL] ext-sodium required</error>'];

        // 3. SQLite Extensions
        $sqliteLoaded = extension_loaded('sqlite3') && extension_loaded('pdo_sqlite');
        if (! $sqliteLoaded) {
            $hasFailure = true;
        }
        $rows[] = ['SQLite3 & PDO Driver', $sqliteLoaded ? 'Loaded' : 'Missing', $sqliteLoaded ? '<info>[PASS]</info>' : '<error>[FAIL] sqlite3/pdo_sqlite required</error>'];

        // 4. Required Core Extensions
        $requiredExtensions = ['openssl', 'mbstring', 'json', 'curl', 'fileinfo', 'ctype', 'tokenizer', 'xml', 'bcmath'];
        $missingExt = [];
        foreach ($requiredExtensions as $ext) {
            if (! extension_loaded($ext)) {
                $missingExt[] = $ext;
            }
        }
        $extPass = empty($missingExt);
        if (! $extPass) {
            $hasFailure = true;
        }
        $rows[] = [
            'Core Extensions',
            $extPass ? 'All 9 present' : 'Missing: ' . implode(', ', $missingExt),
            $extPass ? '<info>[PASS]</info>' : '<error>[FAIL]</error>',
        ];

        // 5. Config: Server URL & App Code
        $serverUrl = (string) config('license.server_url');
        $appCode = (string) config('license.app_code');
        $urlPass = filter_var($serverUrl, FILTER_VALIDATE_URL) !== false;
        $appPass = ($appCode === 'SPJ22');
        if (! $urlPass || ! $appPass) {
            $hasFailure = true;
        }
        $rows[] = ['License Server URL', $serverUrl ?: '(empty)', $urlPass ? '<info>[PASS]</info>' : '<error>[FAIL] Invalid URL</error>'];
        $rows[] = ['Application Code', $appCode ?: '(empty)', $appPass ? '<info>[PASS] SPJ22</info>' : '<error>[FAIL] Expected SPJ22</error>'];

        // 6. Config: API Key ID & Secret Status (Secret is NEVER printed)
        $apiKeyId = (string) config('license.api_key_id');
        $apiSecret = (string) config('license.api_secret');
        $keyIdPass = ! empty($apiKeyId);
        $secretPass = ! empty($apiSecret);
        if (! $keyIdPass || ! $secretPass) {
            $hasFailure = true;
        }
        $maskedKeyId = strlen($apiKeyId) > 8 ? substr($apiKeyId, 0, 4) . '...' . substr($apiKeyId, -4) : ($apiKeyId ? 'Set' : '(empty)');
        $rows[] = ['API Key ID (X-Api-Key-Id)', $maskedKeyId, $keyIdPass ? '<info>[PASS] ' . (str_starts_with($apiKeyId, 'ak_') ? '(Format ak_)' : '(Set)') . '</info>' : '<error>[FAIL] Not set</error>'];
        $rows[] = ['API Secret (X-Api-Secret)', $secretPass ? 'Configured (Masked)' : 'Not set', $secretPass ? '<info>[PASS] Present (Hidden)</info>' : '<error>[FAIL] Not set</error>'];

        // 7. Config: Trusted Public Keys Registry
        $trustedKeys = config('license.trusted_public_keys');
        $keyCount = is_array($trustedKeys) ? count($trustedKeys) : 0;
        $registryPass = $keyCount > 0;
        if (! $registryPass) {
            $hasFailure = true;
        }
        $kidsList = is_array($trustedKeys) ? implode(', ', array_keys($trustedKeys)) : 'None';
        $rows[] = ['Trusted Public Key(s)', $registryPass ? "{$keyCount} key(s) [{$kidsList}]" : 'None configured', $registryPass ? '<info>[PASS]</info>' : '<error>[FAIL] No keys</error>'];

        // 8. Writable Directory Paths
        $storageWritable = is_writable(storage_path());
        $cacheWritable = is_writable(base_path('bootstrap/cache'));
        $dbDirWritable = is_writable(database_path());
        $pathsPass = $storageWritable && $cacheWritable && $dbDirWritable;
        if (! $pathsPass) {
            $hasFailure = true;
        }
        $rows[] = ['Writable: storage/', $storageWritable ? 'Writable' : 'Not Writable', $storageWritable ? '<info>[PASS]</info>' : '<error>[FAIL] chmod 755/775</error>'];
        $rows[] = ['Writable: bootstrap/cache/', $cacheWritable ? 'Writable' : 'Not Writable', $cacheWritable ? '<info>[PASS]</info>' : '<error>[FAIL] chmod 755/775</error>'];
        $rows[] = ['Writable: database/', $dbDirWritable ? 'Writable' : 'Not Writable', $dbDirWritable ? '<info>[PASS]</info>' : '<error>[FAIL] chmod 755/775</error>'];

        // 9. Local Database SQLite Connectivity
        $dbPass = false;
        $dbMsg = '';
        try {
            DB::connection()->getPdo();
            $dbPass = true;
            $dbMsg = 'Connected (' . DB::connection()->getDriverName() . ')';
        } catch (Throwable $e) {
            $dbMsg = 'Failed: ' . $e->getMessage();
            $hasFailure = true;
        }
        $rows[] = ['Database Connection', $dbMsg, $dbPass ? '<info>[PASS]</info>' : '<error>[FAIL]</error>'];

        $this->table(['Diagnostic Check', 'Current Value', 'Preflight Status'], $rows);
        $this->info('');

        if ($hasFailure) {
            $this->error('>> PREFLIGHT DIAGNOSTICS: One or more critical checks FAILED. Review configuration before production.');
            return self::FAILURE;
        }

        $this->info('>> PREFLIGHT DIAGNOSTICS: All local system and configuration checks PASSED.');
        return self::SUCCESS;
    }
}
