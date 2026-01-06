<?php

namespace MinderNG\Events;

interface PublisherAggregateInterface {
    /**
     * @return PublisherInterface
     */
    public function getPublisher();
}