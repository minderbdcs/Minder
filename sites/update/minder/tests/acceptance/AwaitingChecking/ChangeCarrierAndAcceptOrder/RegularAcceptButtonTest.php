<?php

class AwaitingChecking_ChangeCarrierAndAcceptOrder_RegularAcceptButtonTest extends MinderAcceptanceTestCase {
    const PICK_ORDER = 'S72162698L001';

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
     * @param $carrierId
     * @param $changeCarrier
     * @param $firstButton
     * @param $shouldSeeDialog
     * @param $secondButton
     * @param $shouldSeeDialog2
     * @param $checkOrder
     * @internal param $button
     * @dataProvider regularButtonTestData
     */
    function testConfirmCarrierDialog($licensee, $carrierId, $changeCarrier, $firstButton, $shouldSeeDialog, $secondButton, $shouldSeeDialog2, $checkOrder) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->_initialSetup();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->_scanLabel(']C0' . static::PICK_ORDER);
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);

        if ($changeCarrier) {
            $this->executeScript('$("#ship_via").val("' . $carrierId . '").change();');
        }

        $this->executeScript('$("#barcode").focus().val("' . $firstButton . '").blur();');
        $this->waitForElement('#confirm_carriers_dialog', 10000);
        $this->assertEquals($shouldSeeDialog, $this->byId('confirm_carriers_dialog')->displayed());

        $this->executeScript('$("#barcode").focus().val("' . $secondButton . '").blur();');
        $this->waitForElement('#confirm_carriers_dialog', 10000);
        $this->assertEquals($shouldSeeDialog2, $this->byId('confirm_carriers_dialog')->displayed());

        if ($checkOrder) {
            $this->waitForElement('.messages', 10000);
            $expectedOrder = new ArrayDataSet(array('PICK_ORDER'=>array(array('PICK_ORDER'=> static::PICK_ORDER, 'SHIP_VIA' => $carrierId))));
            $actualOrder = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
            $actualOrder->addTable('PICK_ORDER', "SELECT PICK_ORDER, SHIP_VIA from PICK_ORDER WHERE PICK_ORDER = '" . static::PICK_ORDER . "'");
            $this->assertDataSetsEqual($expectedOrder, $actualOrder);
            $this->getConnection()->getConnection()->commit();
        }
    }

    function regularButtonTestData() {
        return array(
            array('loan', 'UGLY', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('loan', 'UGLY', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('loan', 'UGLY', false, ']A0ACCEPT118', true, ']A0CANCEL', false, false),
            array('loan', 'UGLY', false, ']A0ACCEPT118', true, ']A0ACCEPT118', false, true),
            array('loan', 'UGLY', false, ']A0ACCEPT118', true, ']A0CONTINUE', false, true),
            array('loan', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('loan', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('loan', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CANCEL', false, false),
            array('loan', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0ACCEPT117', false, true),
            array('loan', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CONTINUE', false, true),
            array('loan', 'COURIERP', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('loan', 'COURIERP', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('loan', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CANCEL', false, false),
            array('loan', 'COURIERP', false, ']A0ACCEPT116', true, ']A0ACCEPT116', false, true),
            array('loan', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CONTINUE', false, true),

            array('product', 'UGLY', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('product', 'UGLY', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('product', 'UGLY', false, ']A0ACCEPT118', true, ']A0CANCEL', false, false),
            array('product', 'UGLY', false, ']A0ACCEPT118', true, ']A0ACCEPT118', false, true),
            array('product', 'UGLY', false, ']A0ACCEPT118', true, ']A0CONTINUE', false, true),
            array('product', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('product', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('product', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CANCEL', false, false),
            array('product', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0ACCEPT117', false, true),
            array('product', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CONTINUE', false, true),
            array('product', 'COURIERP', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('product', 'COURIERP', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('product', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CANCEL', false, false),
            array('product', 'COURIERP', false, ']A0ACCEPT116', true, ']A0ACCEPT116', false, true),
            array('product', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CONTINUE', false, true),

            array('museum', 'UGLY', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('museum', 'UGLY', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('museum', 'UGLY', false, ']A0ACCEPT118', true, ']A0CANCEL', false, false),
            array('museum', 'UGLY', false, ']A0ACCEPT118', true, ']A0ACCEPT118', false, true),
            array('museum', 'UGLY', false, ']A0ACCEPT118', true, ']A0CONTINUE', false, true),
            array('museum', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('museum', 'CPLEASE', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('museum', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CANCEL', false, false),
            array('museum', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0ACCEPT117', false, true),
            array('museum', 'CPLEASE', false, ']A0ACCEPT117', true, ']A0CONTINUE', false, true),
            array('museum', 'COURIERP', true, ']A0ACCEPT', true, ']A0CANCEL', false, false),
            array('museum', 'COURIERP', true, ']A0ACCEPT', true, ']A0CONTINUE', false, true),
            array('museum', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CANCEL', false, false),
            array('museum', 'COURIERP', false, ']A0ACCEPT116', true, ']A0ACCEPT116', false, true),
            array('museum', 'COURIERP', false, ']A0ACCEPT116', true, ']A0CONTINUE', false, true),
        );
    }

    /**
     * @param $licensee
     * @param $carrierId
     * @param $fastButton
     * @dataProvider fastConnoteAcceptData
     */
    function testFastConnoteAccept($licensee, $carrierId, $fastButton) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->login('Admin', 'aDMAX');
        $this->_initialSetup();

        $orderSeed = new ArrayDataSet(array('PICK_ORDER' => array(
            array('PICK_ORDER' => static::PICK_ORDER, 'SHIP_VIA' => $carrierId)
        )));
        $this->update()->execute($this->getConnection(), $orderSeed);
        $this->getConnection()->getConnection()->commit();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->executeScript('$("#barcode").focus().val("]C0' . static::PICK_ORDER . '").blur();');
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);

        $this->executeScript('$("#barcode").focus().val("' . $fastButton . '").blur();');
        $this->waitForElement('#confirm_carriers_dialog', 10000);
        $this->assertFalse($this->byId('confirm_carriers_dialog')->displayed());

        $this->waitForElement('.messages', 10000);
        $expectedOrder = new ArrayDataSet(array('PICK_ORDER'=>array(array('PICK_ORDER'=> static::PICK_ORDER, 'SHIP_VIA' => $carrierId))));
        $actualOrder = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $actualOrder->addTable('PICK_ORDER', "SELECT PICK_ORDER, SHIP_VIA from PICK_ORDER WHERE PICK_ORDER = '" . static::PICK_ORDER . "'");
        $this->assertDataSetsEqual($expectedOrder, $actualOrder);
        $this->getConnection()->getConnection()->commit();

        $this->_initialSetup();

    }

    function fastConnoteAcceptData() {
        return array(
            array('loan', 'CPLEASE', ']A0ACCEPT117'),
            array('product', 'CPLEASE', ']A0ACCEPT117'),
            array('museum', 'CPLEASE', ']A0ACCEPT117'),
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
            ),
            array(
                'DATA_BRAND' => 'DEFAULT',
                'DATA_MODEL' => 'DEFAULT',
                'DATA_ID' => 'SALESORDER',
                'MAX_LENGTH' => 13,
                'DATA_TYPE' => 1,
                'FIXED_LENGTH' => 'F',
                'SYMBOLOGY_PREFIX' => ']C0;',
                'DATA_EXPRESSION' => 'S[0-9]{8}?L[0-9]{3}',
                'GLOBAL_SEARCH' => 'F',
            ),
        )));
    }

    protected function orderSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/order.yml');
    }
}