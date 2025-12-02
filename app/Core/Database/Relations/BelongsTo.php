<?php

namespace Oxygen\Core\Database\Relations;

use Oxygen\Core\Database\Collection;

/**
 * BelongsTo - Inverse One-to-One or Many Relationship
 * 
 * Defines an inverse relationship where the current model belongs to another model.
 * Example: A Post belongs to a User
 * 
 * @package    Oxygen\Core\Database\Relations
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class BelongsTo extends Relation
{
    /**
     * The foreign key of the parent model
     */
    protected $foreignKey;

    /**
     * The associated key on the parent model
     */
    protected $ownerKey;

    /**
     * Create a new belongs to relationship instance
     */
    public function __construct($related, $parent, $foreignKey = null, $ownerKey = null)
    {
        parent::__construct($related, $parent);

        // Auto-detect foreign key: user_id for User model
        $this->foreignKey = $foreignKey ?? $this->getForeignKeyName();

        // Owner key is usually the primary key
        $this->ownerKey = $ownerKey ?? ($this->related->primaryKey ?? 'id');
    }

    /**
     * Get the default foreign key name
     */
    protected function getForeignKeyName()
    {
        $name = (new \ReflectionClass($this->related))->getShortName();
        return strtolower($name) . '_id';
    }

    /**
     * Get the relationship results
     */
    public function get()
    {
        // Get the foreign key value from parent
        $foreignKeyValue = $this->parent->{$this->foreignKey} ?? null;

        if (is_null($foreignKeyValue)) {
            return null;
        }

        // Add the constraint for the owner key
        $this->where($this->ownerKey, $foreignKeyValue);

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
        // Get all foreign key values from parent models
        $keys = [];
        foreach ($models as $model) {
            $value = is_object($model) ? $model->{$this->foreignKey} : $model[$this->foreignKey];
            if (!is_null($value)) {
                $keys[] = $value;
            }
        }

        if (empty($keys)) {
            // Add impossible constraint to return no results
            $this->where($this->ownerKey, '=', null);
            $this->where($this->ownerKey, '!=', null);
        } else {
            // Add whereIn constraint for all keys
            $this->whereIn($this->ownerKey, array_unique($keys));
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
        // Build dictionary of related models keyed by owner key
        $dictionary = [];
        foreach ($results as $result) {
            $key = is_object($result) ? $result->{$this->ownerKey} : $result[$this->ownerKey];
            $dictionary[$key] = $result;
        }

        // Match related models to their parents
        foreach ($models as $model) {
            $foreignKeyValue = is_object($model) ? $model->{$this->foreignKey} : $model[$this->foreignKey];

            if (isset($dictionary[$foreignKeyValue])) {
                if (is_object($model)) {
                    $model->relations[$relation] = $dictionary[$foreignKeyValue];
                } else {
                    $model[$relation] = $dictionary[$foreignKeyValue];
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
     * Associate the model instance to the given parent
     */
    public function associate($model)
    {
        $this->parent->{$this->foreignKey} = $model ? $model->{$this->ownerKey} : null;

        if ($model) {
            $this->parent->relations[get_class($this->related)] = $model;
        }

        return $this->parent;
    }

    /**
     * Dissociate the model from its parent
     */
    public function dissociate()
    {
        $this->parent->{$this->foreignKey} = null;
        unset($this->parent->relations[get_class($this->related)]);

        return $this->parent;
    }
}
