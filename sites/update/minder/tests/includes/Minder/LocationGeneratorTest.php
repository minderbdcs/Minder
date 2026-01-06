<?php

// Setup the environment and includes path
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');

set_include_path(get_include_path()
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'tests');
  
require_once('autoload.php');

class Minder_LocationGeneratorTest extends PHPUnit_Framework_TestCase {
    
    protected $locationGenerator = null;
    
    protected function setUp() {
        $this->locationGenerator = new Minder_LocationGenerator('ZW', array());
    }
    
    public function testSetSequenceBadSequenceName() {
        $this->setExpectedException('Minder_LocationGenerator_Exception');
        
        $this->locationGenerator->setSequence('unsupported');
    }
    
    /**
    * @dataProvider setSequenceProvider
    */
    public function testSetSequence($sequenceName) {
        $this->locationGenerator->setSequence($sequenceName);
        $this->assertArrayHasKey($sequenceName, $this->locationGenerator->sequenceArray);
        $this->assertNotNull($this->locationGenerator->sequenceArray[$sequenceName]);
    }
    
    public function testCheckSequenceInitialization() {
        $locationGenerator = new Minder_LocationGenerator('ZW', array());
        
        try {
            $locationGenerator->doGenerate();
            $this->fail('Minder_LocationGenerator_Exception expected.');
        } catch (Minder_LocationGenerator_Exception $e) {
            $this->assertArrayNotHasKey(Minder_LocationGenerator::AISLE, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::BAY, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::SHELF, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::POSITION, $locationGenerator->sequenceArray);
        }
        
        try {
            $locationGenerator->setSequence(Minder_LocationGenerator::AISLE);
            $locationGenerator->doGenerate();
            $this->fail('Minder_LocationGenerator_Exception expected.');
        } catch (Minder_LocationGenerator_Exception $e) {
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::AISLE]);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::BAY, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::SHELF, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::POSITION, $locationGenerator->sequenceArray);
        }
        
        try {
            $locationGenerator->setSequence(Minder_LocationGenerator::BAY);
            $locationGenerator->doGenerate();
            $this->fail('Minder_LocationGenerator_Exception expected.');
        } catch (Minder_LocationGenerator_Exception $e) {
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::AISLE]);
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::BAY]);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::SHELF, $locationGenerator->sequenceArray);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::POSITION, $locationGenerator->sequenceArray);
        }
        
        try {
            $locationGenerator->setSequence(Minder_LocationGenerator::SHELF);
            $locationGenerator->doGenerate();
            $this->fail('Minder_LocationGenerator_Exception expected.');
        } catch (Minder_LocationGenerator_Exception $e) {
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::AISLE]);
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::BAY]);
            $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::SHELF]);
            $this->assertArrayNotHasKey(Minder_LocationGenerator::POSITION, $locationGenerator->sequenceArray);
        }

        $locationGenerator->setSequence(Minder_LocationGenerator::POSITION);
        $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::AISLE]);
        $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::BAY]);
        $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::SHELF]);
        $this->assertNotNull($locationGenerator->sequenceArray[Minder_LocationGenerator::POSITION]);
    }
    
    public function testDoGenerate3615() {
        $this->locationGenerator->setSequence(Minder_LocationGenerator::AISLE, 'AB', 'AB', 'A2', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::BAY, '01', '02', 'N2', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::SHELF, '01', '03', 'N2', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::POSITION, '01', '02', 'N2', 1);
        
        $locations = $this->locationGenerator->doGenerate();
        $this->assertEquals(12, count($locations));
        
        $locationIds = array_map(create_function('$item', 'return $item->items["LOCN_ID"];'), $locations);
        
        $requeredLocationIds = array(
            'AB010101',
            'AB010102',
            'AB010201',
            'AB010202',
            'AB010301',
            'AB010302',
            'AB020101',
            'AB020102',
            'AB020201',
            'AB020202',
            'AB020301',
            'AB020302'
        );
        foreach ($requeredLocationIds as $locnId) {
            $this->assertContains($locnId, $locationIds);
        }
    }
    
    public function testDoGenerate() {
        $this->locationGenerator->setSequence(Minder_LocationGenerator::AISLE, 'BL', 'BL', 'A2', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::BAY, 'OC', 'OC', 'A2', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::SHELF, 'K0', 'K1', 'AN', 1);
        $this->locationGenerator->setSequence(Minder_LocationGenerator::POSITION, '00', '99', 'N2', 1);
        
        $locations = $this->locationGenerator->doGenerate();
        $this->assertEquals(200, count($locations));
        $locationIds = array_map(create_function('$item', 'return $item->items["LOCN_ID"];'), $locations);
        
    }
    
    
    
    public function setSequenceProvider() {
        return array(
            array(Minder_LocationGenerator::AISLE),
            array(Minder_LocationGenerator::BAY),
            array(Minder_LocationGenerator::SHELF),
            array(Minder_LocationGenerator::POSITION)
        );
    }
}