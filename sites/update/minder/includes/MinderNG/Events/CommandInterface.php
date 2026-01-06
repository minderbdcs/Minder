<?php

namespace MinderNG\Events;

interface CommandInterface extends CatchableInterface {
    const ANY_COMMAND = 'ANY_COMMAND';

    public function isExecuted();

    public function setExecuted($executed = true);

    public function getResponse();

    public function setResponse($response);
}