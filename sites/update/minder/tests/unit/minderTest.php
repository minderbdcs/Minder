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

// Call MinderTest::main() if this source file is executed directly.
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'MinderTest::main');
}

require_once 'PHPUnit/Framework.php';

set_include_path(get_include_path() . PATH_SEPARATOR . realpath('../../includes/'));

/**
 *  Include the definition of the Minder class
 */
require_once 'Minder.php';

/**
 * Test class for Minder.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <Sergey.Boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 *
 */
class MinderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Instance for minder class
     *
     * @var object
     */
    private $minder;

    /**
     * Link to DB
     *
     * @var object
     */
    private $db;

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

        $suite  = new PHPUnit_Framework_TestSuite('MinderTest');
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * This method is called before a test is executed.
     * Set up the database connection
     * Get instance minder class
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        $this->db     = ibase_connect(Minder::$dbLiveDsn, Minder::$dbUser, Minder::$dbPass);
        $this->minder = Minder::getInstance();
        if (false === $this->db) {
            $this->fail("Can't connect to database '" . Minder::$dbLiveDsn .
                        "'. Test unable to start!");
        }
    }

    /**
     * Tears down - close connection to DB
     * This method is called after a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function tearDown()
    {
        ibase_close($this->db);
    }

    /**
     * Check class returned by Minder::getInstance()
     *
     * @return void
     */
    public function testGetInstance()
    {

        $this->assertTrue(is_a($this->minder->getInstance(), "Minder"));
    }

    /**
     * Insert 2 records. Then verify with value received from
     * getContainerTypeList()
     *
     * @return void
     */
    public function testGetContainerTypeList()
    {
        $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                            CODE,
                                            DESCRIPTION)
                        VALUES('SHIP_CONTR',
                               'getContainerTypeList_TESTCODE01',
                               'DUMMY DESCRIPTION FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                                CODE,
                                                DESCRIPTION)
                            VALUES('SHIP_CONTR',
                                   'getContainerTypeList_TESTCODE02',
                                   'DUMMY DESCRIPTION FOR MINDERTEST')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $toCheck = $this->minder->getContainerTypeList();

                $this->assertTrue(array_key_exists('getContainerTypeList_TESTCODE01',
                                                    $toCheck));
                $this->assertTrue(array_key_exists('getContainerTypeList_TESTCODE02',
                                                    $toCheck));

                $queryDelete = "DELETE FROM OPTIONS
                                 WHERE GROUP_CODE  = 'SHIP_CONTR'
                                   AND CODE        = 'getContainerTypeList_TESTCODE02'
                                   AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM OPTIONS
                             WHERE GROUP_CODE  = 'SHIP_CONTR'
                               AND CODE        = 'getContainerTypeList_TESTCODE01'
                               AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert 1 records. Then verify with value received from
     * getLastLineNo()
     *
     * @return void
     */
    public function testGetLastLineNo()
    {
        $queryInsert = "INSERT INTO GRN(ORDER_NO,
                                        LAST_LINE_NO,
                                        GRN,
                                        COMMENTS)
                        VALUES('ORDER01',
                                255,
                               'TESTGRN01',
                               'DUMMY COMMENTS FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $toCheck = $this->minder->getLastLineNo('TESTGRN01');
            $this->assertEquals(255, $toCheck);

            $queryDelete = "DELETE FROM GRN
                             WHERE ORDER_NO     = 'ORDER01'
                               AND LAST_LINE_NO = 255
                               AND GRN          = 'TESTGRN01'
                               AND COMMENTS     = 'DUMMY COMMENTS FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" . $queryDelete);
            }
        }
    }

    /**
     * Insert 1 records. Then verify with value received from
     * getOrderNoFromGrnNo()
     *
     * @return void
     */
    public function testGetOrderNoFromGrnNo()
    {
        $queryInsert = "INSERT INTO GRN(ORDER_NO,
                                        LAST_LINE_NO,
                                        GRN,
                                        COMMENTS)
                        VALUES('ORDER01',
                                255,
                               'TESTGRN01',
                               'DUMMY COMMENTS FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $toCheck = $this->minder->getOrderNoFromGrnNo('TESTGRN01');
            $this->assertEquals('ORDER01', $toCheck);
            $queryDelete = "DELETE FROM GRN
                             WHERE ORDER_NO     = 'ORDER01'
                               AND LAST_LINE_NO = 255
                               AND GRN          = 'TESTGRN01'
                               AND COMMENTS     = 'DUMMY COMMENTS FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" . $queryDelete);
            }
        }
    }

    /**
     * Insert 2 records. Then verify with value received from
     * getPackagingOwnerList()
     *
     * @return void
     */
    public function testGetPackagingOwnerList()
    {
        $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,CODE, DESCRIPTION)
                        VALUES('PACK_OWNER',
                               'getPackagingOwnerList_TESTCODE01',
                               'DUMMY DESCRIPTION FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,CODE, DESCRIPTION)
                            VALUES('PACK_OWNER',
                                   'getPackagingOwnerList_TESTCODE02',
                                   'DUMMY DESCRIPTION FOR MINDERTEST')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $toCheck = $this->minder->getPackagingOwnerList();

                $this->assertTrue(array_key_exists('getPackagingOwnerList_TESTCODE01',
                                                    $toCheck));
                $this->assertTrue(array_key_exists('getPackagingOwnerList_TESTCODE02',
                                                    $toCheck));

                $queryDelete = "DELETE FROM OPTIONS
                                 WHERE GROUP_CODE  = 'PACK_OWNER'
                                   AND CODE        = 'getPackagingOwnerList_TESTCODE02'
                                   AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM OPTIONS
                             WHERE GROUP_CODE  = 'PACK_OWNER'
                               AND CODE        = 'getPackagingOwnerList_TESTCODE01'
                               AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert 2 records. Then verify with value received from
     * getPackagingTypeList().
     *
     * @return void
     */
    public function testGetPackagingTypeList()
    {
        $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                            CODE,
                                            DESCRIPTION)
                        VALUES('PACK_TYPE',
                               'getPackagingTypeList_TESTCODE01',
                               'DUMMY DESCRIPTION FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                                CODE,
                                                DESCRIPTION)
                            VALUES('PACK_TYPE',
                                   'getPackagingTypeList_TESTCODE02',
                                   'DUMMY DESCRIPTION FOR MINDERTEST')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $toCheck = $this->minder->getPackagingTypeList();

                $this->assertTrue(array_key_exists('getPackagingTypeList_TESTCODE01',
                                                    $toCheck));
                $this->assertTrue(array_key_exists('getPackagingTypeList_TESTCODE02',
                                                    $toCheck));

                $queryDelete = "DELETE FROM OPTIONS
                                 WHERE GROUP_CODE  = 'PACK_TYPE'
                                   AND CODE        = 'getPackagingTypeList_TESTCODE02'
                                   AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM OPTIONS
                             WHERE GROUP_CODE  = 'PACK_TYPE'
                               AND CODE        = 'getPackagingTypeList_TESTCODE01'
                               AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert 2 records. Then verify with value received from
     * getPalletOwnerList()
     *
     * @return void
     */
    public function testGetPalletOwnerList()
    {
        $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                            CODE,
                                            DESCRIPTION)
                        VALUES('PALL_OWNER',
                               'getPalletOwnerList_TESTCODE01',
                               'DUMMY DESCRIPTION FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO OPTIONS(GROUP_CODE,
                                                CODE,
                                                DESCRIPTION)
                            VALUES('PALL_OWNER',
                                   'getPalletOwnerList_TESTCODE02',
                                   'DUMMY DESCRIPTION FOR MINDERTEST')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $toCheck = $this->minder->getPalletOwnerList();

                $this->assertTrue(array_key_exists('getPalletOwnerList_TESTCODE01',
                                                    $toCheck));
                $this->assertTrue(array_key_exists('getPalletOwnerList_TESTCODE02',
                                                    $toCheck));

                $queryDelete = "DELETE FROM OPTIONS
                                 WHERE GROUP_CODE  = 'PALL_OWNER'
                                   AND CODE        = 'getPalletOwnerList_TESTCODE02'
                                   AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM OPTIONS
                             WHERE GROUP_CODE  = 'PALL_OWNER'
                               AND CODE        = 'getPalletOwnerList_TESTCODE01'
                               AND DESCRIPTION = 'DUMMY DESCRIPTION FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert 2 records. Then verify with value received from
     * getPrinterList().
     *
     * @return void
     */
    public function testGetPrinterList()
    {
        // record for testgetPrinterList
        $queryInsert = "INSERT INTO SYS_EQUIP(DEVICE_ID,
                                              DEVICE_TYPE,
                                              EQUIPMENT_DESCRIPTION_CODE)
                        VALUES('ZZ',
                               'PR',
                               'DUMMY DESCRIPTION FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO SYS_EQUIP(DEVICE_ID,
                                                  DEVICE_TYPE,
                                                  EQUIPMENT_DESCRIPTION_CODE)
                            VALUES('ZY',
                                   'PR',
                                   'DUMMY DESCRIPTION FOR MINDERTEST')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $toCheck = $this->minder->getPrinterList();

                $this->assertTrue(array_key_exists('ZY', $toCheck));
                $this->assertTrue(array_key_exists('ZZ', $toCheck));

                $queryDelete = "DELETE FROM SYS_EQUIP
                                 WHERE DEVICE_ID                  = 'ZY'
                                   AND DEVICE_TYPE                = 'PR'
                                   AND EQUIPMENT_DESCRIPTION_CODE = 'DUMMY DESCRIPTION FOR MINDERTEST'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM SYS_EQUIP
                             WHERE DEVICE_ID                  = 'ZZ'
                               AND DEVICE_TYPE                = 'PR'
                               AND EQUIPMENT_DESCRIPTION_CODE = 'DUMMY DESCRIPTION FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert record. Then verify with value received from
     * getProductOwnerList()
     *
     * @return void
     */
    public function testGetProductOwnerList()
    {
        $queryInsert = "INSERT INTO COMPANY(COMPANY_ID, NAME)
                        VALUES('TESTID01',
                               'DUMMY COMPANY NAME FOR MINDERTEST')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $toCheck = $this->minder->getProductOwnerList();

            $this->assertTrue(array_key_exists('TESTID01', $toCheck));
            $this->assertTrue(in_array('DUMMY COMPANY NAME FOR MINDERTEST',
                                        $toCheck));

            $queryDelete = "DELETE FROM COMPANY
                             WHERE COMPANY_ID = 'TESTID01'
                               AND NAME       = 'DUMMY COMPANY NAME FOR MINDERTEST'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert record. Then verify with value received from
     * getReceiveLocationList()
     *
     * @return void
     */
    public function testGetReceiveLocationList()
    {

        $queryInsert = "INSERT INTO LOCATION(LOCN_ID,
                                             LOCN_NAME,
                                             STORE_AREA,
                                             WH_ID)
                        VALUES('TESTLOC01',
                               'DUMMY LOCATION NAME 01',
                               'RC',
                               (SELECT SESSION.DESCRIPTION
                               FROM SESSION
                               WHERE SESSION.CODE = 'CURRENT_WH_ID'
                                 AND SESSION.DEVICE_ID = 'MQ'))";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $toCheck = $this->minder->getReceiveLocationList();

            $this->assertTrue(array_key_exists('TESTLOC01', $toCheck));
            $this->assertTrue(in_array('DUMMY LOCATION NAME 01', $toCheck));

            $queryDelete = "DELETE FROM LOCATION
                             WHERE LOCN_ID    = 'TESTLOC01'
                               AND LOCN_NAME  = 'DUMMY LOCATION NAME 01'
                               AND STORE_AREA = 'RC'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Insert 3 record into PERSON. Then verify with value received from
     * getSentByList()
     *
     * @return void
     */
    public function testGetSentByList()
    {
        $queryInsert = "INSERT INTO PERSON(PERSON_ID,
                                           FIRST_NAME,
                                           PERSON_TYPE)
                        VALUES('TESTID01',
                               'TESTFIRSTNAME01',
                               'CO')";
        $result = ibase_query($this->db, $queryInsert);
        if (false !== $result) {
            $queryInsert = "INSERT INTO PERSON(PERSON_ID,
                                               FIRST_NAME,
                                               PERSON_TYPE)
                            VALUES('TESTID02',
                                   'TESTFIRSTNAME02',
                                   'CS')";
            $result = ibase_query($this->db, $queryInsert);
            if (false !== $result) {
                $queryInsert = "INSERT INTO PERSON(PERSON_ID,
                                                   FIRST_NAME,
                                                   PERSON_TYPE)
                                VALUES('TESTID03',
                                       'TESTFIRSTNAME03',
                                       'CU')";
                $result = ibase_query($this->db, $queryInsert);
                if (false !== $result) {
                    $toCheck = $this->minder->getSentByList();

                    $this->assertTrue(array_key_exists('TESTID01', $toCheck));
                    $this->assertTrue(in_array('TESTFIRSTNAME01', $toCheck));
                    $this->assertTrue(array_key_exists('TESTID02', $toCheck));
                    $this->assertTrue(in_array('TESTFIRSTNAME02', $toCheck));
                    $this->assertTrue(array_key_exists('TESTID03', $toCheck));
                    $this->assertTrue(in_array('TESTFIRSTNAME03', $toCheck));

                    $queryDelete = "DELETE FROM PERSON
                                     WHERE PERSON_ID   = 'TESTID03'
                                       AND FIRST_NAME  = 'TESTFIRSTNAME03'
                                       AND PERSON_TYPE = 'CU'";
                    $result = ibase_query($this->db, $queryDelete);
                    if (false === $result) {
                        $this->fail("Can't clear added record.\n" .
                                    $queryDelete);
                    }
                }
                $queryDelete = "DELETE FROM PERSON
                                 WHERE PERSON_ID   = 'TESTID02'
                                   AND FIRST_NAME  = 'TESTFIRSTNAME02'
                                   AND PERSON_TYPE = 'CS'";
                $result = ibase_query($this->db, $queryDelete);
                if (false === $result) {
                    $this->fail("Can't clear added record.\n" .
                                $queryDelete);
                }
            }
            $queryDelete = "DELETE FROM PERSON
                             WHERE PERSON_ID   = 'TESTID01'
                               AND FIRST_NAME  = 'TESTFIRSTNAME01'
                               AND PERSON_TYPE = 'CO'";
            $result = ibase_query($this->db, $queryDelete);
            if (false === $result) {
                $this->fail("Can't clear added record.\n" .
                            $queryDelete);
            }
        }
    }

    /**
     * Attempt login with UserId, UserPass from localhost
     *
     * @return void
     */
    public function testLogin()
    {
        $this->assertTrue($this->minder->login("a", "a", "127.0.0.1"));
        $this->minder->logout();
    }

    /**
     * Attempt logout user with DEVICE_ID = $deviceId.
     * Then check tables SYS_EQUIP and SYS_USER
     * for null values where DEVICE_ID = $deviceId and USER_ID = $userId
     *
     * @return void
     */
    public function testLogout()
    {
        if ($this->minder->login("a", "a", "127.0.0.1")) {
            $this->minder->logout();

            $queryString = "SELECT CURRENT_PERSON,
                                   CURRENT_LOGGED_ON
                            FROM SYS_EQUIP
                            WHERE DEVICE_ID = ?";
            $query = ibase_prepare($this->db, $queryString);

            if (false !==($result = ibase_execute($query, $this->minder->deviceId))) {
                while (false !== ($row = ibase_fetch_row($result))) {
                    if ($row[0] !== null || $row[1] !== null ) {
                        $this->fail('USER NOT LOGGED OUT');
                    }
                }
                ibase_free_result($result);
            } else {
                echo "Unable to select.\n" . $queryString;
                $this->fail();
            }
            ibase_free_query($query);

            $queryString = "SELECT DEVICE_ID,
                                   LOGIN_DATE
                            FROM SYS_USER
                            WHERE USER_ID = ?";
            $query = ibase_prepare($this->db, $queryString);

            if (false !==($result = ibase_execute($query, $this->minder->userId))) {
                while (false !== ($row = ibase_fetch_row($result))) {
                    if ($row[0] !== null || $row[1] !== null ) {
                        $this->fail('USER NOT LOGGED OUT');
                    }
                }
                ibase_free_result($result);
            } else {
                echo "Unable to select.\n" . $queryString;
                $this->fail();
            }
            ibase_free_query($query);
        } else {
            $this->fail("Can't login with UserId = 'a', Pass = 'a' and IP = '127.0.0.1'");
        }
    }

    /**
     * Attempt logout All users. Then check tables SYS_EQUIP and SYS_USER
     * for null values
     *
     * @return void
     */
    public function testLogoutAll()
    {
        if ($this->minder->login("a", "a", "127.0.0.1")) {
            $this->minder->logoutAll();

            $query = "SELECT CURRENT_PERSON,
                             CURRENT_LOGGED_ON
                        FROM SYS_EQUIP";
            if (false !==($result = ibase_query($query))) {
                while (false !== ($row = ibase_fetch_row($result))) {
                    if ($row[0] !== null || $row[1] !== null ) {
                        $this->fail('Not all users logged out');
                    }
                }
                ibase_free_result($result);
            } else {
                echo "Unable to select.\n" . $queryString;
                $this->fail();
            }
            $queryString = "SELECT DEVICE_ID,
                                   LOGIN_DATE
                            FROM SYS_USER";

            if (false !==($result = ibase_query($query))) {
                while (false !== ($row = ibase_fetch_row($result))) {
                    if ($row[0] !== null || $row[1] !== null ) {
                        $this->fail('Not all users logged out');
                    }
                }
                ibase_free_result($result);
            } else {
                echo "Unable to select.\n" . $queryString;
                $this->fail();
            }
        } else {
            $this->fail("Can't login with UserId = 'a', Pass = 'a' and IP = '127.0.0.1'");
        }
    }

}

// Call MinderTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == 'MinderTest::main') {
    MinderTest::main();
}
?>
