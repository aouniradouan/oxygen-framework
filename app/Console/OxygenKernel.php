<?php

namespace Oxygen\Console;

use Oxygen\Core\Application;

/**
 * OxygenKernel - Console Command Handler
 * 
 * Handles routing and execution of console commands.
 * 
 * @package    Oxygen\Console
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class OxygenKernel
{
    protected $commands = [];
    protected $app;

    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->registerCommands();
    }

    /**
     * Register all available commands
     */
    protected function registerCommands()
    {
        $this->commands = [
            'list' => Commands\ListCommand::class,
            'serve' => Commands\ServeCommand::class,
            'migrate' => Commands\MigrateCommand::class,
            'migrate:rollback' => Commands\MigrateRollbackCommand::class,
            'make:controller' => Commands\MakeControllerCommand::class,
            'make:model' => Commands\MakeModelCommand::class,
            'make:middleware' => Commands\MakeMiddlewareCommand::class,
            'make:migration' => Commands\MakeMigrationCommand::class,
            'make:service' => Commands\MakeServiceCommand::class,
            'install' => Commands\InstallCommand::class,
            'generate:app' => Commands\GenerateAppCommand::class,
            'scaffold:resource' => Commands\ScaffoldResourceCommand::class,
            'make:mvc' => Commands\ScaffoldResourceCommand::class,
            'test:all' => Commands\TestAllCommand::class,
            'test:generate' => Commands\TestGenerateCommand::class,
            'queue:work' => Commands\QueueWorkCommand::class,
            'schedule:run' => Commands\ScheduleRunCommand::class,
            // 'security:scan' => Commands\SecurityScanCommand::class,
            // 'virus:scan' => Commands\VirusScanCommand::class,
            // 'cleanup:logs' => Commands\CleanupLogsCommand::class,
            'docs:generate' => Commands\DocsGenerateCommand::class,
            'framework:update' => Commands\FrameworkUpdateCommand::class,
            // 'restore' => Commands\RestoreCommand::class,
            'websocket' => Commands\WebSocketCommand::class,
        ];
    }

    /**
     * Handle incoming console command
     */
    public function handle($argv)
    {
        // Remove script name
        array_shift($argv);

        // Get command name
        $commandName = $argv[0] ?? 'list';
        $arguments = array_slice($argv, 1);

        // Check if command exists
        if (!isset($this->commands[$commandName])) {
            echo "Command '{$commandName}' not found.\n";
            echo "Run 'php oxygen list' to see all available commands.\n";
            return;
        }

        // Execute command
        try {
            $commandClass = $this->commands[$commandName];
            $command = new $commandClass();
            $command->execute($arguments);
        } catch (\Exception $e) {
            echo "Error executing command: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}
