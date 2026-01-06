<?php

namespace MinderNG\Events;

final class Publisher implements PublisherInterface, PublisherAggregateInterface {
    private static $_nextId = 0;

    private $_publisherId;
    private $_catchable = array();

    /**
     * @param string $catchableName
     * @param callable $callback
     * @return void
     */
    public function on($catchableName, $callback)
    {
        $this->_catchable[$catchableName] = isset($this->_catchable[$catchableName]) ? $this->_catchable[$catchableName] : array();
        $this->_catchable[$catchableName][] = $callback;
    }

    /**
     * @param string $catchableName
     * @param callable $callback
     */
    public function off($catchableName = null, $callback = null)
    {
        if (is_null($catchableName) && is_null($callback)) {
            $this->_catchable = array();
        } else {
            $catchableList = is_null($catchableName) ? array_keys($this->_catchable) : array($catchableName);

            foreach ($catchableList as $eventName) {
                $this->_catchable[$eventName] = isset($this->_catchable[$eventName]) ? $this->_catchable[$eventName] : array();

                foreach($this->_catchable[$eventName] as $key => $registeredCallback) {
                    if ($registeredCallback === $callback) {
                        unset($this->_catchable[$eventName][$key]);
                    }
                }

                if (empty($this->_catchable[$eventName])) {
                    unset($this->_catchable[$eventName]);
                }
            }
        }
    }

    /**
     * @param EventInterface $event
     * @param ... $args
     * @return EventInterface
     */
    public function trigger(EventInterface $event)
    {
        $eventName = $event->getName();
        $args = array_merge(array($event), $event->getArgs());
        if (isset($this->_catchable[$eventName])) { $this->_fireEventCallback($event, $this->_catchable[$eventName], $args); }
        if (isset($this->_catchable[EventInterface::ALL_EVENT])) {
            $this->_fireEventCallback($event, $this->_catchable[EventInterface::ALL_EVENT], $args);
        }

        return $event;
    }

    /**
     * @return string|integer
     */
    public function getPublisherId()
    {
        return $this->_publisherId = (is_null($this->_publisherId) ? self::$_nextId++ : $this->_publisherId);
    }

    /**
     * @return boolean
     */
    public function hasSubscribers()
    {
        return count($this->_catchable) > 0;
    }

    /**
     * @param $event
     * @param $eventCallbackList
     * @param $args
     */
    protected function _fireEventCallback(EventInterface $event, $eventCallbackList, $args)
    {
        foreach ($eventCallbackList as $callback) {
            if (false === call_user_func_array($callback, $args)) {
                $event->preventDefault();
            }
        }
    }

    /**
     * @return PublisherInterface
     */
    public function getPublisher()
    {
        return $this;
    }

    /**
     * @param CommandInterface $command
     * @param ... $args
     * @return CommandInterface
     */
    public function send(CommandInterface $command)
    {
        $args = array_merge(array($command), $command->getArgs());
        if (isset($this->_catchable[$command->getName()])) {
            $this->_executeCommandCallback($this->_catchable[$command->getName()], $args);
        }
        if (isset($this->_catchable[CommandInterface::ANY_COMMAND])) {
            $this->_executeCommandCallback($this->_catchable[CommandInterface::ANY_COMMAND], $args);
        }

        return $command;
    }

    private function _executeCommandCallback($commandCallbackList, $args) {
        foreach ($commandCallbackList as $callback) {
            call_user_func_array($callback, $args);
        }
    }
}