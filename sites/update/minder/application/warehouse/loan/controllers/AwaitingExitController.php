<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 09.11.11
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
 
class Warehouse_AwaitingExitController extends Minder_Controller_Action{

    const MODEL_NAME = 'PICK_DESPATCH';
    const MODEL_NAMESPACE  = 'WAREHOUSE-PICK_DESPATCH';
    public function indexAction()
    {
        $this->view->pageTitle = 'Awaiting exit';

        $this->view->locationSsName     = $this->view->searchFormSsName = self::MODEL_NAME;
        $this->view->locationNamespace  = self::MODEL_NAMESPACE;

        $screenBuilder = new Minder_SysScreen_Builder();
        /**
         * @var Minder_Controller_Action_Helper_SearchKeeper $searchKeeper
         */
        $searchKeeper  = $this->_helper->searchKeeper;

        list($tmpSearchFields, , , $tmpGISearchFields) = $screenBuilder->buildSysScreenSearchFields(self::MODEL_NAME);

        $searchFields = array_merge($tmpSearchFields, $tmpGISearchFields);
        $searchFields = $searchKeeper->getSearch($searchFields, self::MODEL_NAMESPACE, self::$defaultSelectionAction, self::$defaultSelectionController);
        $this->view->searchFields = $searchFields;
    }
}
