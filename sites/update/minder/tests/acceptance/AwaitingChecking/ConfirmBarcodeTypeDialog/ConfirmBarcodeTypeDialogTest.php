<?php

class ConfirmBarcodeTypeDialogTest extends MinderAcceptanceTestCase {

    static $_label = '/TTEST9999';

    public function testDialogShouldNotAppearIfDataTypeIsNotRequired() {
        $this->setupConfigNew('loan', 'pinpoint');

        $this->_initialSetup();

        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->_scanLabel(']C0TEST99999999');
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);

        $this->_scanLabel(static::$_label);

        $this->assertFalse($this->byId('confirm_data_identifier_dialog')->displayed(), 'Confirm Data Type dialog should not be visible.');

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
        $this->delete()->execute($this->getConnection(), $this->_cleanUpSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->dataSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
    }

    private function _cleanUpSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/clean_up.yml');
    }

    protected function dataSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/data_seed.yml');
    }
}