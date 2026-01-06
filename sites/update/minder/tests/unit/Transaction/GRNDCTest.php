<?php
/**
 * Transaction_GRNDCTest
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

// Call Transaction_GRNDCTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_GRNDCTest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_GRNDC class
 */
require_once 'Transaction/GRNDC.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_GRNDCTest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNDCTest extends PHPUnit_Framework_TestCase
{
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     * @return void
     */
    public static function main()
    {
        require_once 'PHPUnit/TextUI/TestRunner.php';

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_GRNDCTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_GRNDC;
        $this->assertEquals("", $cls->getObjectId());
        $cls->grnNo   = '111111111111111111111111111111111111111122222222223333333333';
        $cls->comment = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("2222222222222222222222222222222222222222", $cls->getObjectId());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_GRNDC;
        $this->assertEquals("", $cls->getLocation());
        $cls->grnNo   = '111111111111111111111111111111111111111122222222223333333333';
        $cls->comment = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("3333333333", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_GRNDC;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->grnNo   = '111111111111111111111111111111111111111122222222223333333333';
        $cls->comment = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333", $cls->getSubLocation());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_GRNDC;
        $this->assertEquals("", $cls->getReference());
        $cls->grnNo   = '111111111111111111111111111111111111111122222222223333333333';
        $cls->comment = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("4444444444", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_GRNDC;
        $this->assertEquals("1", $cls->getQuantity());
        $cls->grnNo   = '111111111111111111111111111111111111111122222222223333333333';
        $cls->comment = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("1", $cls->getQuantity());
    }

}
