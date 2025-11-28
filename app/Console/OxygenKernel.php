<?php

namespace Oxygen\Console;

/**
 * OxygenKernel - Console Command Handler
 * 
 * This class manages all console commands and routes CLI input to the
 * appropriate command handlers.
 * 
 * @package    Oxygen\Console
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class OxygenKernel
{
    /**
     * Registered commands
     * 
     * @var array
     */
    protected $commands = [];

    /**
     * Constructor - Register all commands
     */
    public function __construct()
    {
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->commands = [
            // Resource Generation Commands (resource:action format)
            'controller:create' => \Oxygen\Console\Commands\MakeControllerCommand::class,
            'model:create' => \Oxygen\Console\Commands\MakeModelCommand::class,
            'middleware:create' => \Oxygen\Console\Commands\MakeMiddlewareCommand::class,
            'service:create' => \Oxygen\Console\Commands\MakeServiceCommand::class,
            'migration:create' => \Oxygen\Console\Commands\MakeMigrationCommand::class,

            // 🚀 POWERFUL: Complete Resource Scaffolding
            'resource:scaffold' => \Oxygen\Console\Commands\ScaffoldResourceCommand::class,
            'make:mvc' => \Oxygen\Console\Commands\ScaffoldResourceCommand::class,

            // 🌟 INTELLIGENT: Full Application Generator
            'generate:app' => \Oxygen\Console\Commands\GenerateAppCommand::class,

            // Database Commands
            'migrate' => \Oxygen\Console\Commands\MigrateCommand::class,
            'migrate:rollback' => \Oxygen\Console\Commands\MigrateRollbackCommand::class,

            // Server Commands
            'serve' => \Oxygen\Console\Commands\ServeCommand::class,
            'websocket:serve' => \Oxygen\Console\Commands\WebSocketCommand::class,

            // Queue Commands
            'queue:work' => \Oxygen\Console\Commands\QueueWorkCommand::class,

            // Documentation Commands
            'docs:generate' => \Oxygen\Console\Commands\DocsGenerateCommand::class,

            // 🔒 SECURITY: Security Scanner & Virus Detection
            // 'security:scan' => \Oxygen\Console\Commands\SecurityScanCommand::class,
            // 'security:restore' => \Oxygen\Console\Commands\RestoreCommand::class,
            // 'virus:scan' => \Oxygen\Console\Commands\VirusScanCommand::class,

            // 🧪 TESTING: Auto-Generated Testing System
            // 'test:generate' => \Oxygen\Console\Commands\TestGenerateCommand::class,
            // 'test:all' => \Oxygen\Console\Commands\TestAllCommand::class,
            // 'test:coverage' => \Oxygen\Console\Commands\TestAllCommand::class, // Alias for test:all --coverage

            // 🕒 SCHEDULER: Task Scheduling System
            'schedule:run' => \Oxygen\Console\Commands\ScheduleRunCommand::class,

            // 🚀 FRAMEWORK: Update System
            'framework:update' => \Oxygen\Console\Commands\FrameworkUpdateCommand::class,

            // Utility Commands
            'list' => \Oxygen\Console\Commands\ListCommand::class,
            'logs:cleanup' => \Oxygen\Console\Commands\CleanupLogsCommand::class,
        ];
    }

    /**
     * Handle the console command
     */
    public function handle($argv)
    {
        // Remove script name
        array_shift($argv);

        // Get command name
        $commandName = $argv[0] ?? 'list';

        // Check if command exists
        if (!isset($this->commands[$commandName])) {
            $this->error("Command '{$commandName}' not found.");
            $this->info("Run 'php oxygen list' to see all available commands.");
            return;
        }

        // Get command arguments
        $arguments = array_slice($argv, 1);

        // Execute command
        $commandClass = $this->commands[$commandName];
        $command = new $commandClass();
        $command->execute($arguments);
    }

    /**
     * Display an error message
     * 
     * @param string $message Error message
     * @return void
     */
    protected function error($message)
    {
        echo "\033[31m✗ {$message}\033[0m\n";
    }

    /**
     * Display an info message
     * 
     * @param string $message Info message
     * @return void
     */
    protected function info($message)
    {
        echo "\033[36mℹ {$message}\033[0m\n";
    }
}
