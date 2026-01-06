<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * MasterTable_Field
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * @throws    Exception
 */
class MasterTable_Field
{
    /**
     * field name
     *
     * @var string
     */
    public $name;

    /**
     * field size in bytes
     *
     * @var integer
     */
    public $length;

    /**
     * field type
     *
     * @var string
     */
    public $type;

    /**
     * table or dataset field relate to which.
     *
     * @var unknown_type
     */
    public $relation;

    public function __construct($name, $length, $type, $relation)
    {
        $error  = false;
        $errmsg = '';

        if (null != $name) {
            $this->name = $name;
        } else {
            $error = $error && true;
            $errmsg .= 'name, ';
        }

        if (null != $length) {
            $this->length = (int)$length;
        } else {
            $error = $error && true;
            $errmsg .= 'length, ';
        }

        if (null != $type) {
            $this->type = $type;
        } else {
            $error = $error && true;
            $errmsg .= 'type, ';
        }
        if ($error) {
            throw new Exception('Field ' . substr($errmsg, 0, -2) . ' can\'t be null');
        }
        $this->relation = $relation;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'length':
                return $this->length;
                break;
            case 'name':
                return $this->name;
                break;
            case 'type':
                return $this->type;
                break;
            case 'relation':
                return $this->relation;
                break;
        }
    }
}
