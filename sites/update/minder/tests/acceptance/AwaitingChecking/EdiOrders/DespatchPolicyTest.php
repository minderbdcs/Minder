<?php

class AwaitingChecking_EdiOrders_DespatchPolicyTest extends MinderAcceptanceTestCase {

    const PICK_BLOCK = 'BLOCK998';
    const LABEL_NO = 'D999998';

    /**
     * @param $licensee
     * @dataProvider dataSource
     */
    public function testIfPartialDespatchAllowedShouldPerformDespatchForPartiallyPickedOrder($licensee) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->_initialSetupAllowed();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');

        $this->_scanLabel(static::PICK_BLOCK);

        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);
        $this->_scanLabel(']A0ACCEPT');

        if ($this->byId('confirm_carriers_dialog')->displayed()) {
            $this->_scanLabel(']A0CONTINUE');
        }

        $this->waitForElement('.messages', 10000);
        $actualItem = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actualItem->addTable('PICK_ITEM', "SELECT PICK_LABEL_NO, PICK_LINE_STATUS from PICK_ITEM WHERE PICK_LABEL_NO = '" . static::LABEL_NO . "'");
        $expectedItem = new ArrayDataSet(array('PICK_ITEM'=>array(array('PICK_LABEL_NO'=> static::LABEL_NO, 'PICK_LINE_STATUS' => 'DC'))));

        $this->assertDataSetsEqual($expectedItem, $actualItem);

    }

    /**
     * @param $licensee
     * @dataProvider dataSource
     */
    public function testIfPartialDespatchNotAllowedShouldRaiseAnErrorForPartiallyPickedOrder($licensee) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->_initialSetupNotAllowed();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');

        $this->_scanLabel(static::PICK_BLOCK);

        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);
        $this->_scanLabel(']A0ACCEPT');

        if ($this->byId('confirm_carriers_dialog')->displayed()) {
            $this->_scanLabel(']A0CONTINUE');
        }

        $this->waitForElement('.errors', 10000);
        $actualItem = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actualItem->addTable('PICK_ITEM', "SELECT PICK_LABEL_NO, PICK_LINE_STATUS from PICK_ITEM WHERE PICK_LABEL_NO = '" . static::LABEL_NO . "'");
        $expectedItem = new ArrayDataSet(array('PICK_ITEM'=>array(array('PICK_LABEL_NO'=> static::LABEL_NO, 'PICK_LINE_STATUS' => 'PL'))));

        $this->assertDataSetsEqual($expectedItem, $actualItem);

    }

    function dataSource() {
        return array(
            array('loan'),
            array('museum'),
            array('product'),
        );
    }

    protected function setUp()
    {
        $this->setBrowserUrl('http://minder.dev/');
        $this->setSeleniumServerRequestsTimeout(10000);
    }

    protected function tearDown()
    {
        $this->getConnection()->getConnection()->commit();
        $this->logout();
    }

    protected function _initialSetupAllowed() {
        $triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');
        $this->disableTriggers($triggers);
//        $this->truncate()->execute($this->getConnection(), $this->deviceSeed());
//        $this->cleanInsert()->execute($this->getConnection(), $this->deviceSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_dataIdentifiersSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_ediOptionsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_orderCheckPromptsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysLabelSsccSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysScreenSsccPackDimsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysScreenSsccRePackSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_allowedOrderSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_orderDataSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
    }

    protected function _initialSetupNotAllowed() {
        $triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');
        $this->disableTriggers($triggers);
//        $this->truncate()->execute($this->getConnection(), $this->deviceSeed());
//        $this->cleanInsert()->execute($this->getConnection(), $this->deviceSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_dataIdentifiersSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_ediOptionsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_orderCheckPromptsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysLabelSsccSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysScreenSsccPackDimsSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_sysScreenSsccRePackSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_notAllowedOrderSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->_orderDataSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
    }

    protected function _dataIdentifiersSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/dataIdentifiers.yml');

    }

    protected function _ediOptionsSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/ediOptions.yml');

    }

    protected function _orderCheckPromptsSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/OPTIONS-ORDERCHECK-PROMPTS.yml');

    }

    protected function _sysLabelSsccSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/SYS_LABEL_SSCC.yml');

    }

    protected function _sysScreenSsccPackDimsSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/sys_screen-SSCC_PACK_DIMS.yml');

    }

    protected function _sysScreenSsccRePackSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/CommonData/sys_screen-SSCC_REPACK_DIMS.yml');

    }

    protected function _allowedOrderSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/allowedOrder.yml');

    }

    protected function _notAllowedOrderSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/notAllowedOrder.yml');

    }

    protected function _orderDataSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/despatch_policy_seed.yml');

    }
}