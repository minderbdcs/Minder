<?php

namespace MinderNG\Page;

use MinderNG\Collection\Event\ModelAdd;
use MinderNG\Events\Subscriber;
use MinderNG\Events\SubscriberAggregateInterface;
use MinderNG\Events\SubscriberInterface;
use MinderNG\SysScreen;

class ScreenManager implements SubscriberAggregateInterface {
    protected $_page;
    protected $_screens;

    /**
     * @var SubscriberInterface
     */
    private $_subscriber;

    function __construct(Page $page, $screens, SysScreen\ScreenCollection $sysScreens)
    {
        $this->_page = $page;
        $this->_screens = $screens;

        $this->getSubscriber()->subscribeTo($sysScreens, ModelAdd::EVENT_NAME, 'onSysScreenAdd');
    }

    public function onSysScreenAdd(SysScreen\Screen $sysScreen) {

    }

    /**
     * @return SubscriberInterface
     */
    public function getSubscriber()
    {
        if ($this->_subscriber) {
            $this->_subscriber = new Subscriber($this);
        }

        return $this->_subscriber;
    }
}