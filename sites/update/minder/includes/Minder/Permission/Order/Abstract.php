<?php
/**
 * Created by PhpStorm.
 * User: sergeyg
 * Date: 04.11.13
 * Time: 10:25
 */
abstract class Minder_Permission_Order_Abstract
{
    abstract public function check($orders);

    public function allowedFor($order)
    {
        $checkResult = $this->check(array($order));
        return !in_array($order, $checkResult);
    }

    protected function _getMinder()
    {
        return Minder::getInstance();
    }
}