<?php

namespace MinderNG\Events;

/**
 * Interface SubscriberInterface
 * @package MinderNG\Events
 *
 * @todo: SubscriberInterface methods should depend on PublisherInterface not on PublisherAggregateInterface
 */
interface SubscriberInterface {
    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param string $catchableName
     * @param callable|string $callback
     * @return void
     */
    public function subscribeTo(PublisherAggregateInterface $publisherAggregate, $catchableName, $callback);

    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param callable|string $callback
     * @return void
     */
    public function subscribeToAllEvents(PublisherAggregateInterface $publisherAggregate, $callback);

    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param string|null $catchableName
     * @param callable|null $callback
     * @return void
     */
    public function stopSubscriptionTo(PublisherAggregateInterface $publisherAggregate = null, $catchableName = null, $callback = null);
}