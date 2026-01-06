<?php
/**
 * Transaction_GRNVSTest
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

// Call Transaction_GRNVSTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_GRNVSTest::main');
}
require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';

/**
 * Include the definition of the Transaction_GRNVS class
 */
require_once 'Transaction/GRNVS.php';

/**
 * Automatic test based on PHPUnit for
 * Transaction_GRNVSTest
 * test correct return values
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Transaction_GRNVSTest extends PHPUnit_Framework_TestCase
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

        $suite  = new PHPUnit_Framework_TestSuite('Transaction_GRNVSTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
    * test class method 'getObjectId()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetObjectId()
    {
        $cls = new Transaction_GRNVS;
        $this->assertEquals("", $cls->getObjectId());
        $cls->supplierId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId       = '222222222222222222222222222222222222222233333333334444444444';
        $cls->divisionId    = '333333333333333333333333333333333333333344444444445555555555';
        $cls->locationId    = '444444444444444444444444444444444444444455555555556666666666';
        $cls->grnNo         = '555555555555555555555555555555555555555566666666667777777777';
        $cls->ssnId         = '666666666666666666666666666666666666666677777777778888888888';
        $cls->issnId        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->kitFlag       = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->totalVerified = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $this->assertEquals("111111111111111111111111111111111111111122222222223333333333222222222222222222222222222222222222222233333333334444444444333333333333333333333333333333333333333344444444445555555555", $cls->getObjectId());
    }

    /**
    * test class method 'getLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetLocation()
    {
        $cls = new Transaction_GRNVS;
        $this->assertEquals("", $cls->getLocation());
        $cls->supplierId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId       = '222222222222222222222222222222222222222233333333334444444444';
        $cls->divisionId    = '333333333333333333333333333333333333333344444444445555555555';
        $cls->locationId    = '444444444444444444444444444444444444444455555555556666666666';
        $cls->grnNo         = '555555555555555555555555555555555555555566666666667777777777';
        $cls->ssnId         = '666666666666666666666666666666666666666677777777778888888888';
        $cls->issnId        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->kitFlag       = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->totalVerified = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $this->assertEquals("444444444444444444444444444444444444444455555555556666666666", $cls->getLocation());
    }

    /**
    * test class method 'getSubLocation()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetSubLocation()
    {
        $cls = new Transaction_GRNVS;
        $this->assertEquals("", $cls->getSubLocation());
        $cls->supplierId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId       = '222222222222222222222222222222222222222233333333334444444444';
        $cls->divisionId    = '333333333333333333333333333333333333333344444444445555555555';
        $cls->locationId    = '444444444444444444444444444444444444444455555555556666666666';
        $cls->grnNo         = '555555555555555555555555555555555555555566666666667777777777';
        $cls->ssnId         = '666666666666666666666666666666666666666677777777778888888888';
        $cls->issnId        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->kitFlag       = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->totalVerified = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $this->assertEquals("555555555555555555555555555555555555555566666666667777777777", $cls->getSubLocation());
    }

    /**
    * test class method 'getReference()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetReference()
    {
        $cls = new Transaction_GRNVS;
        $this->assertEquals("||", $cls->getReference());
        $cls->supplierId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId       = '222222222222222222222222222222222222222233333333334444444444';
        $cls->divisionId    = '333333333333333333333333333333333333333344444444445555555555';
        $cls->locationId    = '444444444444444444444444444444444444444455555555556666666666';
        $cls->grnNo         = '555555555555555555555555555555555555555566666666667777777777';
        $cls->ssnId         = '666666666666666666666666666666666666666677777777778888888888';
        $cls->issnId        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->kitFlag       = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->totalVerified = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $this->assertEquals("666666666666666666|777777777777777777|88888888888888888888888888888888888888889999999999aaaaaaaaaa", $cls->getReference());
    }

    /**
    * test class method 'getQuantity()' with empty and assigned properties
    *
    * @return void;
    */
    public function testgetQuantity()
    {
        $cls = new Transaction_GRNVS;
        $this->assertEquals("", $cls->getQuantity());
        $cls->supplierId    = '111111111111111111111111111111111111111122222222223333333333';
        $cls->ownerId       = '222222222222222222222222222222222222222233333333334444444444';
        $cls->divisionId    = '333333333333333333333333333333333333333344444444445555555555';
        $cls->locationId    = '444444444444444444444444444444444444444455555555556666666666';
        $cls->grnNo         = '555555555555555555555555555555555555555566666666667777777777';
        $cls->ssnId         = '666666666666666666666666666666666666666677777777778888888888';
        $cls->issnId        = '777777777777777777777777777777777777777788888888889999999999';
        $cls->kitFlag       = '88888888888888888888888888888888888888889999999999aaaaaaaaaa';
        $cls->totalVerified = '9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb';
        $this->assertEquals("9999999999999999999999999999999999999999aaaaaaaaaabbbbbbbbbb", $cls->getQuantity());
    }

}
