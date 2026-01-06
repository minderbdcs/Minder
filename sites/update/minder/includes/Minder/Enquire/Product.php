<?php

/**
 * Class Minder_Enquire_Product
 * @property string receiptScreen
 * @property string despatchScreen
 */
class Minder_Enquire_Product extends Minder_Enquire_Abstract {
    const RECEIPT_SCREEN = 'receiptScreen';
    const DESPATCH_SCREEN = 'despatchScreen';

    public function offsetSet($index, $newval)
    {
        switch ($index) {
            case (static::SCREENS):
                list($primaryScreen, $receiptScreen, $despatchScreen) = explode('|', $newval);
                parent::offsetSet(static::PRIMARY_SCREEN, $primaryScreen);
                parent::offsetSet(static::RECEIPT_SCREEN, $receiptScreen);
                parent::offsetSet(static::DESPATCH_SCREEN, $despatchScreen);
                parent::offsetSet($index, $newval);
                break;

            default:
                parent::offsetSet($index, $newval);
        }
    }


}