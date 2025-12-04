<?php

namespace Oxygen\Core;

use Nette\Database\Connection;
use Oxygen\Core\Traits\HasRelationships;
use Oxygen\Core\Database\Collection;

abstract class Model
{
    use HasRelationships;

    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    protected $casts = [];

    protected $relations = [];



    protected $attributes = [];
    public $exists = false;

    /**
     * The relationships that should be eager loaded
     */
    protected $with = [];

    public function __construct(array $attributes = [])
    {
        if (!$this->table) {
            $this->table = $this->guessTableName();
        }

        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Guess the table name from the model name
     */
    public function guessTableName()
    {
        $class = (new \ReflectionClass($this))->getShortName();
        return strtolower($class) . 's'; // Simple pluralization
    }

    /**
     * Get the database connection
     */
    protected static function db()
    {
        return Application::getInstance()->make('db');
    }

    /**
     * Get all records
     */
    public static function all()
    {
        $results = static::db()->query('SELECT * FROM ?name', static::getTableName())->fetchAll();
        return static::hydrate($results);
    }

    /**
     * Find a record by ID
     */
    public static function find($id)
    {
        $result = static::db()->query('SELECT * FROM ?name WHERE ?name = ?', static::getTableName(), static::getPrimaryKey(), $id)->fetch();

        if (!$result) {
            return null;
        }

        return static::hydrateOne($result);
    }

    /**
     * Create a new record
     */
    public static function create(array $data)
    {
        $model = new static;
        $filteredData = $model->filterFillable($data);

        static::db()->query('INSERT INTO ?name ?', static::getTableName(), $filteredData);

        $id = static::db()->getInsertId();
        return static::find($id);
    }

    /**
     * Update a record
     */
    public static function update($id, array $data)
    {
        $model = new static;
        $filteredData = $model->filterFillable($data);

        static::db()->query('UPDATE ?name SET ? WHERE ?name = ?', static::getTableName(), $filteredData, static::getPrimaryKey(), $id);

        return static::find($id);
    }

    /**
     * Delete a record by ID
     */
    public static function delete($id)
    {
        return static::db()->query('DELETE FROM ?name WHERE ?name = ?', static::getTableName(), static::getPrimaryKey(), $id);
    }

    /**
     * Save the model to the database
     * Creates a new record if not exists, updates if exists
     * 
     * @return $this
     */
    public function save()
    {
        $data = $this->filterFillable($this->attributes);

        // Add timestamps
        $now = date('Y-m-d H:i:s');

        if ($this->exists) {
            // Update existing record
            $data['updated_at'] = $now;
            $id = $this->attributes[$this->primaryKey];
            static::db()->query('UPDATE ?name SET ? WHERE ?name = ?', static::getTableName(), $data, $this->primaryKey, $id);
        } else {
            // Create new record
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            static::db()->query('INSERT INTO ?name ?', static::getTableName(), $data);
            $this->attributes[$this->primaryKey] = static::db()->getInsertId();
            $this->exists = true;
        }

        return $this;
    }

    /**
     * Delete the model instance from database
     * 
     * @return bool
     */
    public function destroy()
    {
        if (!$this->exists) {
            return false;
        }

        $id = $this->attributes[$this->primaryKey] ?? null;
        if ($id) {
            static::delete($id);
            $this->exists = false;
            return true;
        }

        return false;
    }

    /**
     * Query with where clause
     */
    public static function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        // Allow only valid SQL operators to prevent injection
        $allowedOperators = ['=', '>', '<', '>=', '<=', 'LIKE', '!=', '<>'];
        if (!in_array(strtoupper($operator), $allowedOperators)) {
            $operator = '=';
        }

        $results = static::db()->query('SELECT * FROM ?name WHERE ?name ' . $operator . ' ?', static::getTableName(), $column, $value)->fetchAll();
        return static::hydrate($results);
    }





    /**
     * Query with whereIn clause
     */
    public static function whereIn($column, array $values)
    {
        if (empty($values)) {
            return new Collection([]);
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $sql = 'SELECT * FROM ' . static::getTableName() . ' WHERE ' . $column . ' IN (' . $placeholders . ')';

        $results = static::db()->query($sql, ...$values)->fetchAll();
        return static::hydrate($results);
    }

    /**
     * Paginate results
     */
    public static function paginate($perPage = 15)
    {
        $page = $_GET['page'] ?? 1;
        $page = max(1, (int) $page);
        $offset = ($page - 1) * $perPage;

        // Get total count
        $total = static::db()->query('SELECT COUNT(*) as count FROM ?name', static::getTableName())->fetch()->count;

        // Get paginated items
        $results = static::db()->query('SELECT * FROM ?name LIMIT ? OFFSET ?', static::getTableName(), $perPage, $offset)->fetchAll();
        $items = static::hydrate($results);

        return new Paginator($items, $total, $perPage, $page);
    }

    /**
     * Eager load relationships
     */
    public static function with($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        $instance = new static;
        $instance->with = array_merge($instance->with, $relations);

        return $instance;
    }

    /**
     * Get results with eager loaded relationships
     */
    public function get()
    {
        $results = static::all();

        if (!empty($this->with)) {
            $results->load($this->with);
        }

        return $results;
    }

    /**
     * Hydrate a collection of models
     */
    protected static function hydrate(array $results)
    {
        $models = [];

        foreach ($results as $result) {
            $models[] = static::hydrateOne($result);
        }

        $collection = new Collection($models);

        // Auto-load default relationships
        $instance = new static;
        if (!empty($instance->with)) {
            $collection->load($instance->with);
        }

        return $collection;
    }

    /**
     * Hydrate a single model
     */
    protected static function hydrateOne($result)
    {
        $model = new static();
        $model->fill((array) $result);
        $model->exists = true;

        return $model;
    }

    /**
     * Filter fillable attributes
     */
    protected function filterFillable(array $data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Get the table name
     */
    protected static function getTableName()
    {
        return (new static)->table;
    }

    /**
     * Get the primary key
     */
    protected static function getPrimaryKey()
    {
        return (new static)->primaryKey;
    }

    /**
     * Convert model to array
     */
    public function toArray()
    {
        $attributes = $this->attributes;

        // Convert attributes that are objects
        foreach ($attributes as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            }
        }

        // Include loaded relationships
        foreach ($this->relations as $key => $value) {
            if ($value instanceof Collection) {
                $attributes[$key] = $value->toArray();
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                $attributes[$key] = $value->toArray();
            } elseif ($value instanceof \DateTimeInterface) {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            } else {
                $attributes[$key] = $value;
            }
        }

        // Filter hidden attributes
        if (!empty($this->hidden)) {
            $attributes = array_diff_key($attributes, array_flip($this->hidden));
        }

        return $attributes;
    }

    /**
     * Convert model to JSON
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Magic method to get attributes
     */
    public function __get($key)
    {
        // Handled by HasRelationships trait
        return $this->getAttribute($key);
    }

    /**
     * Get an attribute value
     */
    protected function getAttribute($key)
    {
        // Check if it's a loaded relationship
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // Check if method exists for relationship
        if (method_exists($this, $key)) {
            $relation = $this->$key();

            // If it's a Relation object, get the results
            if ($relation instanceof \Oxygen\Core\Database\Relations\Relation) {
                $this->relations[$key] = $relation->get();
                return $this->relations[$key];
            }

            return $relation;
        }

        // Fall back to attribute
        return $this->attributes[$key] ?? null;
    }

    /**
     * Magic method to set attributes
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Magic method to check if attribute exists
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }
}
