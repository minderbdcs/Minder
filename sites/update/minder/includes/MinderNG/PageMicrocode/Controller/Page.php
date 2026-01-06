<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Collection\ModelInterface;
use MinderNG\Events;
use MinderNG\PageMicrocode\Command\SwitchToTab;
use MinderNG\PageMicrocode\Component\Components;
use MinderNG\PageMicrocode\Event\PageLoad;

class Page implements Events\SubscriberAggregateInterface {
    private $_subscriber;

    /**
     * @var Components
     */
    private $_components;

    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    public function init(Components $components, Events\PublisherAggregateInterface $messageBus) {
        //todo

        $this->_components = $components;
        $this->_messageBus = $messageBus;

        $this->_initListeners($components, $messageBus);
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

    public function onPageLoad(Events\EventInterface $event, \Minder2_Environment $environment) {
        $this->_parseAndSet($this->_components->user, $environment->getCurrentUser());
        $this->_parseAndSet($this->_components->device, $environment->getCurrentDevice());
        $this->_parseAndSet($this->_components->warehouse, $environment->getCurrentWarehouse());
        $this->_parseAndSet($this->_components->company, $environment->getCurrentCompany());
        $this->_parseAndSet($this->_components->companyLimit, $environment->getCompanyLimit());
        $this->_parseAndSet($this->_components->warehouseLimit, $environment->getWarehouseLimit());
        $this->_parseAndSet($this->_components->selectedPrinter, $environment->getCurrentPrinter());
    }

    private function _parseAndSet(ModelInterface $model, \Minder2_Model_Interface $legacyModel) {
        $model->set($legacyModel->getFields(), false, false, true);
    }

    private function _initListeners(Components $components, Events\PublisherAggregateInterface $messageBus) {
        $this->getSubscriber()->subscribeTo($messageBus, PageLoad::EVENT_NAME, 'onPageLoad');
    }
}