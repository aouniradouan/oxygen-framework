<?php

namespace Oxygen\Core\Database;

/**
 * Seeder - Base Seeder Class
 * 
 * Abstract class for database seeders.
 * 
 * @package    Oxygen\Core\Database
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    abstract public function run();

    /**
     * Call another seeder class.
     *
     * @param string $class
     * @return void
     */
    public function call($class)
    {
        if (!class_exists($class)) {
            // Fallback: Try to require the file manually
            $basePath = __DIR__ . '/../../../database/seeders/';
            $className = str_replace('Database\\Seeders\\', '', $class);
            $filePath = $basePath . $className . '.php';

            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }

        $seeder = new $class();
        $seeder->run();

        echo "Seeded: " . $class . "\n";
    }
}
