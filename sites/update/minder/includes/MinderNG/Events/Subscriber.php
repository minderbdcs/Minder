<?php

namespace MinderNG\Events;

final class Subscriber implements SubscriberInterface {

    /**
     * @var PublisherAggregateInterface[]
     */
    private $_subscribedTo = array();

    /**
     * @var
     */
    private $_realSubscriber;

    function __construct($realSubscriber = null)
    {
        $this->_realSubscriber = is_null($realSubscriber) ? $this : $realSubscriber;
    }

    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param string $catchableName
     * @param callable|string $callback
     * @return void
     */
    public function subscribeTo(PublisherAggregateInterface $publisherAggregate, $catchableName, $callback)
    {
        $publisher = $publisherAggregate->getPublisher();
        $this->_subscribedTo[$publisher->getPublisherId()] = $publisherAggregate;
        $publisher->on($catchableName, $this->_wrapCallback($callback));
    }

    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param string|null $catchableName
     * @param callable|null $callback
     * @return void
     */
    public function stopSubscriptionTo(PublisherAggregateInterface $publisherAggregate = null, $catchableName = null, $callback = null)
    {
        $stopSubscriptionTo = is_null($publisherAggregate) ? $this->_subscribedTo : array($publisherAggregate);
        $callback = $this->_wrapCallback($callback);
        $remove = is_null($catchableName) && is_null($callback);

        foreach ($stopSubscriptionTo as $listeningPublisher) {
            $listeningPublisher = $listeningPublisher->getPublisher();
            $listeningPublisher->off($catchableName, $callback);

            if ($remove || !$listeningPublisher->hasSubscribers()) {
                if (isset($this->_subscribedTo[$listeningPublisher->getPublisherId()])) {
                    unset($this->_subscribedTo[$listeningPublisher->getPublisherId()]);
                }
            }
        }
    }

    /**
     * @param PublisherAggregateInterface $publisherAggregate
     * @param callable|string $callback
     * @return void
     */
    public function subscribeToAllEvents(PublisherAggregateInterface $publisherAggregate, $callback)
    {
        $this->subscribeTo($publisherAggregate, EventInterface::ALL_EVENT, $callback);
    }

    /**
     * @param callable|string $callback
     * @return array
     */
    protected function _wrapCallback($callback = null)
    {
        if (is_null($callback)) {
            return null;
        }elseif (is_string($callback)) {
            return array($this->_realSubscriber, $callback);
        } else {
            if (!is_array($callback)) {
                user_error('Serialization will not work with Closure callable. Use array(<instance>, "<method name>") instead.');
            }
            return $callback;
        }
    }
}