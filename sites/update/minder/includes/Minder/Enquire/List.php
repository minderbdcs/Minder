<?php

/**
 * Class Minder_Enquire_List
 *
 * @property string[] dataType
 * @property string[] primaryScreen
 * @property string[] description
 * @property string screens
 */
class Minder_Enquire_List extends Minder_Collection {
    /**
     * @return string[]
     */
    public function getScreens() {
       return explode('|', implode('|', $this->screens));
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_Enquire_Abstract();
    }
}