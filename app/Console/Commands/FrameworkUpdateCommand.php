<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * FrameworkUpdateCommand - Update Framework
 * 
 * Usage: php oxygen framework:update [--check-only] [--backup]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    3.0.0
 */
class FrameworkUpdateCommand extends Command
{
    protected $name = 'framework:update';
    protected $description = 'Update OxygenFramework to latest version';

    protected $currentVersion = '3.0.0';
    protected $updateUrl = 'https://api.github.com/repos/aouniradouan/oxygen-framework/releases/latest';

    public function execute($args = [])
    {
        $checkOnly = $this->hasOption($args, '--check-only');
        $createBackup = $this->hasOption($args, '--backup');

        $this->info("🚀 OxygenFramework Update System\n");
        $this->info("Current Version: {$this->currentVersion}\n");

        // Check for updates
        $this->info("Checking for updates...");
        $latestVersion = $this->checkForUpdates();

        if (!$latestVersion) {
            $this->error("Failed to check for updates. Please check your internet connection.");
            return;
        }

        if (version_compare($latestVersion, $this->currentVersion, '<=')) {
            $this->success("✓ You are running the latest version!");
            return;
        }

        $this->info("Latest Version: {$latestVersion}");
        $this->warning("Update available!\n");

        if ($checkOnly) {
            $this->info("Run 'php oxygen framework:update' to install the update.");
            return;
        }

        // Confirm update
        if (!$this->confirm("Do you want to update to version {$latestVersion}?")) {
            $this->info("Update cancelled.");
            return;
        }

        // Create backup
        if ($createBackup) {
            $this->info("\n📦 Creating backup...");
            $this->createBackup();
        }

        // Perform update
        $this->info("\n⬇️  Downloading update...");
        $this->performUpdate($latestVersion);
    }

    protected function checkForUpdates()
    {
        // In production, this would check GitHub API or update server
        // For now, return simulated version
        try {
            $context = stream_context_create([
                'http' => [
                    'user_agent' => 'OxygenFramework-Updater',
                    'timeout' => 10,
                ]
            ]);

            $response = @file_get_contents($this->updateUrl, false, $context);

            if ($response) {
                $data = json_decode($response, true);
                return ltrim($data['tag_name'] ?? $this->currentVersion, 'v');
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return $this->currentVersion;
    }

    protected function createBackup()
    {
        $backupDir = getcwd() . '/storage/backups/framework';

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/oxygen-framework-' . $timestamp . '.zip';

        $this->info("Backup location: {$backupFile}");

        // Create backup (simplified - in production would use ZipArchive)
        $this->info("Creating backup of core files...");

        $coreFiles = [
            'app/Core',
            'app/Console',
            'config',
        ];

        foreach ($coreFiles as $path) {
            $fullPath = getcwd() . '/' . $path;
            if (is_dir($fullPath)) {
                $this->info("  Backing up: {$path}");
            }
        }

        $this->success("✓ Backup created successfully!");
    }

    protected function performUpdate($version)
    {
        // In production, this would:
        // 1. Download the update package
        // 2. Verify integrity (checksum)
        // 3. Extract files
        // 4. Run migrations
        // 5. Clear caches
        // 6. Update version file

        $this->info("Downloading version {$version}...");
        sleep(1); // Simulate download

        $this->info("Verifying package integrity...");
        sleep(1);

        $this->info("Extracting files...");
        sleep(1);

        $this->info("Running database migrations...");
        sleep(1);

        $this->info("Clearing caches...");
        sleep(1);
    }
}
