<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Collection\Event\ModelFieldChange;
use MinderNG\Collection\ModelInterface;
use MinderNG\Events;
use MinderNG\PageMicrocode\Component\Components;

class PageSettings implements Events\SubscriberAggregateInterface {

    /**
     * @var Components
     */
    private $_components;

    public function init(Components $components, Events\PublisherAggregateInterface $messageBus) {
        //todo

        $this->_components = $components;

        $this->_initListeners($components, $messageBus);
    }

    public function onPageIdChanged(ModelInterface $model, $newValue) {
        $this->_components->pageSettings->pageId = $newValue;
    }

    public function onControlValuesCompanyIdChanged() {
        $this->_components->pageSettings->companyId = $this->_getEffectiveCompanyId();
    }

    public function onCompanyLimitCompanyIdChanged() {
        $this->_components->pageSettings->companyId = $this->_getEffectiveCompanyId();
    }

    public function onUserCompanyIdChanged() {
        $this->_components->pageSettings->companyId = $this->_getEffectiveCompanyId();
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

    private function _initListeners(Components $components, Events\PublisherAggregateInterface $messageBus) {
        $this->getSubscriber()->subscribeTo($components->page, ModelFieldChange::eventName('id'), 'onPageIdChanged');
        $this->getSubscriber()->subscribeTo($components->controlValues, ModelFieldChange::eventName('COMPANY_ID'), 'onControlValuesCompanyIdChanged');
        $this->getSubscriber()->subscribeTo($components->companyLimit, ModelFieldChange::eventName('COMPANY_ID'), 'onCompanyLimitCompanyIdChanged');
        $this->getSubscriber()->subscribeTo($components->user, ModelFieldChange::eventName('COMPANY_ID'), 'onUserCompanyIdChanged');
    }

    private function _getEffectiveCompanyId() {
        $companyLimitId = strtolower($this->_components->companyLimit->COMPANY_ID);
        if (($companyLimitId != 'all') && (!empty($companyLimitId))) {
            return $companyLimitId;
        }

        if (!empty($this->_components->user->COMPANY_ID)) {
            return $this->_components->user->COMPANY_ID;
        }

        return $this->_components->controlValues->COMPANY_ID;
    }
}