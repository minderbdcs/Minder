<?php
  
// Setup the environment and includes path
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..');

set_include_path(get_include_path()
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'tests');
  
require_once('autoload.php');

class Minder_SequenceGenerator_RegisterBase_AlphaTest extends PHPUnit_Framework_TestCase {
    
    public function testInitDefault() {
        $register = new Minder_SequenceGenerator_RegisterBase_Alpha();
        
        $this->assertEquals(26, $register->getBase());
        $this->assertEquals('A', $register->fromBase10(Minder_SequenceGenerator_RegisterBase_Abstract::NAN));
    }
    
    /**
    * @dataProvider convertProvider
    * 
    */
    public function testConvert($integer, $string) {
        $register = new Minder_SequenceGenerator_RegisterBase_Alpha();

        $this->assertEquals($integer, $register->toBase10($string));
        $this->assertEquals($string, $register->fromBase10($integer));
    }
    
    public function testExceptionFrom() {
        $this->setExpectedException('Minder_SequenceGenerator_RegisterBase_Exception');
        
        $register = new Minder_SequenceGenerator_RegisterBase_Alpha();
        $register->toBase10(15);
    }
    
    public function testExceptionTo() {
        $this->setExpectedException('Minder_SequenceGenerator_RegisterBase_Exception');
        
        $register = new Minder_SequenceGenerator_RegisterBase_Alpha();
        $register->fromBase10(50);
        $register->fromBase10(26);
    }
    
    /**
    * @dataProvider nanValueProvider
    * 
    */
    public function testNanValue($nanValue) {
        $register = new Minder_SequenceGenerator_RegisterBase_Alpha($nanValue);
        $this->assertEquals($nanValue, $register->fromBase10(Minder_SequenceGenerator_RegisterBase_Abstract::NAN));
        
    }
    
    public function convertProvider() {
        return array(
            array(0, 'A'),
            array(1, 'B'),
            array(2, 'C'),
            array(3, 'D'),
            array(4, 'E'),
            array(5, 'F'),
            array(6, 'G'),
            array(7, 'H'),
            array(8, 'I'),
            array(9, 'J'),
            array(10, 'K'),
            array(11, 'L'),
            array(12, 'M'),
            array(13, 'N'),
            array(14, 'O'),
            array(15, 'P'),
            array(16, 'Q'),
            array(17, 'R'),
            array(18, 'S'),
            array(19, 'T'),
            array(20, 'U'),
            array(21, 'V'),
            array(22, 'W'),
            array(23, 'X'),
            array(24, 'Y'),
            array(25, 'Z')
        );
    }
    
    public function nanValueProvider() {
        return array(
            array('0'),
            array('1'),
            array('A'),
            array(''),
            array('_')
        );
    }
}
