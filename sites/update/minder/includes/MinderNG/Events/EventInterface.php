<?php

namespace MinderNG\Events;

interface EventInterface extends CatchableInterface {
    const ALL_EVENT = 'ALL_EVENT';

    /**
     * @return boolean
     */
    public function defaultPrevented();

    /**
     * @return void
     */
    public function preventDefault();
}