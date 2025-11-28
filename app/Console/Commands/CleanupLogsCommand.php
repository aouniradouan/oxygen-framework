<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * CleanupLogsCommand - Example Scheduled Task
 * 
 * Usage: php oxygen logs:cleanup
 * 
 * @package    Oxygen\Console\Commands
 */
class CleanupLogsCommand extends Command
{
    protected $name = 'logs:cleanup';
    protected $description = 'Clean up old log files (Example Task)';

    public function execute($args = [])
    {
        $this->info("🧹 Starting log cleanup...");

        $logDir = getcwd() . '/storage/logs';
        if (!is_dir($logDir)) {
            $this->warning("Log directory does not exist.");
            return;
        }

        $files = glob($logDir . '/*.log');
        $count = 0;

        foreach ($files as $file) {
            // Delete logs older than 7 days
            if (filemtime($file) < (time() - (7 * 24 * 60 * 60))) {
                unlink($file);
                $this->line("Deleted: " . basename($file));
                $count++;
            }
        }

        $this->success("Cleanup complete. Deleted {$count} old log files.");

        // Log this action to demonstrate the scheduler is working
        $logFile = $logDir . '/scheduler_test.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[{$timestamp}] Log cleanup task ran successfully.\n", FILE_APPEND);
    }
}
