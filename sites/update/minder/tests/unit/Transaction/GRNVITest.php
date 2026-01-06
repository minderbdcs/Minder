<?php
/**
 * Transaction_GRNVITest
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

// Call Transaction_GRNVITest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_GRNVITest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_GRNVI class
 */
require_once 'Transaction/GRNVI.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_GRNVITest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNVITest extends PHPUnit_Framework_TestCase
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

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_GRNVITest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_GRNVI;
        $this->assertEquals("", $cls->getObjectId());
        $cls->productId     = '111111111111111111111111111111111111111122222222223333333333';
        $cls->locationId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->grnNo         = '333333333333333333333333333333333333333344444444445555555555';
        $cls->ssnId         = '444444444444444444444444444444444444444455555555556666666666';
        $cls->issnId        = '555555555555555555555555555555555555555566666666667777777777';
        $cls->kitFlag       = '666666666666666666666666666666666666666677777777778888888888';
        $cls->totalVerified = '777777777777777777777777777777777777777788888888889999999999';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333", $cls->getObjectId());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_GRNVI;
        $this->assertEquals("", $cls->getLocation());
        $cls->productId     = '111111111111111111111111111111111111111122222222223333333333';
        $cls->locationId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->grnNo         = '333333333333333333333333333333333333333344444444445555555555';
        $cls->ssnId         = '444444444444444444444444444444444444444455555555556666666666';
        $cls->issnId        = '555555555555555555555555555555555555555566666666667777777777';
        $cls->kitFlag       = '666666666666666666666666666666666666666677777777778888888888';
        $cls->totalVerified = '777777777777777777777777777777777777777788888888889999999999';
        $this->assertEquals("222222222222222222222222222222222222222233333333334444444444", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_GRNVI;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->productId     = '111111111111111111111111111111111111111122222222223333333333';
        $cls->locationId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->grnNo         = '333333333333333333333333333333333333333344444444445555555555';
        $cls->ssnId         = '444444444444444444444444444444444444444455555555556666666666';
        $cls->issnId        = '555555555555555555555555555555555555555566666666667777777777';
        $cls->kitFlag       = '666666666666666666666666666666666666666677777777778888888888';
        $cls->totalVerified = '777777777777777777777777777777777777777788888888889999999999';
        $this->assertEquals("333333333333333333333333333333333333333344444444445555555555", $cls->getSubLocation());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_GRNVI;
        $this->assertEquals("||", $cls->getReference());
        $cls->productId     = '111111111111111111111111111111111111111122222222223333333333';
        $cls->locationId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->grnNo         = '333333333333333333333333333333333333333344444444445555555555';
        $cls->ssnId         = '444444444444444444444444444444444444444455555555556666666666';
        $cls->issnId        = '555555555555555555555555555555555555555566666666667777777777';
        $cls->kitFlag       = '666666666666666666666666666666666666666677777777778888888888';
        $cls->totalVerified = '777777777777777777777777777777777777777788888888889999999999';
        $this->assertEquals("444444444444444444444444444444444444444455555555556666666666|555555555555555555555555555555555555555566666666667777777777|666666666666666666666666666666666666666677777777778888888888", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_GRNVI;
        $this->assertEquals("", $cls->getQuantity());
        $cls->productId     = '111111111111111111111111111111111111111122222222223333333333';
        $cls->locationId    = '222222222222222222222222222222222222222233333333334444444444';
        $cls->grnNo         = '333333333333333333333333333333333333333344444444445555555555';
        $cls->ssnId         = '444444444444444444444444444444444444444455555555556666666666';
        $cls->issnId        = '555555555555555555555555555555555555555566666666667777777777';
        $cls->kitFlag       = '666666666666666666666666666666666666666677777777778888888888';
        $cls->totalVerified = '777777777777777777777777777777777777777788888888889999999999';
        $this->assertEquals("777777777777777777777777777777777777777788888888889999999999", $cls->getQuantity());
    }

}
