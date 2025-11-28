<?php

namespace Oxygen\Core\Database;

use Oxygen\Core\Application;

/**
 * Migration - Base Migration Class
 * 
 * All database migrations should extend this class. Migrations allow you to
 * version control your database schema and easily rollback changes.
 * 
 * @package    Oxygen\Core\Database
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 * @version    2.1.0
 * 
 * @example
 * class CreateUsersTable extends Migration
 * {
 *     public function up()
 *     {
 *         $this->schema->createTable('users', function($table) {
 *             $table->id();
 *             $table->string('name');
 *             $table->string('email')->unique();
 *             $table->timestamps();
 *         });
 *     }
 * 
 *     public function down()
 *     {
 *         $this->schema->dropTable('users');
 *     }
 * }
 */
abstract class Migration
{
    /**
     * Database connection
     * 
     * @var \Nette\Database\Connection
     */
    protected $db;

    /**
     * Schema builder
     * 
     * @var object
     */
    protected $schema;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Application::getInstance()->make('db');
        $this->schema = $this; // Allow $this->schema->createTable() syntax
    }

    /**
     * Run the migration (create tables, add columns, etc.)
     * 
     * @return void
     */
    abstract public function up();

    /**
     * Reverse the migration (drop tables, remove columns, etc.)
     * 
     * @return void
     */
    abstract public function down();

    /**
     * Execute a SQL query
     * 
     * @param string $sql SQL query
     * @return mixed
     */
    protected function execute($sql)
    {
        return $this->db->query($sql);
    }

    /**
     * Create a table using schema builder
     * 
     * @param string $table Table name
     * @param callable $callback Callback to define columns
     * @return void
     */
    public function createTable($table, $callback)
    {
        $schema = new OxygenSchema($table);
        $callback($schema);
        $this->execute($schema->toSQL());
    }

    /**
     * Backwards-compatible alias for older migrations that call ->create()
     *
     * @param string $table
     * @param callable $callback
     * @return void
     */
    public function create($table, $callback)
    {
        return $this->createTable($table, $callback);
    }

    /**
     * Drop a table
     * 
     * @param string $table Table name
     * @return void
     */
    public function dropTable($table)
    {
        $this->execute("DROP TABLE IF EXISTS `{$table}`");
    }

    /**
     * Backwards-compatible alias for older migrations using dropIfExists
     *
     * @param string $table
     * @return void
     */
    public function dropIfExists($table)
    {
        return $this->dropTable($table);
    }

    /**
     * Check if table exists
     * 
     * @param string $table Table name
     * @return bool
     */
    protected function tableExists($table)
    {
        $result = $this->db->query("SHOW TABLES LIKE ?", $table)->fetch();
        return !empty($result);
    }
}

// Create alias for backward compatibility
class_alias(Migration::class, 'Oxygen\Core\Database\OxygenMigration');
