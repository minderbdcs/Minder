<?php

class AwaitingChecking_ChangeCarrierAndAcceptOrder_ConfirmDespatchDialogTest extends MinderAcceptanceTestCase {
    protected function setUp()
    {
        $this->setBrowserUrl('http://minder.dev/');
        $this->setSeleniumServerRequestsTimeout(10000);
    }

    protected function tearDown()
    {
        $this->logout();
    }

    protected function _initialSetup() {
        $triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');
        $this->disableTriggers($triggers);
        $this->truncate()->execute($this->getConnection(), $this->deviceSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->deviceSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->defaultCarrierSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->orderSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->paramSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
    }

    /**
     * @param $licensee
     * @param $firstOrder
     * @param $productLabel
     * @param $secondOrder
     * @param $shouldSeeDialog
     * @param $buttonLabel
     * @param $orderShouldBeDespatched
     * @param $pickLabelNo
     * @dataProvider dataSource
     */
    function testConfirmDespatchDialog($licensee, $firstOrder, $productLabel, $secondOrder, $shouldSeeDialog, $buttonLabel, $orderShouldBeDespatched, $pickLabelNo) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->_initialSetup();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->_scanLabel($firstOrder);
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);
        $this->_scanLabel($productLabel);
        $this->_scanLabel($secondOrder);

        if ($shouldSeeDialog) {
            $this->assertEquals($shouldSeeDialog, $this->byId('confirm_despatch_dialog')->displayed());
            $this->_scanLabel($buttonLabel);

            if ($this->byId('confirm_carriers_dialog')->displayed()) {
                $this->_scanLabel(']A0CONTINUE');
            }

        }

        $actualItem = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actualItem->addTable('PICK_ITEM', "SELECT PICK_LABEL_NO, PICK_LINE_STATUS from PICK_ITEM WHERE PICK_LABEL_NO = '" . $pickLabelNo . "'");

        if ($orderShouldBeDespatched) {
            $this->waitForElement('.messages', 10000);
            $expectedItem = new ArrayDataSet(array('PICK_ITEM'=>array(array('PICK_LABEL_NO'=> $pickLabelNo, 'PICK_LINE_STATUS' => 'DC'))));
        } else {
            $this->waitForElement('#orders_results', 10000);
            $expectedItem = new ArrayDataSet(array('PICK_ITEM'=>array(array('PICK_LABEL_NO'=> $pickLabelNo, 'PICK_LINE_STATUS' => 'PL'))));
        }

        $this->assertDataSetsEqual($expectedItem, $actualItem);
        $this->getConnection()->getConnection()->commit();
    }

    function dataSource() {
        return array(
            array('loan', ']C0S72162698L001', "/T718408", ']C0S72162698L002', true, ']A0CANCEL', false, 'D000632'),
            array('loan', ']C0S72162698L001', "/T718408", ']C0S72162698L002', true, ']A0ACCEPT', true, 'D000632'),
        );
    }

    protected function deviceSeed() {
        return new ArrayDataSet(array('sys_equip' => array(
            array(
                'DEVICE_ID' => 'RE',
                'DEVICE_TYPE' => 'PC',
                'IP_ADDRESS' => 'DHCP',
                'WH_ID' => 'RZ',
                'LOCN_ID' => 'TUNNEL01',
            ),
            array(
                'DEVICE_ID' => 'PA',
                'DEVICE_TYPE' => 'PR',
                'IP_ADDRESS' => '192.168.61.33',
                'WH_ID' => 'RZ',
                'LOCN_ID' => null,
            ),
        )));
    }

    protected function defaultCarrierSeed() {
        return new ArrayDataSet(array('options' => array(
            array(
                'GROUP_CODE' => 'DS_CARRIER',
                'CODE' => 'RE',
                'DESCRIPTION' => 'CPLEASE|POST',
            ),
        )));
    }

    protected function paramSeed() {
        return $this->createArrayDataset(array('PARAM' => array(
            array(
                'DATA_BRAND' => 'DEFAULT',
                'DATA_MODEL' => 'DEFAULT',
                'DATA_ID' => 'SCREEN_BUTTON',
                'MAX_LENGTH' => 40,
                'DATA_TYPE' => 1,
                'FIXED_LENGTH' => 'F',
                'SYMBOLOGY_PREFIX' => ']A0;',
                'DATA_EXPRESSION' => '[A-Z]*',
                'GLOBAL_SEARCH' => 'F',
                'DATA_TYPE_ID' => '',
            ),
            array(
                'DATA_BRAND' => 'DEFAULT',
                'DATA_MODEL' => 'DEFAULT',
                'DATA_ID' => 'PROD_INTERNAL',
                'MAX_LENGTH' => 30,
                'DATA_TYPE' => 1,
                'FIXED_LENGTH' => 'F',
                'SYMBOLOGY_PREFIX' => '/T;',
                'DATA_EXPRESSION' => '[A-Z0-9]*.*',
                'GLOBAL_SEARCH' => 'F',
                'DATA_TYPE_ID' => 'PROD_ID',
            ),
            array(
                'DATA_BRAND' => 'DEFAULT',
                'DATA_MODEL' => 'DEFAULT',
                'DATA_ID' => 'SALESORDER',
                'MAX_LENGTH' => 13,
                'DATA_TYPE' => 1,
                'FIXED_LENGTH' => 'T',
                'SYMBOLOGY_PREFIX' => ']C0;',
                'DATA_EXPRESSION' => 'S[0-9A-Z]*',
                'GLOBAL_SEARCH' => 'T',
                'DATA_TYPE_ID' => 'PICK_ORDER',
            ),
        )));
    }

    protected function orderSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/order.yml');
    }
}