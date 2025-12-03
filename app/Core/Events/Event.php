<?php

namespace Oxygen\Core\Events;

/**
 * Base Event Class
 * 
 * All events should extend this class or implement the interface.
 * 
 * @package    Oxygen\Core\Events
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
abstract class Event
{
    /**
     * Indicates if the event should be broadcast
     * 
     * @var bool
     */
    public $broadcast = false;

    /**
     * Get the event name
     * 
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }
}

