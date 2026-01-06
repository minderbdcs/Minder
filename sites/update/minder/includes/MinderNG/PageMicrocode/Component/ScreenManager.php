<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Database\Table\SysScreen;

class ScreenManager {
    private $_screenProvider;

    function __construct(SysScreen $sysScreenProvider)
    {
        $this->_screenProvider = $sysScreenProvider;
    }

    public function getUserAndDeviceScreens(Page $page, \Minder2_Environment $environment) {
        $screens = $this->_screenProvider->getScreenCollection(
            array($page->SM_SUBMENU_ID),
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );

        $result = new ScreenCollection();
        $result->init($screens);

        return $result;
    }
}