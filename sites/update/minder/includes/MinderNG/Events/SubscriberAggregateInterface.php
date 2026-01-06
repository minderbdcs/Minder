<?php

namespace MinderNG\Events;

interface SubscriberAggregateInterface {
    /**
     * @return SubscriberInterface
     */
    public function getSubscriber();
}