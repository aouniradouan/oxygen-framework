<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * DbSeedCommand - Run database seeders
 * 
 * Usage: php oxygen db:seed
 *        php oxygen db:seed --class=UserSeeder
 */
class DbSeedCommand extends Command
{
    public function execute($arguments)
    {
        // Register temporary autoloader for Database namespace
        spl_autoload_register(function ($class) {
            if (strpos($class, 'Database\\') === 0) {
                $baseDir = __DIR__ . '/../../../database/';
                $relativeClass = substr($class, 9); // Remove 'Database\'
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });

        $seederClass = 'Database\\Seeders\\DatabaseSeeder';

        // Parse arguments for --class option
        foreach ($arguments as $arg) {
            if (strpos($arg, '--class=') === 0) {
                $className = substr($arg, 8);
                $seederClass = 'Database\\Seeders\\' . $className;
            }
        }

        if (!class_exists($seederClass)) {
            // Try without namespace prefix if not found
            if (class_exists($arguments[0] ?? '')) {
                $seederClass = $arguments[0];
            } else {
                $this->error("Seeder class '$seederClass' not found.");
                return;
            }
        }

        $this->info("Seeding database...");

        try {
            $seeder = new $seederClass();
            if (method_exists($seeder, 'run')) {
                $seeder->run();
                $this->success("Database seeding completed successfully.");
            } else {
                $this->error("Seeder class must have a run() method.");
            }
        } catch (\Throwable $e) {
            $this->error("Seeding failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
