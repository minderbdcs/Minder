<?php

class Otc_AddToolDialog_AbstractCase extends MinderAcceptanceTestCase {
    protected function setUp()
    {
        $this->setupConfig('interbase.local:als', 'loan');
        $this->setBrowserUrl('http://minder.dev/');
    }

    public function setUpPage()
    {
        $this->login('Admin', 'aDMAX');

        $this->url('/otc');
        $addToolBtn = $this->byXPath('/html/body/div[4]/div[2]/table/tbody/tr/td[2]/input[3]');
        $addToolBtn->click();
    }

    protected function tearDown()
    {
        $this->logout();
        parent::tearDown();
    }

}