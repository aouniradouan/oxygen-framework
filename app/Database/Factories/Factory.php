<?php

namespace Oxygen\Database\Factories;

use Faker\Factory as FakerFactory;

/**
 * Model Factory Base Class
 * 
 * Provides factory methods for generating fake data.
 * 
 * @package    Oxygen\Database\Factories
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Factory
{
    /**
     * Faker instance
     * 
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Model class
     * 
     * @var string
     */
    protected $model;

    /**
     * Create a new factory instance
     */
    public function __construct()
    {
        if (class_exists(FakerFactory::class)) {
            $this->faker = FakerFactory::create();
        }
    }

    /**
     * Define the model's default state
     * 
     * @return array
     */
    abstract public function definition();

    /**
     * Create a new model instance
     * 
     * @param array $attributes
     * @return \Oxygen\Core\Model
     */
    public function make($attributes = [])
    {
        $attributes = array_merge($this->definition(), $attributes);
        $model = $this->model;
        return new $model($attributes);
    }

    /**
     * Create and persist a new model instance
     * 
     * @param array $attributes
     * @return \Oxygen\Core\Model
     */
    public function create($attributes = [])
    {
        $model = $this->make($attributes);
        $model->save();
        return $model;
    }

    /**
     * Create multiple model instances
     * 
     * @param int $count
     * @param array $attributes
     * @return array
     */
    public function makeMany($count, $attributes = [])
    {
        $models = [];
        for ($i = 0; $i < $count; $i++) {
            $models[] = $this->make($attributes);
        }
        return $models;
    }

    /**
     * Create and persist multiple model instances
     * 
     * @param int $count
     * @param array $attributes
     * @return array
     */
    public function createMany($count, $attributes = [])
    {
        $models = [];
        for ($i = 0; $i < $count; $i++) {
            $models[] = $this->create($attributes);
        }
        return $models;
    }
}

