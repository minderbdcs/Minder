<?php

class Minder_Permission_Order_PartialDespatch extends Minder_Permission_Order_Abstract
{
    public function check($orders) {
        if (count($orders) < 1) {
            return array();
        }

        $sql = "
            SELECT DISTINCT
                PICK_ORDER.PICK_ORDER
            FROM
                PICK_ORDER
                LEFT JOIN PICK_ITEM ON PICK_ORDER.PICK_ORDER = PICK_ITEM.PICK_ORDER
            WHERE
                PICK_ORDER.PICK_ORDER IN (" . substr(str_repeat('?, ', count($orders)), 0, -2) . ")
                AND PICK_ORDER.PARTIAL_DESPATCH_ALLOWED = 'F'
                AND PICK_ITEM.PICK_LINE_STATUS IN ('DC', 'DX')
                AND NOT EXISTS (
                    SELECT PICK_LABEL_NO FROM PICK_ITEM AS SUB_PI WHERE SUB_PI.PICK_ORDER = PICK_ITEM.PICK_ORDER AND SUB_PI.PARENT_LABEL_NO = PICK_ITEM.PICK_LABEL_NO
                )
        ";

        $args = array_merge(array('PICK_ORDER', $sql), $orders);

        return call_user_func_array(array($this->_getMinder(), 'fetchColumn'), $args);
    }
}