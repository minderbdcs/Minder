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
 * ModelCollection
 *
 * All access to the Minder database is through the Minder class.
 * Replaces original Model. All properties stored in collection of items.
 *
 * @category  Minder
 * @package   Minder
 * @throws    Exception
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class ModelCollection extends Model implements ArrayAccess
{
    public $items;
    public $id;

    protected $_mandatory = array();

    public function save($data)
    {
        $data = array_change_key_case($data, CASE_UPPER);
        foreach ($this->items as $key => $val) {
            if (isset($data[$key])) {
                if (gettype($data[$key]) == 'string') {
                    $this->items[$key] = trim($data[$key]);
                } else {
                    $this->items[$key] = $data[$key];
                }
            }
        }
        return $this->validate($data);
    }

    public function validate($data)
    {
        return true;
    }

    /**
     * Provides direct access to properties similar to PickOrder, PickItem and other Rich's classes
     *
     * @throws Exception if property does not exists
     * @param string $propName property name
     * @return mixed
     */
    public function __get($propName)
    {
        $formName = strtoupper(preg_replace('/([A-Z])/', '_$1', $propName));
        if (array_key_exists($formName, $this->items)) {
            return $this->items[$formName];
        } else {
            //return 'DATA SOURCE UNKNOWN';
            throw new Exception("Property '" . $propName . "' does not exists!");
        }
    }

    /**
     * Provides direct access to properties similar to PickOrder, PickItem and other classes
     *
     * @throws Exception if property does not exists
     * @param string $propName property name
     * @param mixed  $data     data to be stored
     * @return mixed
     */
    public function __set($propName, $data)
    {
        $formName = strtoupper(preg_replace('/([A-Z])/', '_$1', $propName));
        if (array_key_exists($formName, $this->items)) {
            return $this->items[$formName] = $data;
        } else {
            throw new Exception("Property '" . $propName . "' does not exists!");
        }
    }

    /**
     * Stuff of methods for access properties as array element
     *
     * @param string|integer $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * Stuff of methods for access properties as array element
     *
     * @param string|integer $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
         if ($this->offsetExists($offset)) {
             $items = $this->items;
             unset($items[$offset]);
             $this->items = $items;
         }
    }

    /**
     * Stuff of methods for access properties as array element
     *
     * @param string|integer $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->items[$offset];
        } else {
            throw new Exception('Array key \'' . $offset . '\' not found. ');
        }
    }

    /**
     * Stuff of methods for access properties as array element
     *
     * @param string|integer $offset
     * @return void
     *
     */
    public function offsetSet($offset, $value)
    {
        $items = $this->items;
        $items[$offset] = $value;
        $this->items = $items;
    }

    /**
     * get Array of mandatory fields
     *
     * @return array
     */
    public function getMandatoryList()
    {
        return $this->_mandatory;
    }
}
