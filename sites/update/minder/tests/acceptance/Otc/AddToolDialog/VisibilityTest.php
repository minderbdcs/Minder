<?php

class Otc_AddToolDialog_VisibilityTest extends Otc_AddToolDialog_AbstractCase {
    public function testDialogIsVisible() {
        $this->assertTrue($this->byId('add_tool_dialog')->displayed(), 'AddTool dialog is visible.');
        $this->assertTrue($this->byId('add_tool_dialog_add_tool')->displayed(), 'Add Tool page is visible by default.');
    }

    public function testAllTabsAreAccessible()
    {
        $this->byLinkText('Safety Test')->click();
        $this->assertTrue($this->byId('add_tool_dialog_safety_test')->displayed(), 'Safety Test page is visible.');

        $this->byLinkText('Calibration')->click();
        $this->assertTrue($this->byId('add_tool_dialog_calibration')->displayed(), 'Calibration page is visible.');

        $this->byLinkText('Inspection')->click();
        $this->assertTrue($this->byId('add_tool_dialog_inspection')->displayed(), 'Inspection page is visible.');

        $this->byLinkText('Add Tool')->click();
        $this->assertTrue($this->byId('add_tool_dialog_add_tool')->displayed(), 'Add Tool page is visible.');
    }

    public function testAllDefaultsAreFilled()
    {
        $this->byLinkText('Safety Test')->click();
        $this->assertFalse($this->byId('loan_safety_check')->selected());
        $this->assertEmpty($this->byId('loan_last_safety_check_date')->value());
        $this->assertEquals('6', $this->byId('loan_safety_period_no')->value());
        $this->assertEquals('M', $this->byId('loan_safety_period')->value());

        $this->byLinkText('Calibration')->click();
        $this->assertFalse($this->byId('loan_calibrate_check')->selected());
        $this->assertEmpty($this->byId('loan_last_calibrate_check_date')->value());
        $this->assertEquals('6', $this->byId('loan_calibrate_period_no')->value());
        $this->assertEquals('M', $this->byId('loan_calibrate_period')->value());

        $this->byLinkText('Inspection')->click();
        $this->assertFalse($this->byId('loan_inspect_check')->selected());
        $this->assertEmpty($this->byId('loan_last_inspect_check_date')->value());
        $this->assertEquals('6', $this->byId('loan_inspect_period_no')->value());
        $this->assertEquals('M', $this->byId('loan_inspect_period')->value());

    }

}