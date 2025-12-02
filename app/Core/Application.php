<?php

namespace Oxygen\Core;

use Dotenv\Dotenv;
use Bramus\Router\Router;
use Oxygen\Core\Events\Dispatcher;
use Oxygen\Core\Log\Logger;

class Application extends Container
{
    protected $basePath;
    protected static $instance;
    protected $serviceProviders = [];
    protected $events;
    protected $logger;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->registerBaseBindings();
        $this->loadEnvironment();
        $this->initializeConfig();
        $this->initializeSession();
        $this->registerErrorHandling();
        $this->registerCoreServices();
        $this->registerEventDispatcher();
        $this->registerLogger();
        $this->registerServiceProviders();
    }

    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this->instances['app'] = $this;
        $this->instances[Container::class] = $this;
    }

    protected function loadEnvironment()
    {
        if (file_exists($this->basePath . '/.env')) {
            $dotenv = Dotenv::createImmutable($this->basePath);
            $dotenv->load();
        }
    }

    /**
     * Initialize the configuration system
     */
    protected function initializeConfig()
    {
        OxygenConfig::init($this->basePath . '/config');
    }

    /**
     * Initialize the session system
     */
    protected function initializeSession()
    {
        OxygenSession::start();
    }

    /**
     * Register error handling
     */
    protected function registerErrorHandling()
    {
        $debug = OxygenConfig::get('errors.dev_mode', false);

        if ($debug) {
            // Development mode
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            if (class_exists(\Whoops\Run::class)) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->register();
            }
        } else {
            // Production mode
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }

    public function basePath($path = '')
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    public function registerCoreServices()
    {
        // Bind Request
        $this->singleton(Request::class, function () {
            return Request::capture();
        });

        // Bind Router
        $this->singleton(Router::class, function () {
            $router = new Router();

            // Detect base path for subdirectory installations
            if (isset($_SERVER['SCRIPT_NAME'])) {
                $scriptName = dirname($_SERVER['SCRIPT_NAME']);
                // If script is in a subdirectory (not root), set base path
                if ($scriptName !== '/' && $scriptName !== '\\' && $scriptName !== '.') {
                    $router->setBasePath($scriptName);
                }
            }

            return $router;
        });

        // Bind Database (Nette)
        $this->singleton('db', function ($app) {
            $default = OxygenConfig::get('database.default');
            $connection = OxygenConfig::get("database.connections.$default");

            return new \Nette\Database\Connection(
                $connection['dsn'],
                $connection['username'],
                $connection['password']
            );
        });

        // Bind Auth
        $this->singleton(Auth::class, function () {
            return new Auth();
        });

        // Bind CSRF
        $this->singleton(CSRF::class, function () {
            return new CSRF();
        });

        // Bind View
        $this->singleton(View::class, function () {
            return new View();
        });

        // Bind Form
        $this->singleton(Form::class, function () {
            return new Form();
        });

        // Bind Lang
        $this->singleton(Lang::class, function () {
            return new Lang();
        });

        // Bind Event Dispatcher
        $this->singleton(Dispatcher::class, function () {
            return $this->events ?: new Dispatcher();
        });

        // Bind Logger
        $this->singleton(Logger::class, function () {
            return $this->logger ?: new Logger($this->basePath('storage/logs'));
        });
    }

    /**
     * Register event dispatcher
     */
    protected function registerEventDispatcher()
    {
        $this->events = new Dispatcher();
    }

    /**
     * Register logger
     */
    protected function registerLogger()
    {
        $this->logger = new Logger($this->basePath('storage/logs'));
    }

    /**
     * Register service providers
     */
    protected function registerServiceProviders()
    {
        $providers = $this->getServiceProviders();
        
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    /**
     * Get service providers from config
     * 
     * @return array
     */
    protected function getServiceProviders()
    {
        return OxygenConfig::get('app.providers', []);
    }

    /**
     * Register a service provider
     * 
     * @param string $provider
     * @return void
     */
    public function registerProvider($provider)
    {
        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        $provider->register();
        $this->serviceProviders[] = $provider;
    }

    /**
     * Boot service providers
     * 
     * @return void
     */
    public function bootServiceProviders()
    {
        foreach ($this->serviceProviders as $provider) {
            $provider->boot();
        }
    }

    /**
     * Get event dispatcher
     * 
     * @return Dispatcher
     */
    public function getEventDispatcher()
    {
        return $this->events;
    }

    /**
     * Get logger
     * 
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function run()
    {
        // Boot service providers
        $this->bootServiceProviders();

        // Load web routes
        require_once $this->basePath('routes/web.php');

        // Load API routes
        if (file_exists($this->basePath('routes/api.php'))) {
            require_once $this->basePath('routes/api.php');
        }

        // Run the router
        $router = $this->make(Router::class);
        $router->run();
    }

    public static function setInstance($container = null)
    {
        return static::$instance = $container;
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            throw new \Exception("Application not initialized");
        }
        return static::$instance;
    }
}
