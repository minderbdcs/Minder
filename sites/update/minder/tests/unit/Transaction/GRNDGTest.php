<?php
/**
 * Transaction_GRNDGTest
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

// Call Transaction_GRNDGTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_GRNDGTest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_GRNDG class
 */
require_once 'Transaction/GRNDG.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_GRNDGTest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNDGTest extends PHPUnit_Framework_TestCase
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

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_GRNDGTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_GRNDG;
        $this->assertEquals("|", $cls->getObjectId());
        $cls->carrier             = '111111111111111111111111111111111111111122222222223333333333';
        $cls->crateOwner          = '222222222222222222222222222222222222222233333333334444444444';
        $cls->crateQty            = '333333333333333333333333333333333333333344444444445555555555';
        $cls->crateType           = '444444444444444444444444444444444444444455555555556666666666';
        $cls->consignmentNoteNo   = '555555555555555555555555555555555555555566666666667777777777';
        $cls->hasContainer        = '666666666666666666666666666666666666666677777777778888888888';
        $cls->deliveryType        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->orderNo             = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->palletOwner         = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $cls->palletQty           = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc';
        $cls->shipDate            = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd';
        $cls->vehicleRegistration = 'ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee';
        $this->assertEquals("555555555555555555555555555555555555555566666666667777777777|bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd", $cls->getObjectId());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_GRNDG;
        $this->assertEquals("", $cls->getLocation());
        $cls->carrier             = '111111111111111111111111111111111111111122222222223333333333';
        $cls->crateOwner          = '222222222222222222222222222222222222222233333333334444444444';
        $cls->crateQty            = '333333333333333333333333333333333333333344444444445555555555';
        $cls->crateType           = '444444444444444444444444444444444444444455555555556666666666';
        $cls->consignmentNoteNo   = '555555555555555555555555555555555555555566666666667777777777';
        $cls->hasContainer        = '666666666666666666666666666666666666666677777777778888888888';
        $cls->deliveryType        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->orderNo             = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->palletOwner         = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $cls->palletQty           = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc';
        $cls->shipDate            = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd';
        $cls->vehicleRegistration = 'ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_GRNDG;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->carrier             = '111111111111111111111111111111111111111122222222223333333333';
        $cls->crateOwner          = '222222222222222222222222222222222222222233333333334444444444';
        $cls->crateQty            = '333333333333333333333333333333333333333344444444445555555555';
        $cls->crateType           = '444444444444444444444444444444444444444455555555556666666666';
        $cls->consignmentNoteNo   = '555555555555555555555555555555555555555566666666667777777777';
        $cls->hasContainer        = '666666666666666666666666666666666666666677777777778888888888';
        $cls->deliveryType        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->orderNo             = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->palletOwner         = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $cls->palletQty           = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc';
        $cls->shipDate            = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd';
        $cls->vehicleRegistration = 'ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee';
        $this->assertEquals("ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee", $cls->getSubLocation());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_GRNDG;
        $this->assertEquals("|||N|||||", $cls->getReference());
        $cls->carrier             = '111111111111111111111111111111111111111122222222223333333333';
        $cls->crateOwner          = '222222222222222222222222222222222222222233333333334444444444';
        $cls->crateQty            = '333333333333333333333333333333333333333344444444445555555555';
        $cls->crateType           = '444444444444444444444444444444444444444455555555556666666666';
        $cls->consignmentNoteNo   = '555555555555555555555555555555555555555566666666667777777777';
        $cls->hasContainer        = '666666666666666666666666666666666666666677777777778888888888';
        $cls->deliveryType        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->orderNo             = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->palletOwner         = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $cls->palletQty           = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc';
        $cls->shipDate            = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd';
        $cls->vehicleRegistration = 'ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee';
        $this->assertEquals("777777777777777777777777777777777777777788888888889999999999|88888888888888888888888888888888888888889999999999aaaaaaaaaa||666666666666666666666666666666666666666677777777778888888888|9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb|aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc|222222222222222222222222222222222222222233333333334444444444|444444444444444444444444444444444444444455555555556666666666|333333333333333333333333333333333333333344444444445555555555", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_GRNDG;
        $this->assertEquals("1", $cls->getQuantity());
        $cls->carrier             = '111111111111111111111111111111111111111122222222223333333333';
        $cls->crateOwner          = '222222222222222222222222222222222222222233333333334444444444';
        $cls->crateQty            = '333333333333333333333333333333333333333344444444445555555555';
        $cls->crateType           = '444444444444444444444444444444444444444455555555556666666666';
        $cls->consignmentNoteNo   = '555555555555555555555555555555555555555566666666667777777777';
        $cls->hasContainer        = '666666666666666666666666666666666666666677777777778888888888';
        $cls->deliveryType        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->orderNo             = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->palletOwner         = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $cls->palletQty           = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaabbbbbbbbbbcccccccccc';
        $cls->shipDate            = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbccccccccccdddddddddd';
        $cls->vehicleRegistration = 'ccccccccccccccccccccccccccccccccccccccccddddddddddeeeeeeeeee';
        $this->assertEquals("1", $cls->getQuantity());
    }

}
