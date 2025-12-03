<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;
use Oxygen\Core\Schedule;
use Oxygen\Core\Kernel;

/**
 * ScheduleRunCommand - Run Scheduled Tasks
 * 
 * Usage: php oxygen schedule:run
 * Add to system cron: * * * * * php /path/to/oxygen schedule:run
 * 
 * @package    Oxygen\Console\Commands
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    1.0.0
 */
class ScheduleRunCommand extends Command
{
    protected $name = 'schedule:run';
    protected $description = 'Run the scheduled commands';

    public function execute($args = [])
    {
        $schedule = new Schedule();

        // Load defined tasks
        $this->defineTasks($schedule);

        $events = $schedule->getEvents();
        $ran = false;

        foreach ($events as $event) {
            if ($event->isDue()) {
                $this->runEvent($event);
                $ran = true;
            }
        }

        if (!$ran) {
            $this->info("No tasks due at this time.");
        }
    }

    protected function defineTasks(Schedule $schedule)
    {
        // 1. Command Example: Run security scan daily at 2:00 AM
        // $schedule->command('security:scan')->dailyAt('02:00')
        //     ->description('Daily Security Scan');

        // 2. Command Example: Run deep virus scan weekly on Sunday
        // $schedule->command('virus:scan --deep')->weekly()
        //     ->description('Weekly Deep Virus Scan');

        // 3. Command Example: Clean up logs every day (The one we just created)
        $schedule->command('logs:cleanup')->daily()
            ->description('Clean up old logs');

        // 4. Closure Example: Simple logging every minute
        $schedule->call(function () {
            // This code runs directly in the scheduler process
            $file = getcwd() . '/storage/logs/heartbeat.log';
            if (!is_dir(dirname($file)))
                mkdir(dirname($file), 0755, true);
            file_put_contents($file, date('Y-m-d H:i:s') . " - Scheduler is alive\n", FILE_APPEND);
        })->everyMinute()->description('System Heartbeat');
    }

    protected function runEvent($event)
    {
        $this->info("Running task: " . ($event->description ?? 'Unnamed Task'));

        switch ($event->type) {
            case 'command':
                $this->line("Executing: php oxygen " . $event->action);
                // In production, execute this in background or via Kernel
                // For demo purposes, we'll just simulate or run simple exec
                // passthru("php oxygen " . $event->action); 
                break;

            case 'closure':
                call_user_func($event->action);
                break;

            case 'exec':
                exec($event->action);
                break;
        }
    }
}
