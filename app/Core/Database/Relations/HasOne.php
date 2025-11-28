<?php

namespace Oxygen\Core\Database\Relations;

use Oxygen\Core\Database\Collection;

/**
 * HasOne - One-to-One Relationship
 * 
 * Defines a one-to-one relationship where the current model has one related model.
 * Example: A User has one Profile
 * 
 * @package    Oxygen\Core\Database\Relations
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class HasOne extends Relation
{
    /**
     * The foreign key of the related model
     */
    protected $foreignKey;

    /**
     * The local key of the parent model
     */
    protected $localKey;

    /**
     * Create a new has one relationship instance
     */
    public function __construct($related, $parent, $foreignKey = null, $localKey = null)
    {
        parent::__construct($related, $parent);

        // Auto-detect foreign key: user_id for User model
        $this->foreignKey = $foreignKey ?? $this->getForeignKeyName();

        // Local key is usually the primary key
        $this->localKey = $localKey ?? ($this->parent->primaryKey ?? 'id');
    }

    /**
     * Get the default foreign key name
     */
    protected function getForeignKeyName()
    {
        $name = (new \ReflectionClass($this->parent))->getShortName();
        return strtolower($name) . '_id';
    }

    /**
     * Get the relationship results
     */
    public function get()
    {
        // Get the local key value from parent
        $localKeyValue = $this->parent->{$this->localKey} ?? null;

        if (is_null($localKeyValue)) {
            return null;
        }

        // Add the constraint for the foreign key
        $this->where($this->foreignKey, $localKeyValue);

        // Execute query
        $sql = $this->toSql();
        $bindings = $this->getBindings();

        $result = $this->db->query($sql, ...$bindings)->fetch();

        if (!$result) {
            return null;
        }

        // Hydrate single model
        $class = get_class($this->related);
        $model = new $class();
        $model->fill((array) $result);
        $model->exists = true;

        return $model;
    }

    /**
     * Set the constraints for an eager load of the relation
     */
    public function addEagerConstraints(array $models)
    {
        // Get all local key values from parent models
        $keys = [];
        foreach ($models as $model) {
            $value = is_object($model) ? $model->{$this->localKey} : $model[$this->localKey];
            if (!is_null($value)) {
                $keys[] = $value;
            }
        }

        if (empty($keys)) {
            // Add impossible constraint
            $this->where($this->foreignKey, '=', null);
            $this->where($this->foreignKey, '!=', null);
        } else {
            // Add whereIn constraint
            $this->whereIn($this->foreignKey, array_unique($keys));
        }
    }

    /**
     * Get the relationship for eager loading
     */
    public function getEager()
    {
        $sql = $this->toSql();
        $bindings = $this->getBindings();

        $results = $this->db->query($sql, ...$bindings)->fetchAll();

        return $this->hydrate($results);
    }

    /**
     * Match the eagerly loaded results to their parents
     */
    public function match(array $models, Collection $results, $relation)
    {
        // Build dictionary of related models keyed by foreign key
        $dictionary = [];
        foreach ($results as $result) {
            $key = is_object($result) ? $result->{$this->foreignKey} : $result[$this->foreignKey];
            $dictionary[$key] = $result;
        }

        // Match related models to their parents
        foreach ($models as $model) {
            $localKeyValue = is_object($model) ? $model->{$this->localKey} : $model[$this->localKey];

            if (isset($dictionary[$localKeyValue])) {
                if (is_object($model)) {
                    $model->relations[$relation] = $dictionary[$localKeyValue];
                } else {
                    $model[$relation] = $dictionary[$localKeyValue];
                }
            } else {
                if (is_object($model)) {
                    $model->relations[$relation] = null;
                } else {
                    $model[$relation] = null;
                }
            }
        }
    }

    /**
     * Create a new instance of the related model
     */
    public function create(array $attributes = [])
    {
        $attributes[$this->foreignKey] = $this->parent->{$this->localKey};

        $class = get_class($this->related);
        return $class::create($attributes);
    }

    /**
     * Save the related model
     */
    public function save($model)
    {
        $model->{$this->foreignKey} = $this->parent->{$this->localKey};

        if ($model->exists) {
            $class = get_class($model);
            return $class::update($model->{$model->primaryKey ?? 'id'}, $model->attributes);
        } else {
            $class = get_class($model);
            return $class::create($model->attributes);
        }
    }
}
