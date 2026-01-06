<?php

interface Minder_OtcProcess_State_ItemInterface {
    /**
     * @return boolean
     */
    public function isTool();

    /**
     * @return boolean
     */
    public function isConsumable();

    public function isExisted();

    public function doesExpirationConfirmed();

    public function isOnLoan();

    public function doesTransferConfirmed();

    public function confirmExpiration();

    public function confirmTransfer();
}