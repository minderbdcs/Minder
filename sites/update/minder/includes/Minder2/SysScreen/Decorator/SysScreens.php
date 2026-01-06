<?php

class Minder2_SysScreen_Decorator_SysScreens extends Minder2_SysScreen_Decorator_Abstract {
    public function render($content)
    {
        /**
         * @var Minder2_Model_Page $page
         */
        $page = $this->_getModel();

        $page->sortScreens();
        foreach ($page->getScreens() as $sysScreen) {
            $content .= $sysScreen;
        }

        return $content;
    }

}