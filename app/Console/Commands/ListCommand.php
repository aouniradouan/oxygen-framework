<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

/**
 * ListCommand - Display all available commands
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class ListCommand extends Command
{
    public function execute($arguments)
    {
        echo "\n";
        echo "\033[36m╔═══════════════════════════════════════════════════════════╗\033[0m\n";
        echo "\033[36m║         OxygenFramework CLI - Available Commands         ║\033[0m\n";
        echo "\033[36m╚═══════════════════════════════════════════════════════════╝\033[0m\n";
        echo "\n";

        $commands = [
            'list'               => 'Show this help message',
            'serve'              => 'Start the development server',
            'websocket'          => 'Start WebSocket server',
            'migrate'            => 'Run database migrations',
            'migrate:rollback'   => 'Rollback the last migration batch',

            'make:controller'    => 'Create a new controller class',
            'make:model'         => 'Create a new model class',
            'make:middleware'    => 'Create a new middleware class',
            'make:migration'     => 'Create a new migration file',
            'make:service'       => 'Create a new service class',

            'generate:app'       => '🌟 Generate complete application (interactive)',
            'scaffold:resource'  => '🚀 Generate complete CRUD resource (interactive)',
            'make:mvc'           => '🚀 Generate complete CRUD resource (interactive)',

            'install'            => 'Run framework installer',
            'queue:work'         => 'Process queued jobs',
            'schedule:run'       => 'Execute scheduled tasks',

            'test:all'           => 'Run all tests',
            'test:generate'      => 'Generate test files',

            'docs:generate'      => 'Generate project documentation',
            'framework:update'   => 'Update Oxygen framework core',
        ];

        foreach ($commands as $command => $description) {
            printf("  \033[32m%-22s\033[0m %s\n", $command, $description);
        }

        echo "\n";
        echo "\033[33mUsage:\033[0m\n";
        echo "  php oxygen <command> [arguments]\n";
        echo "\n";
        echo "\033[33mExamples:\033[0m\n";
        echo "  php oxygen controller:create UserController\n";
        echo "  php oxygen model:create Post\n";
        echo "  php oxygen serve --port=8080\n";
        echo "\n";
    }
}
