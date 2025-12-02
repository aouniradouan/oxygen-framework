<?php

namespace Oxygen\Core\Events;

/**
 * Event Dispatcher
 * 
 * Handles event registration and dispatching.
 * 
 * @package    Oxygen\Core\Events
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class Dispatcher
{
    /**
     * Registered event listeners
     * 
     * @var array
     */
    protected $listeners = [];

    /**
     * Wildcard listeners
     * 
     * @var array
     */
    protected $wildcards = [];

    /**
     * Register an event listener
     * 
     * @param string|array $events
     * @param callable|string $listener
     * @return void
     */
    public function listen($events, $listener)
    {
        foreach ((array) $events as $event) {
            if (strpos($event, '*') !== false) {
                $this->wildcards[$event][] = $listener;
            } else {
                $this->listeners[$event][] = $listener;
            }
        }
    }

    /**
     * Dispatch an event
     * 
     * @param string|Event $event
     * @param mixed $payload
     * @param bool $halt
     * @return mixed
     */
    public function dispatch($event, $payload = [], $halt = false)
    {
        // If event is a string, create a generic event
        if (is_string($event)) {
            $event = new GenericEvent($event, $payload);
        }

        $eventName = $event instanceof Event ? $event->getName() : $event;

        // Get listeners for this event
        $listeners = $this->getListeners($eventName);

        // Also check wildcards
        foreach ($this->wildcards as $pattern => $wildcardListeners) {
            if ($this->matchesWildcard($eventName, $pattern)) {
                $listeners = array_merge($listeners, $wildcardListeners);
            }
        }

        // Execute listeners
        foreach ($listeners as $listener) {
            $response = $this->callListener($listener, $event, $payload);

            if ($halt && !is_null($response)) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Get listeners for an event
     * 
     * @param string $eventName
     * @return array
     */
    protected function getListeners($eventName)
    {
        return $this->listeners[$eventName] ?? [];
    }

    /**
     * Check if event name matches wildcard pattern
     * 
     * @param string $eventName
     * @param string $pattern
     * @return bool
     */
    protected function matchesWildcard($eventName, $pattern)
    {
        $pattern = str_replace('\*', '.*', preg_quote($pattern, '/'));
        return preg_match('/^' . $pattern . '$/i', $eventName);
    }

    /**
     * Call a listener
     * 
     * @param callable|string $listener
     * @param Event $event
     * @param mixed $payload
     * @return mixed
     */
    protected function callListener($listener, $event, $payload)
    {
        if (is_string($listener)) {
            // Assume it's a class name
            $listener = new $listener();
        }

        if (is_callable($listener)) {
            return call_user_func($listener, $event, $payload);
        }

        return null;
    }

    /**
     * Remove a listener
     * 
     * @param string $event
     * @param callable|null $listener
     * @return void
     */
    public function forget($event, $listener = null)
    {
        if (is_null($listener)) {
            unset($this->listeners[$event]);
        } else {
            $key = array_search($listener, $this->listeners[$event] ?? []);
            if ($key !== false) {
                unset($this->listeners[$event][$key]);
            }
        }
    }

    /**
     * Clear all listeners
     * 
     * @return void
     */
    public function flush()
    {
        $this->listeners = [];
        $this->wildcards = [];
    }
}

