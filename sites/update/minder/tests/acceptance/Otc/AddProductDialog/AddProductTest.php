<?php

class Otc_AddProductDialog_AddProductTest extends Otc_AddProductDialog_AbstractCase {
    /**
     * @param $productLabel
     * @param $expectedProdId
     * @param $expectedDescription
     * @dataProvider dataSetForAddProductDialog
     */
    public function testAddProductDialog($productLabel, $expectedProdId, $expectedCompanyId) {
        $this->assertTrue( $this->byId('add_product_dialog')->displayed());
        $this->byId('generateProductId')->click();
        $this->byId('prod_id')->value($productLabel);
        $this->executeScript('var keyUpEvt = $.Event("keyup"); keyUpEvt.keyCode = 13; $("#prod_id").trigger(keyUpEvt);');
        $this->waitForValue('#prod_id', $expectedProdId, 10000);
        $this->assertEquals($expectedProdId, $this->byId('prod_id')->value());

        $this->executeScript('saveProduct();');
        $this->waitForElement('.messages', 10000);

        $expectedDataset = $this->createArrayDataset(array(
            'PROD_PROFILE' => array(
                array('PROD_ID' => $expectedProdId, 'COMPANY_ID' => $expectedCompanyId),
            ),
        ));

        $actualDataset = $this->createQueryDataset();
        $actualDataset->addTable('PROD_PROFILE', "SELECT PROD_ID, COMPANY_ID FROM PROD_PROFILE WHERE PROD_ID = '" . $expectedProdId . "' AND COMPANY_ID = '" . $expectedCompanyId . "'");

        $this->assertDataSetsEqual($expectedDataset, $actualDataset);
    }

    public function dataSetForAddProductDialog() {
        return array(
            array(']E012345678', '12345678', 'ALSTOMPOWR'),
            array(']E01234567891234', '1234567891234', 'ALSTOMPOWR'),
            array(']E012345678912345', '12345678912345', 'ALSTOMPOWR'),
        );
    }

    protected function dataIdentifierSeed() {
        return $this->createArrayDataset(array(
            'PARAM' => array(
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN8', 'MAX_LENGTH' => 8, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN13', 'MAX_LENGTH' => 13, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
                array('DATA_BRAND' => 'DEFAULT', 'DATA_MODEL' => 'DEFAULT', 'DATA_ID' => 'PROD_EAN14', 'MAX_LENGTH' => 14, 'DATA_TYPE' => 3, 'FIXED_LENGTH' => 'T', 'SYMBOLOGY_PREFIX' => ']E0', 'DATA_EXPRESSION' => '[0-9]*', 'DATA_TYPE_ID' => 'PROD_ID'),
            ),
        ));
    }

    protected function productProfileSeed() {
        return $this->createArrayDataset(array(
            'PROD_PROFILE' => array(
                array('PROD_ID' => '12345678', 'SHORT_DESC' => '12345678', 'COMPANY_ID' => 'ALSTOMPOWR'),
                array('PROD_ID' => '1234567891234', 'SHORT_DESC' => '1234567891234', 'COMPANY_ID' => 'ALSTOMPOWR'),
                array('PROD_ID' => '12345678912345', 'SHORT_DESC' => '12345678912345', 'COMPANY_ID' => 'ALSTOMPOWR'),
                array('PROD_ID' => '12345678', 'SHORT_DESC' => '12345678', 'COMPANY_ID' => 'ALL'),
                array('PROD_ID' => '1234567891234', 'SHORT_DESC' => '1234567891234', 'COMPANY_ID' => 'ALL'),
                array('PROD_ID' => '12345678912345', 'SHORT_DESC' => '12345678912345', 'COMPANY_ID' => 'ALL'),
            ),
        ));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->delete()->execute($this->getConnection(), $this->productProfileSeed());
        $this->cleanInsert()->execute($this->getConnection(), $this->dataIdentifierSeed());
        $this->getConnection()->getConnection()->commit();
    }


}