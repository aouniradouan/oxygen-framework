<?php

namespace Oxygen\Core\Database;

/**
 * OxygenSchema - Database Schema Builder
 * 
 * Provides a fluent API for building database table schemas.
 * 
 * @package    Oxygen\Core\Database
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 * @version    2.1.0
 */
class OxygenSchema
{
    protected $table;
    protected $columns = [];
    protected $engine = 'InnoDB';
    protected $charset = 'utf8mb4';
    protected $foreignKeys = [];
    protected $lastForeignColumn = null;

    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Add an auto-incrementing ID column
     */
    public function id($name = 'id')
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    /**
     * Add a string/varchar column
     */
    public function string($name, $length = 255)
    {
        $this->columns[] = "`{$name}` VARCHAR({$length}) NOT NULL";
        return $this;
    }

    /**
     * Add a text column
     */
    public function text($name)
    {
        $this->columns[] = "`{$name}` TEXT NOT NULL";
        return $this;
    }

    /**
     * Add an integer column
     */
    public function integer($name)
    {
        $this->columns[] = "`{$name}` INT NOT NULL";
        return $this;
    }

    /**
     * Add a big integer column
     */
    public function bigInteger($name)
    {
        $this->columns[] = "`{$name}` BIGINT NOT NULL";
        return $this;
    }

    /**
     * Add a medium integer column
     */
    public function medium($name)
    {
        $this->columns[] = "`{$name}` MEDIUMINT NOT NULL";
        return $this;
    }

    /**
     * Add a long/big integer column (alias for bigInteger)
     */
    public function long($name)
    {
        return $this->bigInteger($name);
    }

    /**
     * Add a foreign key ID column (BIGINT UNSIGNED for referencing another table)
     * 
     * @param string $name Column name (e.g., 'user_id')
     * @return $this
     */
    public function foreignId($name)
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED NOT NULL";
        $this->lastForeignColumn = $name;
        return $this;
    }

    /**
     * Add constrained foreign key (requires foreignId to be called first)
     * 
     * @param string|null $table Table to reference (guessed from column if null)
     * @param string $column Column to reference (default: id)
     * @return $this
     */
    public function constrained($table = null, $column = 'id')
    {
        if (!isset($this->lastForeignColumn)) {
            return $this;
        }

        $columnName = $this->lastForeignColumn;

        // Guess table name from column: user_id -> users
        if ($table === null) {
            $table = rtrim($columnName, '_id') . 's';
        }

        $constraintName = "fk_{$this->table}_{$columnName}";
        $this->foreignKeys[] = "CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$columnName}`) REFERENCES `{$table}`(`{$column}`)";

        return $this;
    }

    /**
     * Specify ON DELETE action for foreign key
     * 
     * @param string $action CASCADE, SET NULL, RESTRICT, NO ACTION
     * @return $this
     */
    public function onDelete($action)
    {
        if (!empty($this->foreignKeys)) {
            $lastKey = count($this->foreignKeys) - 1;
            $this->foreignKeys[$lastKey] .= " ON DELETE {$action}";
        }
        return $this;
    }

    /**
     * Specify ON UPDATE action for foreign key
     * 
     * @param string $action CASCADE, SET NULL, RESTRICT, NO ACTION
     * @return $this
     */
    public function onUpdate($action)
    {
        if (!empty($this->foreignKeys)) {
            $lastKey = count($this->foreignKeys) - 1;
            $this->foreignKeys[$lastKey] .= " ON UPDATE {$action}";
        }
        return $this;
    }

    /**
     * Add a double column
     */
    public function double($name)
    {
        $this->columns[] = "`{$name}` DOUBLE NOT NULL";
        return $this;
    }

    /**
     * Add a decimal column with precision
     */
    public function decimal($name, $precision = 8, $scale = 2)
    {
        $this->columns[] = "`{$name}` DECIMAL({$precision},{$scale}) NOT NULL";
        return $this;
    }

    /**
     * Add a float column
     */
    public function float($name)
    {
        $this->columns[] = "`{$name}` FLOAT NOT NULL";
        return $this;
    }

    /**
     * Add a boolean column
     */
    public function boolean($name)
    {
        $this->columns[] = "`{$name}` TINYINT(1) NOT NULL DEFAULT 0";
        return $this;
    }

    /**
     * Add a date column
     */
    public function date($name)
    {
        $this->columns[] = "`{$name}` DATE NOT NULL";
        return $this;
    }

    /**
     * Add a datetime column
     */
    public function datetime($name)
    {
        $this->columns[] = "`{$name}` DATETIME NOT NULL";
        return $this;
    }

    /**
     * Add a timestamp column
     */
    public function timestamp($name)
    {
        $this->columns[] = "`{$name}` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
        return $this;
    }

    /**
     * Add an enum column
     */
    public function enum($name, array $values)
    {
        $options = "'" . implode("', '", $values) . "'";
        $this->columns[] = "`{$name}` ENUM({$options}) NOT NULL";
        return $this;
    }

    /**
     * Add a JSON column
     */
    public function json($name)
    {
        $this->columns[] = "`{$name}` JSON NOT NULL";
        return $this;
    }

    /**
     * Add unsigned big integer (for foreign keys)
     */
    public function unsignedBigInteger($name)
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED NOT NULL";
        return $this;
    }

    /**
     * Add unsigned integer
     */
    public function unsignedInteger($name)
    {
        $this->columns[] = "`{$name}` INT UNSIGNED NOT NULL";
        return $this;
    }

    /**
     * Add timestamp columns (created_at, updated_at)
     */
    public function timestamps()
    {
        $this->columns[] = "`created_at` TIMESTAMP NULL DEFAULT NULL";
        $this->columns[] = "`updated_at` TIMESTAMP NULL DEFAULT NULL";
        return $this;
    }

    /**
     * Add soft deletes column
     */
    public function softDeletes()
    {
        $this->columns[] = "`deleted_at` TIMESTAMP NULL DEFAULT NULL";
        return $this;
    }

    /**
     * Add UNIQUE constraint
     */
    public function unique()
    {
        $last = array_pop($this->columns);
        $this->columns[] = $last . ' UNIQUE';
        return $this;
    }

    /**
     * Make the column UNSIGNED (for integer types)
     */
    public function unsigned()
    {
        $last = array_pop($this->columns);
        // Insert UNSIGNED after the type name
        $last = preg_replace('/(INT|BIGINT|MEDIUMINT|SMALLINT|TINYINT)/', '$1 UNSIGNED', $last);
        $this->columns[] = $last;
        return $this;
    }

    /**
     * Allow NULL values
     */
    public function nullable()
    {
        $last = array_pop($this->columns);
        // Remove NOT NULL if present
        $last = str_replace(' NOT NULL', '', $last);
        // Add NULL
        $last = str_replace(' DEFAULT', ' NULL DEFAULT', $last);
        if (strpos($last, ' NULL') === false && strpos($last, ' DEFAULT') === false) {
            $last .= ' NULL';
        }
        $this->columns[] = $last;
        return $this;
    }

    /**
     * Set default value
     */
    public function default($value)
    {
        $last = array_pop($this->columns);
        if (is_string($value)) {
            $value = "'" . addslashes($value) . "'";
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'NULL';
        }
        $this->columns[] = $last . " DEFAULT {$value}";
        return $this;
    }

    /**
     * Add an index
     */
    public function index($column)
    {
        // Indexes are added after column definitions
        $this->columns[] = "INDEX `idx_{$column}` (`{$column}`)";
        return $this;
    }

    /**
     * Add a primary key (composite)
     */
    public function primary($columns)
    {
        if (is_array($columns)) {
            $cols = '`' . implode('`, `', $columns) . '`';
        } else {
            $cols = "`{$columns}`";
        }
        $this->columns[] = "PRIMARY KEY ({$cols})";
        return $this;
    }

    /**
     * Add a foreign key constraint
     */
    public function foreign($column)
    {
        // Return a ForeignKeyDefinition object for chaining
        return new ForeignKeyDefinition($this, $column);
    }

    /**
     * Generate the CREATE TABLE SQL
     */
    public function toSQL()
    {
        $allDefinitions = $this->columns;

        // Add foreign key constraints
        if (!empty($this->foreignKeys)) {
            $allDefinitions = array_merge($allDefinitions, $this->foreignKeys);
        }

        $definitions = implode(",\n    ", $allDefinitions);
        return "CREATE TABLE `{$this->table}` (\n    {$definitions}\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset}";
    }
}

/**
 * Foreign Key Definition Helper
 */
class ForeignKeyDefinition
{
    protected $schema;
    protected $column;
    protected $references;
    protected $on;
    protected $onDelete;
    protected $onUpdate;

    public function __construct($schema, $column)
    {
        $this->schema = $schema;
        $this->column = $column;
    }

    public function references($column)
    {
        $this->references = $column;
        return $this;
    }

    public function on($table)
    {
        $this->on = $table;
        $this->addConstraint();
        return $this->schema;
    }

    public function onDelete($action)
    {
        $this->onDelete = $action;
        return $this;
    }

    public function onUpdate($action)
    {
        $this->onUpdate = $action;
        return $this;
    }

    protected function addConstraint()
    {
        $constraint = "FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->on}`(`{$this->references}`)";

        if ($this->onDelete) {
            $constraint .= " ON DELETE {$this->onDelete}";
        }

        if ($this->onUpdate) {
            $constraint .= " ON UPDATE {$this->onUpdate}";
        }

        $this->schema->columns[] = $constraint;
    }
}
