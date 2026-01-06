<?php

class AwaitingChecking_AcceptByAwbConsignmentNo_ScanConnoteNoAndAccept extends MinderAcceptanceTestCase {
    /**
     * @param $licensee
     * @param $orderLabel
     * @param $connoteLabel
     * @dataProvider dataProvider
     */
    public function testWhenThirdPartyConnoteLabelIsScannedConsignmentShouldBeAcceptedWithScannedConnoteNoAndCarrier($licensee, $orderLabel, $connoteLabel) {
        $this->setupConfig('interbase.local:pinpoint', $licensee);
        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->_initialSetup();

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->_scanLabel($orderLabel);
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);

        $this->_scanLabel($connoteLabel);

        if ($this->byId('confirm_carriers_dialog')->displayed()) {
            $this->_scanLabel(']A0CONTINUE');
        }

        $this->waitForElement('.messages', 10000);

    }

    public function dataProvider() {
        return array(
            array('loan', ']C0S72162698L001', ']C0AP000000000000000000'),
            array('product', ']C0S72162698L001', ']C0AP000000000000000000'),
            array('museum', ']C0S72162698L001', ']C0AP000000000000000000'),
        );
    }

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
        $this->cleanInsert()->execute($this->getConnection(), $this->getDataSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
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

    protected function getDataSeed() {
        return $this->createArrayDataset(include __DIR__ . '/dataSeed.php');
    }
}