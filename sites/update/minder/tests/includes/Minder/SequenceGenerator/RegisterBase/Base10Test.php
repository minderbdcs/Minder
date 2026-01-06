<?php
  
// Setup the environment and includes path
$rootDir = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..'. DIRECTORY_SEPARATOR . '..');

set_include_path(get_include_path()
    . PATH_SEPARATOR . $rootDir . DIRECTORY_SEPARATOR . 'tests');
  
require_once('autoload.php');

class Minder_SequenceGenerator_RegisterBase_Base10Test extends PHPUnit_Framework_TestCase {
    
    public function testInitDefault() {
        $register = new Minder_SequenceGenerator_RegisterBase_Base10();
        
        $this->assertEquals(10, $register->getBase());
        $this->assertEquals('0', $register->fromBase10(Minder_SequenceGenerator_RegisterBase_Abstract::NAN));
    }
    
    /**
    * @dataProvider convertProvider
    * 
    */
    public function testConvert($integer, $string) {
        $register = new Minder_SequenceGenerator_RegisterBase_Base10();

        $this->assertEquals($integer, $register->toBase10($string));
        $this->assertEquals($string, $register->fromBase10($integer));
    }
    
    public function testException() {
        $this->setExpectedException('Minder_SequenceGenerator_RegisterBase_Exception');
        
        $register = new Minder_SequenceGenerator_RegisterBase_Base10();
        $register->toBase10(15);
    }
    
    /**
    * @dataProvider nanValueProvider
    * 
    */
    public function testNanValue($nanValue) {
        $register = new Minder_SequenceGenerator_RegisterBase_Base10($nanValue);
        $this->assertEquals($nanValue, $register->fromBase10(Minder_SequenceGenerator_RegisterBase_Abstract::NAN));
        
    }
    
    public function convertProvider() {
        return array(
            array(0, '0'),
            array(1, '1'),
            array(2, '2'),
            array(3, '3'),
            array(4, '4'),
            array(5, '5'),
            array(6, '6'),
            array(7, '7'),
            array(8, '8'),
            array(9, '9')
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
