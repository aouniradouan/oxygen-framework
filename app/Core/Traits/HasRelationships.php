<?php

namespace Oxygen\Core\Traits;

use Oxygen\Core\Database\Relations\HasOne;
use Oxygen\Core\Database\Relations\HasMany;
use Oxygen\Core\Database\Relations\BelongsTo;
use Oxygen\Core\Database\Relations\BelongsToMany;

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
     * @return HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKeyName($related);
        $localKey = $localKey ?? $this->primaryKey;

        return new HasOne($related, $this, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship
     * 
     * @param string $related The related model class
     * @param string $foreignKey The foreign key
     * @param string $localKey The local key
     * @return HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        // Foreign key should be based on current model (e.g., user_id for User model)
        $foreignKey = $foreignKey ?? $this->getForeignKeyName($this);
        $localKey = $localKey ?? $this->primaryKey;

        return new \Oxygen\Core\Database\Relations\HasMany($related, $this, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship
     * 
     * @param string $related The related model class
     * @param string $foreignKey The foreign key
     * @param string $ownerKey The owner key
     * @return BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $foreignKey = $foreignKey ?? $this->getForeignKeyName($related);
        $ownerKey = $ownerKey ?? 'id';

        return new BelongsTo($related, $this, $foreignKey, $ownerKey);
    }

    /**
     * Define a many-to-many relationship
     * 
     * @param string $related The related model class
     * @param string $table The pivot table name
     * @param string $foreignPivotKey Foreign key in pivot table
     * @param string $relatedPivotKey Related key in pivot table
     * @return BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
    {
        return new BelongsToMany($related, $this, $table, $foreignPivotKey, $relatedPivotKey);
    }

    /**
     * Get the default foreign key name for the model
     * 
     * @param string|object $model
     * @return string
     */
    protected function getForeignKeyName($model)
    {
        if (is_string($model)) {
            $reflection = new \ReflectionClass($model);
        } else {
            $reflection = new \ReflectionClass($model);
        }

        $name = $reflection->getShortName();
        return strtolower($name) . '_id';
    }

    /**
     * Get a relationship value
     */
    public function getRelationValue($key)
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

        return null;
    }

    /**
     * Load a relationship
     */
    public function load($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $relationInstance = $this->$relation();
                if ($relationInstance instanceof \Oxygen\Core\Database\Relations\Relation) {
                    $this->relations[$relation] = $relationInstance->get();
                } else {
                    $this->relations[$relation] = $relationInstance;
                }
            }
        }

        return $this;
    }

    /**
     * Load multiple relationships
     */
    public function loadMissing($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (!array_key_exists($relation, $this->relations)) {
                $this->load($relation);
            }
        }

        return $this;
    }
}
