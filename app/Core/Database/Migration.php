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

    /**
     * Add a column to an existing table
     * 
     * @param string $table
     * @param string $column
     * @param string $type
     * @return void
     */
    public function addColumn($table, $column, $type)
    {
        $this->execute("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}");
    }

    /**
     * Drop a column from a table
     * 
     * @param string $table
     * @param string $column
     * @return void
     */
    public function dropColumn($table, $column)
    {
        $this->execute("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
    }

    /**
     * Modify a column in a table
     * 
     * @param string $table
     * @param string $column
     * @param string $type
     * @return void
     */
    public function modifyColumn($table, $column, $type)
    {
        $this->execute("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$type}");
    }

    /**
     * Rename a column
     * 
     * @param string $table
     * @param string $from
     * @param string $to
     * @return void
     */
    public function renameColumn($table, $from, $to)
    {
        $this->execute("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");
    }

    /**
     * Add a foreign key constraint
     * 
     * @param string $table
     * @param string $column
     * @param string $referencesTable
     * @param string $referencesColumn
     * @param string $onDelete
     * @param string $onUpdate
     * @return void
     */
    public function addForeignKey($table, $column, $referencesTable, $referencesColumn = 'id', $onDelete = 'CASCADE', $onUpdate = 'CASCADE')
    {
        $constraintName = "fk_{$table}_{$column}";
        $sql = "ALTER TABLE `{$table}` 
                ADD CONSTRAINT `{$constraintName}` 
                FOREIGN KEY (`{$column}`) 
                REFERENCES `{$referencesTable}`(`{$referencesColumn}`) 
                ON DELETE {$onDelete} 
                ON UPDATE {$onUpdate}";
        $this->execute($sql);
    }

    /**
     * Drop a foreign key constraint
     * 
     * @param string $table
     * @param string $constraintName
     * @return void
     */
    public function dropForeignKey($table, $constraintName)
    {
        $this->execute("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
    }

    /**
     * Add an index to a table
     * 
     * @param string $table
     * @param string|array $columns
     * @param string|null $name
     * @return void
     */
    public function addIndex($table, $columns, $name = null)
    {
        if (is_array($columns)) {
            $columnList = '`' . implode('`, `', $columns) . '`';
            $name = $name ?: 'idx_' . implode('_', $columns);
        } else {
            $columnList = "`{$columns}`";
            $name = $name ?: "idx_{$columns}";
        }
        
        $this->execute("ALTER TABLE `{$table}` ADD INDEX `{$name}` ({$columnList})");
    }

    /**
     * Drop an index
     * 
     * @param string $table
     * @param string $name
     * @return void
     */
    public function dropIndex($table, $name)
    {
        $this->execute("ALTER TABLE `{$table}` DROP INDEX `{$name}`");
    }

    /**
     * Create a pivot table for many-to-many relationships
     * 
     * @param string $table1
     * @param string $table2
     * @param string|null $pivotTable
     * @return void
     */
    public function createPivotTable($table1, $table2, $pivotTable = null)
    {
        if (!$pivotTable) {
            $tables = [$table1, $table2];
            sort($tables);
            $pivotTable = implode('_', $tables);
        }

        $key1 = rtrim($table1, 's') . '_id';
        $key2 = rtrim($table2, 's') . '_id';

        $this->createTable($pivotTable, function($table) use ($key1, $key2, $table1, $table2) {
            $table->bigInteger($key1)->unsigned();
            $table->bigInteger($key2)->unsigned();
            $table->primary([$key1, $key2]);
            $table->index($key1);
            $table->index($key2);
        });

        // Add foreign keys
        $this->addForeignKey($pivotTable, $key1, $table1, 'id');
        $this->addForeignKey($pivotTable, $key2, $table2, 'id');
    }
}

// Create alias for backward compatibility
class_alias(Migration::class, 'Oxygen\Core\Database\OxygenMigration');
