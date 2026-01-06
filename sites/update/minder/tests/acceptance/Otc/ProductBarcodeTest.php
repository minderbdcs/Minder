<?php

class Otc_ProductBarcodeTest extends MinderAcceptanceTestCase {
    /**
     * @param $productLabel
     * @param $expectedProdId
     * @param $expectedDescription
     * @dataProvider dataSetForScreenAcceptsProductLabel
     */
    public function testScreenAcceptsProductLabel($productLabel, $expectedProdId, $expectedDescription) {
        $this->byId('barcode')->value($productLabel);
        $this->executeScript('$("#barcode").blur();');
        $this->waitForValue('#field3_issues', $expectedProdId, 10000);
        $this->assertEquals($expectedProdId, $this->byId('field3_issues')->value());
        $this->waitForValue('#field3_tip_issues', $expectedDescription, 10000);
        $this->assertEquals($expectedDescription, $this->byId('field3_tip_issues')->value());
    }

    public function dataSetForScreenAcceptsProductLabel() {
        return array(
            array(']E012345678', '12345678', '[12345678] 12345678'),
            array(']E01234567891234', '1234567891234', '[1234567891234] 1234567891234'),
            array(']E012345678912345', '12345678912345', '[12345678912345] 12345678912345'),
        );
    }

    protected function _dataSeed() {
        return $this->createArrayDataset(array(
            'PARAM' => array(
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN8', 'MAX_LENGTH' => 8, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN13', 'MAX_LENGTH' => 13, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN14', 'MAX_LENGTH' => 14, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
            ),
            'PROD_PROFILE' => array(
                array('PROD_ID' => '12345678', 'SHORT_DESC' => '12345678', 'COMPANY_ID' => 'ALL'),
                array('PROD_ID' => '1234567891234', 'SHORT_DESC' => '1234567891234', 'COMPANY_ID' => 'ALL'),
                array('PROD_ID' => '12345678912345', 'SHORT_DESC' => '12345678912345', 'COMPANY_ID' => 'ALL'),
                array('PROD_ID' => '12345678912345', 'SHORT_DESC' => '12345678912345', 'COMPANY_ID' => 'ALSTOMPOWR'),
            ),
        ));
    }

    protected function setUp()
    {
        $this->setupConfig('interbase.local:als', 'loan');
        $this->setBrowserUrl('http://minder.dev/');
        $this->cleanInsert()->execute($this->getConnection(), $this->_dataSeed());
        $this->getConnection()->getConnection()->commit();
    }

    public function setUpPage()
    {
        $this->login('Admin', 'aDMAX');

        $this->url('/otc');
    }

    protected function tearDown()
    {
        $this->logout();
        parent::tearDown();
    }

}