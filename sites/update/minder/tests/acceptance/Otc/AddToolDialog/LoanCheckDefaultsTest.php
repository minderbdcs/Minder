<?php

class Otc_AddToolDialog_LoanCheckDefaultsTest extends Otc_AddToolDialog_AbstractCase {

    protected function optionsSeed() {
        return $this->createYamlDataset(dirname(__FILE__). '/options.yml');
    }

    protected function ssnSeed() {
        return $this->createYamlDataset(dirname(__FILE__) . '/ssn.yml');
    }

    protected function issnSeed() {
        return new ArrayDataSet(array('issn' => array(array('SSN_ID' => '10001000'))));
    }

    protected function ssnSeedWithFCheckedFlags() {
        return $this->createYamlDataset(dirname(__FILE__) . '/ssn2.yml');
    }

    protected function transactionsArchiveDataset() {
        return new ArrayDataSet(array('transactions_archive' => array()));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->cleanInsert()->execute($this->getConnection(), $this->optionsSeed());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->delete()->execute($this->getConnection(), $this->optionsSeed());
    }

    public function testOnToolType1ChangeCheckDefaultsShouldChange() {
        $this->executeScript('$("#type_1").val("ELECTRICAL").change();');

        $this->byLinkText('Safety Test')->click();
        $this->assertTrue($this->byId('loan_safety_check')->selected());
        $this->assertNotEmpty($this->byId('loan_last_safety_check_date')->value());
        $this->assertEquals('3', $this->byId('loan_safety_period_no')->value());
        $this->assertEquals('Q', $this->byId('loan_safety_period')->value());

        $this->byLinkText('Calibration')->click();
        $this->assertTrue($this->byId('loan_calibrate_check')->selected());
        $this->assertNotEmpty($this->byId('loan_last_calibrate_check_date')->value());
        $this->assertEquals('4', $this->byId('loan_calibrate_period_no')->value());
        $this->assertEquals('Y', $this->byId('loan_calibrate_period')->value());

        $this->byLinkText('Inspection')->click();
        $this->assertTrue($this->byId('loan_inspect_check')->selected());
        $this->assertNotEmpty($this->byId('loan_last_inspect_check_date')->value());
        $this->assertEquals('5', $this->byId('loan_inspect_period_no')->value());
        $this->assertEquals('W', $this->byId('loan_inspect_period')->value());
    }

    public function testIfOptionsCheckDefaultsIsEqualToFThenCorrespondingFlagShouldBeUnchecked() {
        $this->executeScript('$("#type_1").val("HYDRAULIC TOOLS").change();');

        $this->byLinkText('Safety Test')->click();
        $this->assertFalse($this->byId('loan_safety_check')->selected());

        $this->byLinkText('Calibration')->click();
        $this->assertFalse($this->byId('loan_calibrate_check')->selected());

        $this->byLinkText('Inspection')->click();
        $this->assertFalse($this->byId('loan_inspect_check')->selected());
    }

    public function testOnSaveButtonSsnShouldBeAddedWithProvidedCheckDefaults() {
        $ssnSeed = $this->ssnSeed();
        $this->delete()->execute($this->getConnection(), $ssnSeed);
        $this->delete()->execute($this->getConnection(), $this->issnSeed());
        $this->truncate()->execute($this->getConnection(), $this->transactionsArchiveDataset());

        $this->getConnection()->getConnection()->commit(); //force commit to be able to see subsequent changes

        $this->timeouts()->implicitWait(10000);
        $this->byId('ssn_id')->value('10001000');
        $this->executeScript('$("#type_1").val("ELECTRICAL").change();');
        $this->executeScript('saveTool();');

        $this->byClassName('messages');

        $columns = implode(', ', $ssnSeed->getTable('ssn')->getTableMetaData()->getColumns());

        $ssnDataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $ssnDataSet->addTable('ssn', "select " . $columns . " from ssn where ssn_id = '10001000'");

        $this->assertDataSetsEqual($ssnSeed, $ssnDataSet);
    }

    public function testCheckFlagsShouldBeEqualToFIfUnselectedOnPage() {
        $ssnSeed = $this->ssnSeedWithFCheckedFlags();
        $this->delete()->execute($this->getConnection(), $ssnSeed);
        $this->delete()->execute($this->getConnection(), $this->issnSeed());
        $this->truncate()->execute($this->getConnection(), $this->transactionsArchiveDataset());

        $this->getConnection()->getConnection()->commit(); //force commit to be able to see subsequent changes

        $this->timeouts()->implicitWait(10000);
        $this->byId('ssn_id')->value('10001000');
        $this->executeScript('$("#type_1").val("ELECTRICAL").change();');
        $this->byLinkText('Safety Test')->click();
        $this->executeScript('$("#loan_safety_check").attr("checked", false);');
        $this->byLinkText('Calibration')->click();
        $this->executeScript('$("#loan_calibrate_check").attr("checked", false);');
        $this->byLinkText('Inspection')->click();
        $this->executeScript('$("#loan_inspect_check").attr("checked", false);');
        $this->executeScript('saveTool();');

        $this->byClassName('messages');

        $columns = implode(', ', $ssnSeed->getTable('ssn')->getTableMetaData()->getColumns());

        $ssnDataSet = new PHPUnit_Extensions_Database_DataSet_QueryDataSet($this->getConnection());
        $ssnDataSet->addTable('ssn', "select " . $columns . " from ssn where ssn_id = '10001000'");

        $this->assertDataSetsEqual($ssnSeed, $ssnDataSet);
    }

}