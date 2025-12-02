<?php

namespace Oxygen\Core\Events;

/**
 * Generic Event
 * 
 * A simple event implementation for string-based events.
 * 
 * @package    Oxygen\Core\Events
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class GenericEvent extends Event
{
    /**
     * Event name
     * 
     * @var string
     */
    protected $name;

    /**
     * Event payload
     * 
     * @var mixed
     */
    protected $payload;

    /**
     * Create a new generic event
     * 
     * @param string $name
     * @param mixed $payload
     */
    public function __construct($name, $payload = [])
    {
        $this->name = $name;
        $this->payload = $payload;
    }

    /**
     * Get the event name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the event payload
     * 
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

