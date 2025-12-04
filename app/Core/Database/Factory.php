<?php

namespace Oxygen\Core\Database;

use Oxygen\Core\Model;

/**
 * Factory - Base Model Factory Class
 * 
 * Abstract class for model factories to generate dummy data.
 * 
 * @package    Oxygen\Core\Database
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Factory
{
    /**
     * The model that this factory is for.
     * 
     * @var string
     */
    protected $model;

    /**
     * The number of models to create.
     * 
     * @var int
     */
    protected $count = 1;

    /**
     * The Faker instance.
     * 
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Create a new factory instance.
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    abstract public function definition();

    /**
     * Set the number of models to create.
     * 
     * @param int $count
     * @return $this
     */
    public function count($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * Create instances and save them to the database.
     * 
     * @param array $attributes
     * @return \Oxygen\Core\Database\Collection|Model
     */
    public function create(array $attributes = [])
    {
        $results = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data = array_merge($this->definition(), $attributes);
            $modelClass = $this->model;
            $results[] = $modelClass::create($data);
        }

        if ($this->count === 1) {
            return $results[0];
        }

        return new Collection($results);
    }

    /**
     * Make instances without saving them.
     * 
     * @param array $attributes
     * @return \Oxygen\Core\Database\Collection|Model
     */
    public function make(array $attributes = [])
    {
        $results = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data = array_merge($this->definition(), $attributes);
            $modelClass = $this->model;
            $instance = new $modelClass();
            foreach ($data as $key => $value) {
                $instance->$key = $value;
            }
            $results[] = $instance;
        }

        if ($this->count === 1) {
            return $results[0];
        }

        return new Collection($results);
    }
}
