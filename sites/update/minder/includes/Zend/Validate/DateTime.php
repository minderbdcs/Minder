<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Date.php 6168 2007-08-21 15:31:22Z darby $
 */


/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_DateTime extends Zend_Validate_Abstract
{
    /**
     * Validation failure message key for when the value does not follow the YYYY-MM-DD HH:MM:SS format
     */
    const WRONG_FORMAT = 'dateTimeWrongFormat';

    /**
     * Validation failure message key for when the value does not appear to be a valid date
     */
    const INVALID        = 'dateTimeInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::WRONG_FORMAT => "'%value%' is not of the format YYYY-MM-DD HH:MM:SS",
        self::INVALID        => "'%value%' does not appear to be a valid datetime"
    );

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid date of the format YYYY-MM-DD
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $valueString = (string) $value;

        $this->_setValue($valueString);

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{1,2}:\d{2}:\d{2}?$/', $valueString)) {
            $this->_error(self::WRONG_FORMAT);
            return false;
        }

        list($year, $month, $day, $hour, $minute, $second) = sscanf($valueString, '%d-%d-%d %d:%d:%d');

        if (!checkdate($month, $day, $year)) {
            $this->_error(self::INVALID);
            return false;
        }
        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }

}
