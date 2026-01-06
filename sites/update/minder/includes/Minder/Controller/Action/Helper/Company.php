<?php

/**
 * Class Minder_Controller_Action_Helper_Company
 *
 * @method Minder2_Model_Company find(string $companyId)
 */
class Minder_Controller_Action_Helper_Company extends Minder_Controller_Action_Helper_ServiceProxy {
    protected function _getServiceClassName()
    {
        return 'Minder2_Model_Mapper_Company';
    }
}