<?php
/**
 * UIPCATest
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

// Call Transaction_UIPCATest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_UIPCATest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_UIPCA class
 */
require_once 'Transaction/UIPCA.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_UIPCATest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_UIPCATest extends PHPUnit_Framework_TestCase
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

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_UIPCATest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_UIPCA;
        $this->assertEquals("", $cls->getObjectId());
        $cls->objectId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->prodIdValue = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333", $cls->getObjectId());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_UIPCA;
        $this->assertEquals("", $cls->getReference());
        $cls->objectId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->prodIdValue = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("222222222222222222222222222222222222222233333333334444444444", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_UIPCA;
        $this->assertEquals("1", $cls->getQuantity());
        $cls->objectId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->prodIdValue = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("1", $cls->getQuantity());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_UIPCA;
        $this->assertEquals("", $cls->getLocation());
        $cls->objectId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->prodIdValue = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_UIPCA;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->objectId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->prodIdValue = '222222222222222222222222222222222222222233333333334444444444';
        $this->assertEquals("", $cls->getSubLocation());
    }

}
