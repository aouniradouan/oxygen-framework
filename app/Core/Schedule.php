<?php

namespace Oxygen\Core;

use Closure;

/**
 * Schedule - Task Scheduler
 * 
 * Manages and defines scheduled tasks.
 * 
 * @package    Oxygen\Core
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    1.0.0
 */
class Schedule
{
    protected $events = [];

    /**
     * Add a command to the schedule
     * 
     * @param string $command Command signature
     * @return Event
     */
    public function command($command)
    {
        $event = new Event('command', $command);
        $this->events[] = $event;
        return $event;
    }

    /**
     * Add a closure to the schedule
     * 
     * @param Closure $callback Callback function
     * @return Event
     */
    public function call(Closure $callback)
    {
        $event = new Event('closure', $callback);
        $this->events[] = $event;
        return $event;
    }

    /**
     * Add a shell command to the schedule
     * 
     * @param string $command Shell command
     * @return Event
     */
    public function exec($command)
    {
        $event = new Event('exec', $command);
        $this->events[] = $event;
        return $event;
    }

    /**
     * Get all scheduled events
     * 
     * @return array
     */
    public function getEvents()
    {
        return $this->events;
    }
}

class Event
{
    public $type;
    public $action;
    public $expression = '* * * * *';
    public $description;

    public function __construct($type, $action)
    {
        $this->type = $type;
        $this->action = $action;
    }

    public function cron($expression)
    {
        $this->expression = $expression;
        return $this;
    }

    public function everyMinute()
    {
        return $this->cron('* * * * *');
    }

    public function hourly()
    {
        return $this->cron('0 * * * *');
    }

    public function daily()
    {
        return $this->cron('0 0 * * *');
    }

    public function dailyAt($time)
    {
        $parts = explode(':', $time);
        return $this->cron((int) $parts[1] . ' ' . (int) $parts[0] . ' * * *');
    }

    public function weekly()
    {
        return $this->cron('0 0 * * 0');
    }

    public function monthly()
    {
        return $this->cron('0 0 1 * *');
    }

    public function description($description)
    {
        $this->description = $description;
        return $this;
    }

    public function isDue()
    {
        return CronExpression::isDue($this->expression);
    }
}
