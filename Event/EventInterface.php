<?php

namespace Obullo\Event;

/**
 * EventInterface Class
 * 
 * @category  Event
 * @package   EventInterface
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://obullo.com/package/event
 */
interface EventInterface
{
    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array $events   events
     * @param mixed        $listener $listener string classname or closure object
     * @param int          $priority priority of events
     * 
     * @return void
     */
    public function listen($events, $listener, $priority = 0);

    /**
     * Determine if a given event has listeners.
     *
     * @param string $event name
     * 
     * @return bool
     */
    public function hasListeners($event);

    /**
     * Register an event subscriber with the dispatcher.
     *
     * @param string $subscriber classname
     * 
     * @return void
     */
    public function subscribe($subscriber);

    /**
     * Fire an event until the first non-null response is returned.
     *
     * @param string $event   name
     * @param array  $payload payload
     * 
     * @return mixed
     */
    public function until($event, $payload = array());

    /**
     * Fire an event and call the listeners.
     *
     * @param string $event   name
     * @param mixed  $payload event payload
     * @param bool   $halt    disable event response
     * 
     * @return array|null
     */
    public function fire($event, $payload = array(), $halt = false);

    /**
     * Get all of the listeners for a given event name.
     *
     * @param string $event name
     * 
     * @return array
     */
    public function getListeners($event);

    /**
     * Remove a set of listeners from the dispatcher.
     *
     * @param string $event name
     * 
     * @return void
     */
    public function forget($event);

}

// END EventInterface class

/* End of file EventInterface.php */
/* Location: .Obullo/Event/EventInterface.php */