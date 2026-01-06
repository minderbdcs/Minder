<?php

class SerialCheckTest extends MinderAcceptanceTestCase {

    static $_productsToCheck = array(
        '/TTEST9999' => 1,
        '/TTEST9998' => 1,
        '/TTEST9997' => 3,
        '/TTEST9996' => 3,
    );

    public function testSerialCheck() {
        $this->setupConfigNew('loan', 'pinpoint');

        $this->_initialSetup();

        $this->logoutAll('Admin', 'aDMAX');
        $this->login('Admin', 'aDMAX');

        $this->url('/despatches/awaiting-checking?menuId=AWAITING_CHECKING');
        $this->_scanLabel(']C0TEST99999999');
        $this->waitForElement('#MAIN_CONNOTE_FORM', 10000);

        foreach (static::$_productsToCheck as $label => $amount) {
            $this->_checkProduct($label, $amount);
        }
    }

    protected function _checkProduct($label, $itemsAmount) {
        $generator = 1;

        $this->_scanLabel($label);

        while ($itemsAmount-- > 0) {
            $this->_scanLabel(']C0SN' . str_pad($generator++, 8, '0', STR_PAD_LEFT));
        }
    }

    protected function setUp()
    {
        $this->setBrowserUrl('http://minder.dev/');
        $this->setSeleniumServerRequestsTimeout(10000);
    }

    protected function tearDown()
    {
//        $this->getConnection()->getConnection()->commit();
        $this->logout();
    }

    protected function _initialSetup() {
        $triggers = array('STOP_DELETE_PICK_ITEM', 'TG_ADD_PICK_ITEM_LINE_NO');
        $this->disableTriggers($triggers);
        $this->cleanInsert()->execute($this->getConnection(), $this->dataSeed());
        $this->enableTriggers($triggers);

        $this->getConnection()->getConnection()->commit();
    }

    protected function dataSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/data_seed.yml');
    }
}