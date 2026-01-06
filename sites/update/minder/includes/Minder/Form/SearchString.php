<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 24.04.12
 * Time: 17:29
 * To change this template use File | Settings | File Templates.
 */
class Minder_Form_SearchString extends Zend_Form
{
    public function __construct()
    {
        $options = new Zend_Config_Ini(APPLICATION_CONFIG_DIR . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'search-string.ini', 'form');
        parent::__construct($options);
    }
}
