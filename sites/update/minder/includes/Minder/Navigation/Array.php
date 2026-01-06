<?php

class Minder_Navigation_Array {

    static public function getShortcuts($section) {
        if (false === Zend_Loader::loadFile(APPLICATION_CONFIG_DIR . '/nav/' . $section . '.array.php', null, true)) {
            return array();
        }

        global $shortcuts;
        return $shortcuts;
    }

    static public function getTooltips($section) {
        if (false === Zend_Loader::loadFile(APPLICATION_CONFIG_DIR . '/nav/' . $section . '.array.php', null, true)) {
            return array();
        }

        global $tooltip;
        return $tooltip;
    }
}