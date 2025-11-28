<?php

namespace Oxygen\Core\Database;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Collection - Model Collection Class
 * 
 * A powerful collection class for working with arrays of models.
 * Provides Laravel-like collection methods with additional features.
 * 
 * @package    Oxygen\Core\Database
 * @author     Redwan Aouni
 * @version    1.0.0
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * The items contained in the collection
     */
    protected $items = [];

    /**
     * Create a new collection
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Get all items in the collection
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get the first item from the collection
     */
    public function first(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($this->items) ? $default : reset($this->items);
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the last item from the collection
     */
    public function last(callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            return empty($this->items) ? $default : end($this->items);
        }

        return $this->reverse()->first($callback, $default);
    }

    /**
     * Map over each item in the collection
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Filter items by the given callback
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the values of a given key
     */
    public function pluck($value, $key = null)
    {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = is_object($item) ? $item->{$value} : $item[$value];

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_object($item) ? $item->{$key} : $item[$key];
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    /**
     * Chunk the collection into chunks of the given size
     */
    public function chunk($size)
    {
        $chunks = [];

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Reverse items order
     */
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Sort through each item with a callback
     */
    public function sort(callable $callback = null)
    {
        $items = $this->items;

        $callback ? uasort($items, $callback) : asort($items);

        return new static($items);
    }

    /**
     * Sort items by a key
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        foreach ($this->items as $key => $value) {
            $results[$key] = is_callable($callback) ? $callback($value, $key) : (is_object($value) ? $value->{$callback} : $value[$callback]);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Get unique items
     */
    public function unique($key = null)
    {
        if (is_null($key)) {
            return new static(array_unique($this->items, SORT_REGULAR));
        }

        $exists = [];
        return $this->filter(function ($item) use ($key, &$exists) {
            $value = is_object($item) ? $item->{$key} : $item[$key];

            if (in_array($value, $exists)) {
                return false;
            }

            $exists[] = $value;
            return true;
        });
    }

    /**
     * Get the sum of the given values
     */
    public function sum($callback = null)
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + (is_callable($callback) ? $callback($item) : (is_object($item) ? $item->{$callback} : $item[$callback]));
        }, 0);
    }

    /**
     * Reduce the collection to a single value
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Determine if an item exists in the collection
     */
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            if (is_callable($key)) {
                foreach ($this->items as $item) {
                    if ($key($item)) {
                        return true;
                    }
                }
                return false;
            }

            return in_array($key, $this->items);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get the items with the specified keys
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

    /**
     * Get all items except those with the specified keys
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(array_diff_key($this->items, array_flip($keys)));
    }

    /**
     * Eager load relationships on the collection
     */
    public function load($relations)
    {
        if (empty($this->items)) {
            return $this;
        }

        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $name => $constraints) {
            // Handle nested relations
            if (is_numeric($name)) {
                $name = $constraints;
                $constraints = null;
            }

            $this->loadRelation($name, $constraints);
        }

        return $this;
    }

    /**
     * Load a relationship on the collection
     */
    protected function loadRelation($name, $constraints = null)
    {
        $relation = $this->first()->{$name}();

        if (method_exists($relation, 'addEagerConstraints')) {
            $relation->addEagerConstraints($this->items);
        }

        if ($constraints) {
            $constraints($relation);
        }

        if (method_exists($relation, 'getEager')) {
            $results = $relation->getEager();
            $relation->match($this->items, $results, $name);
        }
    }

    /**
     * Get an operator checker callback
     */
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = is_object($item) ? $item->{$key} : $item[$key];

            switch ($operator) {
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
            }

            return false;
        };
    }

    /**
     * Get the collection as an array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return is_object($value) && method_exists($value, 'toArray') ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Get the collection as JSON
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Count the number of items in the collection
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Determine if the collection is empty
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty
     */
    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    /**
     * Get an iterator for the items
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset
     */
    public function offsetExists($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
