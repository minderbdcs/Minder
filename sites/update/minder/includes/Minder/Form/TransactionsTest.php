<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 18.10.11
 * Time: 14:19
 * To change this template use File | Settings | File Templates.
 */
 
class Minder_Form_TransactionsTest extends Zend_Form {
    public function __construct()
    {
        $options = new Zend_Config_Ini(APPLICATION_CONFIG_DIR . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'transactions-test.ini', 'form');
        parent::__construct($options);
    }
}
