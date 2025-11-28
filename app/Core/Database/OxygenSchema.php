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
     * Add a foreign ID column (BIGINT UNSIGNED for referencing another table's id)
     */
    public function foreignId($name)
    {
        $this->columns[] = "`{$name}` BIGINT UNSIGNED NOT NULL";
        return $this;
    }

    /**
     * Add a foreign key constraint (called after foreignId)
     * Assumes standard naming: table_id references tables(id)
     */
    public function constrained($table = null)
    {
        $last = array_pop($this->columns);

        // Extract table name from column name if not provided
        // e.g., user_id -> users, contact_id -> contacts
        if (!$table) {
            preg_match('/`(\w+)_id`/', $last, $matches);
            if (isset($matches[1])) {
                $table = $matches[1] . 's'; // Simple pluralization
            }
        }

        if ($table) {
            $last .= " REFERENCES `{$table}`(`id`)";
        }

        $this->columns[] = $last;
        return $this;
    }

    /**
     * Set ON DELETE action for foreign key
     */
    public function onDelete($action = 'CASCADE')
    {
        $last = array_pop($this->columns);
        $this->columns[] = $last . " ON DELETE {$action}";
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
        $columns = implode(",\n    ", $this->columns);
        return "CREATE TABLE `{$this->table}` (\n    {$columns}\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset}";
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
