<?php

namespace Oxygen\Core\Database\Relations;

use Oxygen\Core\Database\Collection;

/**
 * Relation - Base Relationship Class
 * 
 * Abstract base class for all database relationships.
 * Provides query building, constraint methods, and eager loading support.
 * 
 * @package    Oxygen\Core\Database\Relations
 * @author     Redwan Aouni
 * @version    1.0.0
 */
abstract class Relation
{
    /**
     * The parent model instance
     */
    protected $parent;

    /**
     * The related model instance
     */
    protected $related;

    /**
     * The database connection
     */
    protected $db;

    /**
     * The query constraints
     */
    protected $constraints = [];

    /**
     * The columns to select
     */
    protected $select = ['*'];

    /**
     * The where clauses
     */
    protected $wheres = [];

    /**
     * The order by clauses
     */
    protected $orders = [];

    /**
     * The limit
     */
    protected $limit;

    /**
     * The offset
     */
    protected $offset;

    /**
     * Create a new relation instance
     */
    public function __construct($related, $parent)
    {
        $this->related = is_string($related) ? new $related() : $related;
        $this->parent = $parent;
        $this->db = \Oxygen\Core\Application::getInstance()->make('db');
    }

    /**
     * Set the select clause
     */
    public function select($columns = ['*'])
    {
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a basic where clause
     */
    public function where($column, $operator = null, $value = null)
    {
        // Allow where($column, $value) syntax
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Add an "or where" clause
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'or'
        ];

        return $this;
    }

    /**
     * Add a where in clause
     */
    public function whereIn($column, array $values)
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Add a where not in clause
     */
    public function whereNotIn($column, array $values)
    {
        $this->wheres[] = [
            'type' => 'notIn',
            'column' => $column,
            'values' => $values,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Add a where null clause
     */
    public function whereNull($column)
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Add a where not null clause
     */
    public function whereNotNull($column)
    {
        $this->wheres[] = [
            'type' => 'notNull',
            'column' => $column,
            'boolean' => 'and'
        ];

        return $this;
    }

    /**
     * Add an order by clause
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'ASC' : 'DESC'
        ];

        return $this;
    }

    /**
     * Set the limit
     */
    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    /**
     * Alias for limit
     */
    public function take($value)
    {
        return $this->limit($value);
    }

    /**
     * Set the offset
     */
    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * Alias for offset
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Get the first result
     */
    public function first()
    {
        $results = $this->take(1)->get();
        return $results->first();
    }

    /**
     * Find a model by its primary key
     */
    public function find($id)
    {
        return $this->where($this->related->primaryKey ?? 'id', $id)->first();
    }

    /**
     * Get the count of results
     */
    public function count()
    {
        $sql = $this->toSql(true);
        $bindings = $this->getBindings();

        $result = $this->db->query($sql, ...$bindings)->fetch();
        return $result ? (int) $result->count : 0;
    }

    /**
     * Determine if any rows exist
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * Build the SQL query
     */
    protected function toSql($count = false)
    {
        $table = $this->related->table ?? $this->related->guessTableName();

        if ($count) {
            $sql = "SELECT COUNT(*) as count FROM {$table}";
        } else {
            $columns = implode(', ', $this->select);
            $sql = "SELECT {$columns} FROM {$table}";
        }

        // Add where clauses
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }

        // Add order by
        if (!empty($this->orders) && !$count) {
            $orderClauses = [];
            foreach ($this->orders as $order) {
                $orderClauses[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClauses);
        }

        // Add limit and offset
        if ($this->limit && !$count) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset && !$count) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Build the where clause
     */
    protected function buildWheres()
    {
        $clauses = [];

        foreach ($this->wheres as $index => $where) {
            $boolean = $index === 0 ? '' : " {$where['boolean']} ";

            switch ($where['type']) {
                case 'basic':
                    $clauses[] = $boolean . "{$where['column']} {$where['operator']} ?";
                    break;
                case 'in':
                    $placeholders = implode(',', array_fill(0, count($where['values']), '?'));
                    $clauses[] = $boolean . "{$where['column']} IN ({$placeholders})";
                    break;
                case 'notIn':
                    $placeholders = implode(',', array_fill(0, count($where['values']), '?'));
                    $clauses[] = $boolean . "{$where['column']} NOT IN ({$placeholders})";
                    break;
                case 'null':
                    $clauses[] = $boolean . "{$where['column']} IS NULL";
                    break;
                case 'notNull':
                    $clauses[] = $boolean . "{$where['column']} IS NOT NULL";
                    break;
            }
        }

        return implode('', $clauses);
    }

    /**
     * Get the query bindings
     */
    protected function getBindings()
    {
        $bindings = [];

        foreach ($this->wheres as $where) {
            if ($where['type'] === 'basic') {
                $bindings[] = $where['value'];
            } elseif (in_array($where['type'], ['in', 'notIn'])) {
                $bindings = array_merge($bindings, $where['values']);
            }
        }

        return $bindings;
    }

    /**
     * Hydrate models from raw results
     */
    protected function hydrate(array $results)
    {
        $models = [];
        $class = get_class($this->related);

        foreach ($results as $result) {
            $model = new $class();
            $model->fill((array) $result);
            $model->exists = true;
            $models[] = $model;
        }

        return new Collection($models);
    }

    /**
     * Get the relationship results
     * Must be implemented by child classes
     */
    abstract public function get();

    /**
     * Set the constraints for an eager load of the relation
     * Must be implemented by child classes
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Get the relationship for eager loading
     * Must be implemented by child classes
     */
    abstract public function getEager();

    /**
     * Match the eagerly loaded results to their parents
     * Must be implemented by child classes
     */
    abstract public function match(array $models, Collection $results, $relation);
}
