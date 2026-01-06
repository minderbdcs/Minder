<?php

namespace MinderNG\Events;

interface CatchableInterface {

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getArgs();
}