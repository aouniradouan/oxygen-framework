<?php

namespace Oxygen\Core\Database;

use Oxygen\Core\Application;

/**
 * OxygenMigrator - Migration Runner
 * 
 * Handles running and rolling back database migrations.
 * 
 * @package    Oxygen\Core\Database
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class OxygenMigrator
{
    protected $db;
    protected $migrationsPath;

    public function __construct()
    {
        $this->db = Application::getInstance()->make('db');
        $this->migrationsPath = Application::getInstance()->basePath('database/migrations');
        $this->ensureMigrationsTableExists();
    }

    /**
     * Create migrations tracking table if it doesn't exist
     */
    protected function ensureMigrationsTableExists()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL,
                `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    /**
     * Run all pending migrations
     */
    public function migrate()
    {
        $files = $this->getMigrationFiles();
        echo "Found " . count($files) . " migration files.\n";

        $ran = $this->getRanMigrations();
        echo "Found " . count($ran) . " already ran migrations.\n";

        $batch = $this->getNextBatchNumber();

        $pending = array_diff($files, $ran);
        echo "Pending migrations: " . count($pending) . "\n";

        if (empty($pending)) {
            return ['message' => 'Nothing to migrate'];
        }

        $migrated = [];
        $failed = [];

        foreach ($pending as $file) {
            echo "Migrating: $file\n";
            $ok = $this->runMigration($file, $batch);
            if ($ok) {
                $migrated[] = $file;
            } else {
                $failed[] = $file;
            }
        }

        $result = ['migrated' => $migrated];
        if (!empty($failed)) {
            $result['failed'] = $failed;
        }

        return $result;
    }

    /**
     * Rollback the last batch of migrations
     */
    public function rollback()
    {
        $lastBatch = $this->getLastBatchNumber();

        if ($lastBatch === 0) {
            return ['message' => 'Nothing to rollback'];
        }

        $migrations = $this->db->query(
            "SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC",
            $lastBatch
        )->fetchAll();

        $rolledBack = [];
        foreach ($migrations as $migration) {
            $this->rollbackMigration($migration['migration']);
            $rolledBack[] = $migration['migration'];
        }

        return ['rolled_back' => $rolledBack];
    }

    /**
     * Run a single migration
     */
    protected function runMigration($file, $batch)
    {
        $path = $this->migrationsPath . '/' . $file;

        if (!file_exists($path)) {
            echo "Error: Migration file not found: $path\n";
            return false;
        }

        require_once $path;

        $className = $this->getClassNameFromFile($file);
        echo "Resolved class name: $className\n";

        if (!class_exists($className)) {
            echo "Error: Class '$className' not found in $file\n";
            return false;
        }

        try {
            $migration = new $className();
            if (method_exists($migration, 'up')) {
                $migration->up();
            } else {
                echo "Error: Migration class '$className' has no up() method\n";
                return false;
            }

            $this->db->query(
                "INSERT INTO migrations (migration, batch) VALUES (?, ?)",
                $file,
                $batch
            );

            return true;
        } catch (\Throwable $e) {
            echo "Exception while running migration $file: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Rollback a single migration
     */
    protected function rollbackMigration($file)
    {
        require_once $this->migrationsPath . '/' . $file;

        $className = $this->getClassNameFromFile($file);
        $migration = new $className();
        $migration->down();

        $this->db->query("DELETE FROM migrations WHERE migration = ?", $file);
    }

    /**
     * Get all migration files
     */
    protected function getMigrationFiles()
    {
        $files = glob($this->migrationsPath . '/*.php');
        return array_map('basename', $files);
    }

    /**
     * Get migrations that have already been run
     */
    protected function getRanMigrations()
    {
        $rows = $this->db->query("SELECT migration FROM migrations")->fetchAll();

        $migrations = [];
        foreach ($rows as $r) {
            if (is_array($r) && isset($r['migration'])) {
                $migrations[] = $r['migration'];
            } elseif (is_object($r) && isset($r->migration)) {
                $migrations[] = $r->migration;
            }
        }

        return $migrations;
    }

    /**
     * Get the next batch number
     */
    protected function getNextBatchNumber()
    {
        $max = $this->db->query("SELECT MAX(batch) as max_batch FROM migrations")->fetch();
        if (is_array($max)) {
            $val = $max['max_batch'] ?? 0;
        } elseif (is_object($max)) {
            $val = $max->max_batch ?? 0;
        } else {
            $val = 0;
        }

        return ((int) $val) + 1;
    }

    /**
     * Get the last batch number
     */
    protected function getLastBatchNumber()
    {
        $max = $this->db->query("SELECT MAX(batch) as max_batch FROM migrations")->fetch();

        if (is_array($max)) {
            return $max['max_batch'] ?? 0;
        } elseif (is_object($max)) {
            return $max->max_batch ?? 0;
        }

        return 0;
    }

    /**
     * Extract class name from migration file
     */
    protected function getClassNameFromFile($file)
    {
        // Remove timestamp and .php extension
        // e.g., "2024_01_01_000000_create_users_table.php" -> "CreateUsersTable"
        $parts = explode('_', $file);
        array_shift($parts); // Remove year
        array_shift($parts); // Remove month
        array_shift($parts); // Remove day
        array_shift($parts); // Remove time
        $name = implode('_', $parts);
        $name = str_replace('.php', '', $name);

        return str_replace('_', '', ucwords($name, '_'));
    }
}
