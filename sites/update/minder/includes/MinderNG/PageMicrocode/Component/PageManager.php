<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysMenu;

class PageManager {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Component\\PageManager';

    private $_sysMenuProvider;

    function __construct(SysMenu $sysMenuProvider)
    {
        $this->_sysMenuProvider = $sysMenuProvider;
    }

    public function getCurrentUserAndDevicePages(\Minder2_Environment $environment) {
        $sysMenu = $this->_sysMenuProvider->getSysMenuCollection(
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );
        $result = new PageCollection();
        $result->init($sysMenu, new AddOptions(false, true));
        return $result;
    }

}