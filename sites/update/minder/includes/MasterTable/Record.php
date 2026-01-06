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
 * MasterTable_Record
 *
 * Provides record with field info
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * @throws    Exception
 */
class MasterTable_Record implements Iterator, ArrayAccess
{
    /**
     * Fields info
     *
     * @var array of Field
     */
    protected $fields;

    /**
     * Record data
     * KEY is fieldname, VALUE is fieldvalue
     *
     * @var array
     */
    protected $data;

    /**
     * Count of fields
     *
     * @var int
     */
    protected $numFields;

    /**
     * Value of current field
     *
     * @var mixed
     */
    protected $current;

    /**
     * Array of fields which are used for identification fo row
     *
     * @var array
     */
    protected $uniqueConstraint;

    /**
     * List of Zend_Validate for validation
     *
     * @var array
     */
    protected $validators;

    /**
     * List of validation Errors
     *
     * @var array
     */
    protected $validationErrors;

    /**
     * Flag indicates changes of content
     *
     * @var boolean
     */
    protected $modified = false;

    protected $deleted = false;

    protected $new = false;

    /**
     * ID formed as SQL clause for direct insert into SQL WHERE statement
     *
     * @var string
     */
    protected $idAsSql;

    /**
     * Unique ID for record
     *
     * @var string
     */
    public $id;

    /**
     * Create dataset from associative array and array of Field
     *
     * @param array          $datasource
     * @param array of Field $fieldsinfo
     * @param array          $uniqueConstraint array of key fields
     */
    public function __construct(array $datasource, array $fieldsinfo, array $uniqueConstraint, &$validators, $isNew = false)
    {
    	$this->fields     = $fieldsinfo;
        $this->data       = $datasource;
        $this->numFields  = count($fieldsinfo);
        $this->validators =& $validators;
        $this->new        = $isNew;
		
        $id            = '';
        $this->idAsSql = '';
        $this->uniqueConstraint = $uniqueConstraint;
        $count = 0;
        $limit = 10;
        if (false == $isNew) {
        	foreach($uniqueConstraint as $key => $val){
        		if(stripos($val, 'ARCHIVE_TABLE') !== false){
        			$limit = 2;
        			break;	
        		}
        	}
        	
        	foreach ($uniqueConstraint as $key => $val) {
             	$id            .= $datasource[trim($key)];
                $this->idAsSql .= trim($key) . ' = \'' . addslashes($datasource[trim($key)]) . '\' AND ';
                $count++;
                
                if($count > $limit) {
                	break;
                }
            
            }
            $this->idAsSql = substr($this->idAsSql, 0, -5);
        }
        $this->id      = $id;
        $this->current = current($this->data);
    }

    /**
     * Update row
     *
     * @param array $row
     * @return boolean
     */
    public function save(array $row)
    {
        $result = true;
        $this->validationErrors = array();
        $log = Zend_Registry::get('logger');
        $objMinder = Minder::getInstance();
        foreach ($row as $key => $val) {

            if($objMinder->isNewDateCalculation() == true){
                if($objMinder->isValidDate($val)){
                    $val = $objMinder->getFormatedDateToDb($val);
                }
            }

            $key = strtoupper($key);
            if (array_key_exists($key, $this->data)) {
                if (array_key_exists($key, $this->validators)) {
                    if ((null != $val) && (false != $this->validators[$key]) && (false == $this->validators[$key]->isValid($val))) {
                        $this->validationErrors[] = $key . ' = ' . implode('; ', $this->validators[$key]->getMessages());
                        $result = false;
                    }
                }
                
                //check if someone try to modify RECORD_ID (which is autogenerating field) manualy
                if (($key == 'RECORD_ID') && ((int)$this->data[$key] !== (int)$val)) {
                    $this->validationErrors[] = $key . ' is autogenerating field, you can\'t change it manualy.';
                    $result = false;
                }
                
                if ($this->data[$key] !== $val) {
                    $this->data[$key] = $val;
                    $this->modified   = true;
                } else {
                    $log->info($key . PHP_EOL . $val);
                }
            }
        }
        return $result;
    }

    public function regenerateId()
    {
            $id = '';
            foreach ($this->uniqueConstraint as $key => $val) {
                $id            .= $this->data[trim($key)];
                $this->idAsSql .= trim($key) . ' = \'' . $this->data[trim($key)] . '\' AND ';
            }
            $this->id = $id;
            $this->idAsSql = substr($this->idAsSql, 0, -5);
    }

    /**
     * get list of validation errors
     *
     * @return array
     */
    public function getValidationErrorList()
    {
        $errList = $this->validationErrors;
        $this->validationErrors = array();
        return $errList;
    }

    public function getRawData()
    {
        return $this->data;
    }

    /**
     * Flag indicates changes of content
     *
     * @return boolean
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * Flag indicates that the record should be removed.
     */
    public function isDeleted($flag = null)
    {
        if (!is_null($flag)) {
            $this->deleted = (bool) $flag;
            return $this;
        }
        return $this->deleted;
    }

    /**
     * Reset modified flag to unmodified
     *
     */
    public function unModified() {
        $this->modified = false;
        $this->new      = false;
    }

    /**
     * TRUE if record is NEW, else FALSE
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Return ID as SQL-ready string for WHERE clause
     *
     * @return string
     */
    public function getIdAsSql()
    {
        return $this->idAsSql;
    }

    /**
     * get list of Unique keys
     *
     * @return array
     */
    public function getUniqueConstraint()
    {
        return $this->uniqueConstraint;
    }

    /**
     * Return number of fields
     *
     * @return int
     */
    public function count()
    {
        return $this->numRecords;
    }

    /**
     * Return info for field specified by $fieldname
     *
     * @param string $fieldname
     * @return Field
     */
    public function getFieldInfo($fieldname)
    {
        return $this->fields[$fieldname];
    }

    public function rewind() {
        if (false !== ($temp = reset($this->data))) {
            $this->current = $temp;
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    /**
     * get data from current field
     *
     * @return mixed
     */
    public function current() {
        return $this->current;
    }

    public function key() {
        return key($this->data);
    }

    public function next() {
        if (false !== ($temp = next($this->data))) {
            $this->current = $temp;
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    public function prev() {
        if (false !== ($temp = prev($this->data))) {
            $this->current = $temp;
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    public function valid() {
        return ($this->current() !== false);
    }

    /**
     * Magic method to access fields value
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return false;
        }
    }

    /**
     * Check is exist array element
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->data);
    }

    /**
     * Allow access to property as array element
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    /**
     * Allow access to property as array element
     *
     * @param string $offset
     * @param mixed  $value
     * @return mixed
     */
    public function offsetSet($offset, $value) {
        if (array_key_exists($offset, $this->data)) {
            if ($this->validators[$offset]->isValid($value)) {
                $items = $this->data;
                $items[$offset] = $value;
                $this->data = $items;
                return true;
            } else {
                throw new Exception ($offset . ' = ' . implode('; ', $this->validators[$offset]->getMessages()));
            }
        } else {
            throw new Exception ($offset . ' doesn\'t exists.');
        }
    }

    /**
     * Implements unset method for elements
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) {
        $items = $this->data;
        unset($items[$offset]);
        $this->data = $items;
        return true;
    }

}
