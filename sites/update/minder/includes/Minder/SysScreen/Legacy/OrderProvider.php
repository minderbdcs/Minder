<?php

class Minder_SysScreen_Legacy_OrderProvider {
    public function fetchOrders($screenName) {
        $sql = "SELECT SSO_ORDER,
                       SSO_SEQUENCE,
                       COMPANY_ID,
                       WH_ID,
                       SSO_USER_TYPE,
                       SSO_DEVICE_TYPE
                FROM SYS_SCREEN_ORDER
                WHERE SS_NAME = ?
                AND   SSO_ORDER_STATUS = ?
                ORDER BY SSO_SEQUENCE ";

        $values = array( $screenName, 'OK' );

        if (false === ($values = $this->_getMinder()->prepareArgs($sql, $values))) {
            throw new Minder_Exception(ibase_errcode() . ': ' . ibase_errmsg() . '. Query text: ' . $sql);
        }

        return $this->_getMinder()->findAllAssocMod($sql, $values);
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}