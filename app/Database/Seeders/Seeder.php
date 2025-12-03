<?php

namespace Oxygen\Database\Seeders;

/**
 * Seeder Base Class
 * 
 * Base class for database seeders.
 * 
 * @package    Oxygen\Database\Seeders
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Seeder
{
    /**
     * Run the database seeds
     * 
     * @return void
     */
    abstract public function run();

    /**
     * Call another seeder
     * 
     * @param string $seeder
     * @return void
     */
    protected function call($seeder)
    {
        $seeder = new $seeder();
        $seeder->run();
    }
}

