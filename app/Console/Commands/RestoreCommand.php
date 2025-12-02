<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * RestoreCommand - Restore Quarantined Files
 * 
 * Usage: php oxygen security:restore [--all] [--force]
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    1.0.0
 */
class RestoreCommand extends Command
{
    protected $name = 'security:restore';
    protected $description = 'Restore files from quarantine to their original location';

    protected $quarantineDir;

    public function __construct()
    {
        $this->quarantineDir = __DIR__ . '/../../../storage/quarantine';
    }

    public function execute($args = [])
    {
        $restoreAll = $this->hasOption($args, '--all');
        $force = $this->hasOption($args, '--force');

        $this->info("🛡️  Oxygen Security Restore System\n");

        if (!is_dir($this->quarantineDir)) {
            $this->error("Quarantine directory not found.");
            return;
        }

        $files = glob($this->quarantineDir . '/*.info.json');

        if (empty($files)) {
            $this->success("No files found in quarantine.");
            return;
        }

        $this->info("Found " . count($files) . " quarantined items.");

        foreach ($files as $infoFile) {
            $info = json_decode(file_get_contents($infoFile), true);
            $quarantinedFile = str_replace('.info.json', '', $infoFile);

            if (!file_exists($quarantinedFile)) {
                $this->warning("Quarantined file not found for metadata: " . basename($infoFile));
                continue;
            }

            $originalPath = $info['original_path'];
            $filename = basename($originalPath);

            $this->line("Found: {$filename}");
            $this->line("  Original Path: {$originalPath}");
            $this->line("  Reason: {$info['reason']}");
            $this->line("  Date: {$info['date']}");

            if ($restoreAll || $this->confirm("Restore this file?", true)) {
                $this->restoreFile($quarantinedFile, $originalPath, $infoFile, $force);
            }

            $this->line(""); // Empty line for spacing
        }
    }

    protected function restoreFile($quarantinedFile, $originalPath, $infoFile, $force)
    {
        // Check if destination directory exists
        $dir = dirname($originalPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Check if destination file exists
        if (file_exists($originalPath) && !$force) {
            if (!$this->confirm("File already exists at destination. Overwrite?", false)) {
                $this->info("Skipped.");
                return;
            }
        }

        if (rename($quarantinedFile, $originalPath)) {
            // Remove metadata file
            unlink($infoFile);
            $this->success("Restored successfully.");
        } else {
            $this->error("Failed to restore file.");
        }
    }
}
