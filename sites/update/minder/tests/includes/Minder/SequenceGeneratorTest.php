<?php
// Setup the environment and includes path
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

set_include_path(get_include_path()
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'tests');
  
require_once('autoload.php');

class Minder_SequenceGeneratorTest extends PHPUnit_Framework_TestCase {
    public function testNumericGenerator() {
        $sequenceGenerator = new Minder_SequenceGenerator(0, 99, 1, 2, new Minder_SequenceGenerator_RegisterBase_Base10());
        
        $result = array();
        foreach ($sequenceGenerator as $sequenceValue)
            $result[] = $sequenceValue;
            
        $this->assertEquals(100, count($result));
        $this->assertContains('00', $result);
        $this->assertContains('99', $result);
        $this->assertContains('01', $result);
        $this->assertContains('02', $result);
        $this->assertContains('20', $result);
    }
    
    public function testAlphaGenerator() {
        $sequenceGenerator = new Minder_SequenceGenerator('AA', 'ZZ', 1, 2, new Minder_SequenceGenerator_RegisterBase_Alpha());
        
        $result = array();
        foreach ($sequenceGenerator as $sequenceValue)
            $result[] = $sequenceValue;
            
        $this->assertEquals(676, count($result));
        $this->assertContains('AA', $result);
        $this->assertContains('ZZ', $result);
        $this->assertContains('AB', $result);
        $this->assertContains('BA', $result);
        $this->assertContains('AZ', $result);
        $this->assertContains('ZA', $result);
    }
    
    public function testANGenerator() {
        $sequenceGenerator = new Minder_SequenceGenerator(
                                    'A0', 
                                    'Z9', 
                                    1, 
                                    2, 
                                    array (
                                        new Minder_SequenceGenerator_RegisterBase_Base10(),
                                        new Minder_SequenceGenerator_RegisterBase_Alpha()
                                    )
        );
        
        $result = array();
        foreach ($sequenceGenerator as $sequenceValue)
            $result[] = $sequenceValue;
            
        $this->assertEquals(260, count($result));
        $this->assertContains('A1', $result);
        $this->assertContains('Z9', $result);
        $this->assertContains('A9', $result);
        $this->assertContains('B1', $result);
        $this->assertContains('Z1', $result);
        $this->assertContains('Z0', $result);
        
    }
    
    public function testNAGenerator() {
        $sequenceGenerator = new Minder_SequenceGenerator(
                                    '0A', 
                                    '9Z', 
                                    1, 
                                    2, 
                                    array (
                                        new Minder_SequenceGenerator_RegisterBase_Alpha(),
                                        new Minder_SequenceGenerator_RegisterBase_Base10()
                                    )
        );
        
        $result = array();
        foreach ($sequenceGenerator as $sequenceValue)
            $result[] = $sequenceValue;
            
        $this->assertEquals(260, count($result));
        $this->assertContains('0A', $result);
        $this->assertContains('9Z', $result);
        $this->assertContains('1A', $result);
        $this->assertContains('1Z', $result);
        $this->assertContains('0Z', $result);
        $this->assertContains('1C', $result);
        
    }
}
