<?php

namespace MinderNG\Events;

interface PublisherInterface {

    /**
     * @param string $catchableName
     * @param callable $callback
     * @return void
     */
    public function on($catchableName, $callback);

    /**
     * @param string $catchableName
     * @param callable $callback
     * @return
     */
    public function off($catchableName = null, $callback = null);

    /**
     * @param EventInterface $event
     * @param ... $args
     * @return EventInterface
     */
    public function trigger(EventInterface $event);

    /**
     * @param CommandInterface $command
     * @param ... $args
     * @return CommandInterface
     */
    public function send(CommandInterface $command);

    /**
     * @return string|integer
     */
    public function getPublisherId();

    /**
     * @return boolean
     */
    public function hasSubscribers();
}