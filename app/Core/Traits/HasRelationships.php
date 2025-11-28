<?php

namespace Oxygen\Core\Traits;

/**
 * HasRelationships Trait
 * 
 * Adds relationship functionality to models.
 * Supports: belongsTo, hasMany, hasOne, belongsToMany
 * 
 * @package    Oxygen\Core\Traits
 */
trait HasRelationships
{
    protected $relations = [];

    /**
     * Define a one-to-one relationship
     * 
     * @param string $related The related model class
     * @param string $foreignKey The foreign key
     * @param string $localKey The local key
     */
    protected function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related();
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $localKey = $localKey ?? $this->primaryKey;

        // Use static where method which returns array of results
        $results = $related::where($foreignKey, '=', $this->{$localKey});
        
        if (empty($results)) {
            return null;
        }

        // Hydrate the first result into a model instance
        $model = new $related();
        $model->fill((array) $results[0]);
        $model->exists = true;
        
        return $model;
    }

    /**
     * Define a one-to-many relationship
     * 
     * @param string $related The related model class
     * @param string $foreignKey The foreign key
     * @param string $localKey The local key
     */
    protected function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related();
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $localKey = $localKey ?? $this->primaryKey;

        // Use static where method
        $results = $related::where($foreignKey, '=', $this->{$localKey});
        
        // Hydrate all results
        $models = [];
        foreach ($results as $result) {
            $model = new $related();
            $model->fill((array) $result);
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Define an inverse one-to-one or many relationship
     * 
     * @param string $related The related model class
     * @param string $foreignKey The foreign key
     * @param string $ownerKey The owner key
     */
    protected function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related();
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($instance))->getShortName()) . '_id';
        $ownerKey = $ownerKey ?? $instance->primaryKey;

        if (!isset($this->{$foreignKey})) {
            return null;
        }

        // Use static find method
        $result = $related::find($this->{$foreignKey});
        
        if (!$result) {
            return null;
        }

        // Hydrate result
        $model = new $related();
        $model->fill((array) $result);
        $model->exists = true;
        
        return $model;
    }

    /**
     * Define a many-to-many relationship
     * 
     * @param string $related The related model class
     * @param string $table The pivot table name
     * @param string $foreignPivotKey Foreign key in pivot table
     * @param string $relatedPivotKey Related key in pivot table
     */
    protected function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
    {
        $instance = new $related();

        // Auto-generate table name if not provided
        if (!$table) {
            $models = [
                strtolower((new \ReflectionClass($this))->getShortName()),
                strtolower((new \ReflectionClass($instance))->getShortName())
            ];
            sort($models);
            $table = implode('_', $models);
        }

        $foreignPivotKey = $foreignPivotKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $relatedPivotKey = $relatedPivotKey ?? strtolower((new \ReflectionClass($instance))->getShortName()) . '_id';

        // Get related IDs from pivot table
        $db = \Oxygen\Core\Application::getInstance()->make('db');
        $pivotRecords = $db->query("SELECT * FROM {$table} WHERE {$foreignPivotKey} = ?", $this->{$this->primaryKey})->fetchAll();

        if (empty($pivotRecords)) {
            return [];
        }

        $relatedIds = array_column($pivotRecords, $relatedPivotKey);

        // Get related models using whereIn
        $results = $related::whereIn($instance->primaryKey, $relatedIds);

        // Hydrate results
        $models = [];
        foreach ($results as $result) {
            $model = new $related();
            $model->fill((array) $result);
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Get a relationship value
     */
    public function __get($key)
    {
        // Check if it's a loaded relationship
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        // Check if method exists for relationship
        if (method_exists($this, $key)) {
            $this->relations[$key] = $this->$key();
            return $this->relations[$key];
        }

        // Fall back to attribute
        return $this->attributes[$key] ?? null;
    }

    /**
     * Load a relationship
     */
    public function load($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $this->relations[$relation] = $this->$relation();
            }
        }

        return $this;
    }
}
