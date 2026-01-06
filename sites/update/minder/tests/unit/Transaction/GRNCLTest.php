<?php
/**
 * Transaction_GRNCLTest
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

// Call Transaction_GRNCLTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_GRNCLTest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_GRNCL class
 */
require_once 'Transaction/GRNCL.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_GRNCLTest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNCLTest extends PHPUnit_Framework_TestCase
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

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_GRNCLTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_GRNCL;
        $this->assertEquals("", $cls->getObjectId());
        $cls->grnNo      = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->supplierId = '333333333333333333333333333333333333333344444444445555555555';
        $cls->lotNo      = '444444444444444444444444444444444444444455555555556666666666';
        $cls->lotLineNo  = '555555555555555555555555555555555555555566666666667777777777';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333", $cls->getObjectId());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_GRNCL;
        $this->assertEquals("", $cls->getLocation());
        $cls->grnNo      = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->supplierId = '333333333333333333333333333333333333333344444444445555555555';
        $cls->lotNo      = '444444444444444444444444444444444444444455555555556666666666';
        $cls->lotLineNo  = '555555555555555555555555555555555555555566666666667777777777';
        $this->assertEquals("222222222222222222222222222222222222222233333333334444444444", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_GRNCL;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->grnNo      = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->supplierId = '333333333333333333333333333333333333333344444444445555555555';
        $cls->lotNo      = '444444444444444444444444444444444444444455555555556666666666';
        $cls->lotLineNo  = '555555555555555555555555555555555555555566666666667777777777';
        $this->assertEquals("333333333333333333333333333333333333333344444444445555555555", $cls->getSubLocation());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_GRNCL;
        $this->assertEquals("||", $cls->getReference());
        $cls->grnNo      = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->supplierId = '333333333333333333333333333333333333333344444444445555555555';
        $cls->lotNo      = '444444444444444444444444444444444444444455555555556666666666';
        $cls->lotLineNo  = '555555555555555555555555555555555555555566666666667777777777';
        $this->assertEquals("444444444444444444444444444444444444444455555555556666666666|555555555555555555555555555555555555555566666666667777777777|", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_GRNCL;
        $this->assertEquals("1", $cls->getQuantity());
        $cls->grnNo      = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->supplierId = '333333333333333333333333333333333333333344444444445555555555';
        $cls->lotNo      = '444444444444444444444444444444444444444455555555556666666666';
        $cls->lotLineNo  = '555555555555555555555555555555555555555566666666667777777777';
        $this->assertEquals("1", $cls->getQuantity());
    }

}
