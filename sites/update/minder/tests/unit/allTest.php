<?php
/**
 * Transaction_AllTest
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

set_include_path(get_include_path() . ':' . realpath('../../includes/'));

// Call AllTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Transaction_AllTest::main');
}
require_once 'PHPUnit/Framework.php';

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';


/**
 * Include the definition of the Transaction_GRNCCTest class
 */
require_once 'Transaction/GRNCCTest.php';

/**
 * Include the definition of the Transaction_GRNCLTest class
 */
require_once 'Transaction/GRNCLTest.php';

/**
 * Include the definition of the Transaction_GRNCNTest class
 */
require_once 'Transaction/GRNCNTest.php';

/**
 * Include the definition of the Transaction_GRNDBTest class
 */
require_once 'Transaction/GRNDBTest.php';

/**
 * Include the definition of the Transaction_GRNDCTest class
 */
require_once 'Transaction/GRNDCTest.php';

/**
 * Include the definition of the Transaction_GRNDGTest class
 */
require_once 'Transaction/GRNDGTest.php';

/**
 * Include the definition of the Transaction_GRNDLTest class
 */
require_once 'Transaction/GRNDLTest.php';

/**
 * Include the definition of the Transaction_GRNVHTest class
 */
require_once 'Transaction/GRNVHTest.php';

/**
 * Include the definition of the Transaction_GRNVITest class
 */
require_once 'Transaction/GRNVITest.php';

/**
 * Include the definition of the Transaction_GRNVPTest class
 */
require_once 'Transaction/GRNVPTest.php';

/**
 * Include the definition of the Transaction_GRNVSTest class
 */
require_once 'Transaction/GRNVSTest.php';

/**
 * Include the definition of the Transaction_UISNATest class
 */
require_once 'Transaction/UISNATest.php';

/**
 * Include the definition of the Transaction_UISCATest class
 */
require_once 'Transaction/UISCATest.php';

/**
 * Include the definition of the Transaction_UIO2ATest class
 */
require_once 'Transaction/UIO2ATest.php';

/**
 * Include the definition of the Transaction_UIO1ATest class
 */
require_once 'Transaction/UIO1ATest.php';

/**
 * Include the definition of the Transaction_UIKTATest class
 */
require_once 'Transaction/UIKTATest.php';

/**
 * Include the definition of the Transaction_UIDIATest class
 */
require_once 'Transaction/UIDIATest.php';

/**
 * Include the definition of the Transaction_UICOATest class
 */
require_once 'Transaction/UICOATest.php';

/**
 * Include the definition of the Transaction_NITPATest class
 */
require_once 'Transaction/NITPATest.php';

/**
 * Include the definition of the Transaction_NIOBATest class
 */
require_once 'Transaction/NIOBATest.php';

/**
 * Include the definition of the Transaction_NID3ATest class
 */
require_once 'Transaction/NID3ATest.php';

/**
 * Include the definition of the Transaction_NIBCATest class
 */
require_once 'Transaction/NIBCATest.php';

class Transaction_AllTest
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite("PHPUnit Framework");

        $suite->addTestSuite("Transaction_GRNCCTest");
        $suite->addTestSuite("Transaction_GRNCLTest");
        $suite->addTestSuite("Transaction_GRNCNTest");
        $suite->addTestSuite("Transaction_GRNDBTest");
        $suite->addTestSuite("Transaction_GRNDCTest");
        $suite->addTestSuite("Transaction_GRNDGTest");
        $suite->addTestSuite("Transaction_GRNDLTest");
        $suite->addTestSuite("Transaction_GRNVHTest");
        $suite->addTestSuite("Transaction_GRNVITest");
        $suite->addTestSuite("Transaction_GRNVPTest");
        $suite->addTestSuite("Transaction_GRNVSTest");

        $suite->addTestSuite("Transaction_UISNATest");
        $suite->addTestSuite("Transaction_UISCATest");
        $suite->addTestSuite("Transaction_UIO2ATest");
        $suite->addTestSuite("Transaction_UIO1ATest");
        $suite->addTestSuite("Transaction_UIKTATest");
        $suite->addTestSuite("Transaction_UIDIATest");
        $suite->addTestSuite("Transaction_UICOATest");
        $suite->addTestSuite("Transaction_NITPATest");
        $suite->addTestSuite("Transaction_NIOBATest");
        $suite->addTestSuite("Transaction_NID3ATest");
        $suite->addTestSuite("Transaction_NIBCATest");

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
