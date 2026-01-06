<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\PageMicrocode\Command\SwitchToForm;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\ScreenHasNoResultForm;
use MinderNG\PageMicrocode\Event\SearchCompleted;

class Screen implements Events\SubscriberAggregateInterface {
    private $_subscriber;
    /**
     * @var Component\Components
     */
    private $_pageComponents;
    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    public function init(Component\Components $pageComponents, Events\PublisherAggregateInterface $messageBus) {
        $this->_pageComponents = $pageComponents;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, SearchCompleted::EVENT_NAME, 'onSearchCompleted');
    }

    public function onSearchCompleted(SearchCompleted $event, Component\Screen $screen) {
        $foundScreen = $this->_pageComponents->screens->findScreen($screen);
        $searchForm = $this->_pageComponents->forms->getScreenSearchForm($foundScreen);
        $searchResultName = empty($searchForm) ? '' : $searchForm->SSF_SEARCH_RESULT_NAME;

        if (!empty($searchResultName)) {
            $resultForm = $this->_pageComponents->forms->getScreenForm($foundScreen, $searchResultName);
        }

        if (empty($resultForm)) {
            if (!empty($searchResultName)) {
                //todo: add warning
            }

            $resultForm = $this->_pageComponents->forms->getScreenSearchResultForm($foundScreen);
        }

        if (empty($resultForm)) {
            $resultForm = $this->_pageComponents->forms->getScreenDefaultEditForm($foundScreen);
        }

        if (empty($resultForm)) {
            $resultForm = $this->_pageComponents->forms->getScreenAnyEditForm($foundScreen);
        }

        if (empty($resultForm)) {
            throw new ScreenHasNoResultForm($foundScreen);
        }

        $this->_messageBus->getPublisher()->send(new SwitchToForm($resultForm));
    }

    /**
     * @return Events\SubscriberInterface
     */
    public function getSubscriber()
    {
        if (empty($this->_subscriber)) {
            $this->_subscriber = new Events\Subscriber($this);
        }

        return $this->_subscriber;
    }
}