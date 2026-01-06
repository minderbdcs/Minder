<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Command\SwitchToTab;
use MinderNG\PageMicrocode\Event\PageLoad;

class Tab implements Events\SubscriberAggregateInterface {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Controller\\Tab';

    /**
     * @var Events\SubscriberInterface
     */
    private $_subscriber;
    /**
     * @var Component\Components
     */
    private $_components;
    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    public function init(Component\Components $components, Events\PublisherAggregateInterface $messageBus) {
        $this->_components = $components;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, PageLoad::EVENT_NAME, 'onPageLoad');
        $this->getSubscriber()->subscribeTo($messageBus, SwitchToTab::COMMAND_NAME, 'onSwitchToTab');
    }

    public function onPageLoad() {
        foreach ($this->_components->forms->getPageForms() as $form) {
            $activeTab = $this->_components->tabs->getFormActiveTab($form);

            if (empty($activeTab)) {
                $activeTab = $this->_components->tabs->getFormTabList($form);
                $activeTab->rewind();
                $activeTab = $activeTab->current();
            }

            if ($activeTab) {
                $this->_messageBus->getPublisher()->send(new SwitchToTab($form, $activeTab));
            }
        }
    }

    public function onSwitchToTab(SwitchToTab $command, Component\Form $form, Component\Tab $tab) {
        $foundForm = $this->_components->forms->findForm($form);
        $foundTab = $this->_components->tabs->findTab($tab);

        foreach ($this->_components->tabs->getFormTabList($foundForm) as $formTab) {
            $formTab->active = false;
        }

        $foundTab->active = true;
        $command->setResponse(true);
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