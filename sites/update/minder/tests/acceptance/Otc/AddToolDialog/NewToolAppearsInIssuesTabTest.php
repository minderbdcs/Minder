<?php

class Otc_AddToolDialog_NewToolAppearsInIssuesTabTest extends Otc_AddToolDialog_AbstractCase {
    protected function toolSeed() {
        return new ArrayDataSet(array(
            'issn' => array(array('SSN_ID' => '10001000')),
            'ssn' => array(array('SSN_ID' => '10001000')),
        ));
    }

    public function testWhenToolAddedSsnIdShouldAppearInProductToolField() {
        $this->delete()->execute($this->getConnection(), $this->toolSeed());

        $this->byId('ssn_id')->value('10001000');
        $this->byId('type_1')->value('AIR');
        $this->executeScript('saveTool();');

        $this->assertTrue($this->waitForValue('#field3_issues', '10001000', 10000));
    }
}