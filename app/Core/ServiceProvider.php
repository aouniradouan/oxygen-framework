<?php

namespace Oxygen\Core;

/**
 * Service Provider Base Class
 * 
 * Service providers are the central place to configure your application.
 * They register services, bindings, and configure the framework.
 * 
 * @package    Oxygen\Core
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class ServiceProvider
{
    /**
     * The application instance
     * 
     * @var Application
     */
    protected $app;

    /**
     * Create a new service provider instance
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services
     * 
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services
     * 
     * @return void
     */
    public function boot()
    {
        //
    }
}

