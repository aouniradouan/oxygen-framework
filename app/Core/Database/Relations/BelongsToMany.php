<?php

namespace Oxygen\Core\Database\Relations;

use Oxygen\Core\Database\Collection;

/**
 * BelongsToMany - Many-to-Many Relationship
 * 
 * Defines a many-to-many relationship using a pivot table.
 * Example: A User belongs to many Roles (via role_user pivot table)
 * 
 * @package    Oxygen\Core\Database\Relations
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class BelongsToMany extends Relation
{
    /**
     * The pivot table name
     */
    protected $table;

    /**
     * The foreign key of the parent model on the pivot table
     */
    protected $foreignPivotKey;

    /**
     * The foreign key of the related model on the pivot table
     */
    protected $relatedPivotKey;

    /**
     * The pivot columns to retrieve
     */
    protected $pivotColumns = [];

    /**
     * Indicates if timestamps should be retrieved from pivot
     */
    protected $withTimestamps = false;

    /**
     * Create a new belongs to many relationship instance
     */
    public function __construct($related, $parent, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
    {
        parent::__construct($related, $parent);

        $this->table = $table ?? $this->guessTableName();
        $this->foreignPivotKey = $foreignPivotKey ?? $this->getForeignPivotKeyName();
        $this->relatedPivotKey = $relatedPivotKey ?? $this->getRelatedPivotKeyName();
    }

    /**
     * Guess the pivot table name
     */
    protected function guessTableName()
    {
        $models = [
            strtolower((new \ReflectionClass($this->parent))->getShortName()),
            strtolower((new \ReflectionClass($this->related))->getShortName())
        ];

        sort($models);
        return implode('_', $models);
    }

    /**
     * Get the foreign pivot key name
     */
    protected function getForeignPivotKeyName()
    {
        $name = (new \ReflectionClass($this->parent))->getShortName();
        return strtolower($name) . '_id';
    }

    /**
     * Get the related pivot key name
     */
    protected function getRelatedPivotKeyName()
    {
        $name = (new \ReflectionClass($this->related))->getShortName();
        return strtolower($name) . '_id';
    }

    /**
     * Specify additional pivot columns to retrieve
     */
    public function withPivot(...$columns)
    {
        $this->pivotColumns = array_merge($this->pivotColumns, $columns);
        return $this;
    }

    /**
     * Indicate that the pivot table has creation and update timestamps
     */
    public function withTimestamps()
    {
        $this->withTimestamps = true;
        return $this->withPivot('created_at', 'updated_at');
    }

    /**
     * Get the relationship results
     */
    public function get()
    {
        $parentKey = $this->parent->{$this->parent->primaryKey ?? 'id'};

        if (is_null($parentKey)) {
            return new Collection([]);
        }

        // Build the query with join
        $relatedTable = $this->related->table ?? $this->related->guessTableName();
        $relatedPrimaryKey = $this->related->primaryKey ?? 'id';

        // Select columns from related table and pivot
        $selectColumns = [];
        foreach ($this->select as $column) {
            if ($column === '*') {
                $selectColumns[] = "{$relatedTable}.*";
            } else {
                $selectColumns[] = "{$relatedTable}.{$column}";
            }
        }

        // Add pivot columns
        $selectColumns[] = "{$this->table}.{$this->foreignPivotKey} as pivot_{$this->foreignPivotKey}";
        $selectColumns[] = "{$this->table}.{$this->relatedPivotKey} as pivot_{$this->relatedPivotKey}";

        foreach ($this->pivotColumns as $column) {
            $selectColumns[] = "{$this->table}.{$column} as pivot_{$column}";
        }

        $sql = "SELECT " . implode(', ', $selectColumns) .
            " FROM {$relatedTable}" .
            " INNER JOIN {$this->table} ON {$relatedTable}.{$relatedPrimaryKey} = {$this->table}.{$this->relatedPivotKey}" .
            " WHERE {$this->table}.{$this->foreignPivotKey} = ?";

        $bindings = [$parentKey];

        // Add additional where clauses
        if (!empty($this->wheres)) {
            $sql .= ' AND ' . $this->buildWheres();
            $bindings = array_merge($bindings, $this->getBindings());
        }

        // Add order by
        if (!empty($this->orders)) {
            $orderClauses = [];
            foreach ($this->orders as $order) {
                $orderClauses[] = "{$relatedTable}.{$order['column']} {$order['direction']}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }

        // Add limit and offset
        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset) {
            $sql .= " OFFSET {$this->offset}";
        }

        $results = $this->db->query($sql, ...$bindings)->fetchAll();

        return $this->hydrateWithPivot($results);
    }

    /**
     * Hydrate models with pivot data
     */
    protected function hydrateWithPivot(array $results)
    {
        $models = [];
        $class = get_class($this->related);

        foreach ($results as $result) {
            $model = new $class();
            $attributes = [];
            $pivotAttributes = [];

            foreach ((array) $result as $key => $value) {
                if (strpos($key, 'pivot_') === 0) {
                    $pivotAttributes[substr($key, 6)] = $value;
                } else {
                    $attributes[$key] = $value;
                }
            }

            $model->fill($attributes);
            $model->exists = true;
            $model->pivot = (object) $pivotAttributes;

            $models[] = $model;
        }

        return new Collection($models);
    }

    /**
     * Set the constraints for an eager load of the relation
     */
    public function addEagerConstraints(array $models)
    {
        // This will be handled in getEager
    }

    /**
     * Get the relationship for eager loading
     */
    public function getEager()
    {
        // Get all parent keys
        $keys = [];
        // Note: $this->parent is not available in eager loading context
        // We need to get keys from the query builder or pass them differently

        // For now, return empty collection
        // This needs to be called with proper context
        return new Collection([]);
    }

    /**
     * Match the eagerly loaded results to their parents
     */
    public function match(array $models, Collection $results, $relation)
    {
        // Build dictionary of related models keyed by pivot foreign key
        $dictionary = [];
        foreach ($results as $result) {
            $key = $result->pivot->{$this->foreignPivotKey};

            if (!isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }

            $dictionary[$key][] = $result;
        }

        // Match related models to their parents
        foreach ($models as $model) {
            $parentKey = is_object($model) ? $model->{$model->primaryKey ?? 'id'} : $model[$model->primaryKey ?? 'id'];

            if (isset($dictionary[$parentKey])) {
                $collection = new Collection($dictionary[$parentKey]);

                if (is_object($model)) {
                    $model->relations[$relation] = $collection;
                } else {
                    $model[$relation] = $collection;
                }
            } else {
                if (is_object($model)) {
                    $model->relations[$relation] = new Collection([]);
                } else {
                    $model[$relation] = new Collection([]);
                }
            }
        }
    }

    /**
     * Attach a model to the parent
     */
    public function attach($id, array $attributes = [])
    {
        $parentKey = $this->parent->{$this->parent->primaryKey ?? 'id'};

        $record = [
            $this->foreignPivotKey => $parentKey,
            $this->relatedPivotKey => $id
        ];

        if ($this->withTimestamps) {
            $now = date('Y-m-d H:i:s');
            $record['created_at'] = $now;
            $record['updated_at'] = $now;
        }

        $record = array_merge($record, $attributes);

        $this->db->query("INSERT INTO {$this->table} ?", $record);
    }

    /**
     * Detach models from the parent
     */
    public function detach($ids = null)
    {
        $parentKey = $this->parent->{$this->parent->primaryKey ?? 'id'};

        if (is_null($ids)) {
            // Detach all
            $this->db->query(
                "DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = ?",
                $parentKey
            );
        } else {
            $ids = is_array($ids) ? $ids : [$ids];
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            $this->db->query(
                "DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = ? AND {$this->relatedPivotKey} IN ({$placeholders})",
                $parentKey,
                ...$ids
            );
        }
    }

    /**
     * Sync the intermediate tables with a list of IDs
     */
    public function sync(array $ids)
    {
        // Get current IDs
        $current = $this->get()->pluck($this->related->primaryKey ?? 'id')->all();

        // Determine what to detach and attach
        $detach = array_diff($current, $ids);
        $attach = array_diff($ids, $current);

        if (!empty($detach)) {
            $this->detach($detach);
        }

        foreach ($attach as $id) {
            $this->attach($id);
        }

        return [
            'attached' => $attach,
            'detached' => $detach,
            'updated' => []
        ];
    }

    /**
     * Toggle the attachment of models
     */
    public function toggle(array $ids)
    {
        $current = $this->get()->pluck($this->related->primaryKey ?? 'id')->all();

        $detach = array_intersect($current, $ids);
        $attach = array_diff($ids, $current);

        if (!empty($detach)) {
            $this->detach($detach);
        }

        foreach ($attach as $id) {
            $this->attach($id);
        }

        return [
            'attached' => $attach,
            'detached' => $detach
        ];
    }
}
