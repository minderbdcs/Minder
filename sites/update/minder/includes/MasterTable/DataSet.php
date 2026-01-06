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
 * MasterTable_DataSet provides data with field info
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
class MasterTable_DataSet implements Iterator
{
    protected $data;

    protected $fields;
    protected $numRecords;
    protected $numSkippedRecords;
    protected $limitRecords;
    protected $numFields;
    protected $uniqueConstraint;
    protected $current;
    protected $validators;

    /**
     * array of row numbers
     *
     */
    protected $rowNumbers;

    public $table;

    /**
     * Create dataset from associative array and array of Field
     *
     * @param array          $datasource
     * @param array of Field $fieldsinfo
     * @param array          $uniqueConstraint array of key fields
     */
    public function __construct($datasource, $fieldsinfo, $uniqueConstraint, $numRecordsInTable = 0, $numSkippedRecords = 0)
    {
        $validator = array();
        foreach ($fieldsinfo as $val) {
            switch ($val->type) {
                case 'CHAR':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_StringLength(0, $val->length));
                    $validator[$val->name] = $temp;
                   break;
                case 'VARCHAR':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_StringLength(0, $val->length));
                    $validator[$val->name] = $temp;
                    break;
                case 'SMALLINT':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Int())
                         ->addValidator(new Zend_Validate_Between(-1 * pow(2, 16), pow(2, 16) - 1));
                    $validator[$val->name] = $temp;
                    break;
                case 'INTEGER':
                    $temp = new Zend_Validate();
                    $modified = new Zend_Validate_Int();
                    $modified->setMessage("'%value%' does not appear to be an integer(4)", Zend_Validate_Int::NOT_INT);
                    $temp->addValidator($modified)
                         ->addValidator(new Zend_Validate_Between(-1 * pow(2, 31), pow(2, 31) - 1));
                    $validator[$val->name] = $temp;
                    break;
                case 'BIGINT':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Between(-1 * pow(2, 64), pow(2, 64) - 1));
                    $validator[$val->name] = $temp;
                    break;
                case 'DATE':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Date());
                    $validator[$val->name] = $temp;
                    break;
                case  'TIMESTAMP':
                    /*$temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Date(Zend_Registry::get('config')->date->format));
                    $validator[$val->name] = $temp;*/
                    break;
                case 'BLOB':
                    $validator[$val->name] = false;
                    break;
                case 'NUMERIC':
                    $validator[$val->name] = false;
                    break;
                case 'FLOAT':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Float());
                    $validator[$val->name] = $temp;
                    break;
                case 'DOUBLE PRECISION':
                    $temp = new Zend_Validate();
                    $temp->addValidator(new Zend_Validate_Float());
                    $validator[$val->name] = $temp;
                    break;
            }

        }
        $this->validators = $validator;

        $this->fields            = $fieldsinfo;
        $this->numSkippedRecords = $numSkippedRecords;
        $this->numFields         = count($fieldsinfo);
        $this->uniqueConstraint  = $uniqueConstraint;

        $data = array();
        $rowNum = $numSkippedRecords;
        foreach ($datasource as $row) {
            $rowNum++;
            $temp            = new MasterTable_Record($row, $fieldsinfo, $this->uniqueConstraint, $this->validators);
            $data[$temp->id] = $temp;
            $this->rowNumbers[$temp->id] = $rowNum;
        }
        $this->data    = $data;
        $this->current = current($this->data);

        $this->limitRecords      = count($this->data);
        if ($numRecordsInTable == 0) {
            $this->numRecords = $this->limitRecords;
        } else {
            $this->numRecords = $numRecordsInTable;
        }

    }

    /**
     * Returns data as associative array
     *
     * @return array
     */
    public function getRawData()
    {
        $data = array();
        foreach ($this->data as $row) {
            $data[$row->id] = $row->getRawData();
        }
        return $data;
    }

    /**
     * get number of stored records in Dataset
     *
     * @return integer
     */
    public function count()
    {
        return $this->limitRecords;
    }

    /**
     * get number of skipped records
     *
     * @return integer
     */
    public function skipped()
    {
        return $this->numSkippedRecords;
    }

    /**
     * get total number of records in table;
     *
     * @return integer
     */
    public function total()
    {
        return $this->numRecords;
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
     * get info about specified Field
     *
     * @param string $field
     * @return Field
     */
    public function getFieldInfo($field)
    {
        return $this->fields[$field];
    }

    /**
     * get all Fields with info from DataSet
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function rewind()
    {
        if (false !== ($row = reset($this->data))) {
            $this->current = $row; //new Record($row, $fieldsinfo, $this->uniqueConstraint);
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    /**
     * return current Record
     *
     * @return MasterTable_Record
     */
    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return key($this->data);
    }

    /**
     * return next Record
     *
     * @return MasterTable_Record
     */
    public function next()
    {
        if (false !== ($row = next($this->data))) {
            $this->current = $row; //new Record($row, $fieldsinfo, $this->uniqueConstraint);
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    /**
     * Isset accessor to $this->data array.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * return prev Record
     *
     * @return MasterTable_Record
     */
    public function prev()
    {
        if (false !== ($row = prev($this->data))) {
            $this->current = $row; //new Record($row, $fieldsinfo, $this->uniqueConstraint);
        } else {
            $this->current = false;
        }
        return $this->current;
    }

    public function valid()
    {
        return ($this->current() !== false);
    }

    /**
     * return Record specified by ID
     *
     * @param string $id
     * @return MasterTable_Record
     */
    public function getRecord($id)
    {
        return $this->data[$id];
    }

    /**
     * get row number by given Record ID
     *
     * @param string $id
     * @return integer
     */
    public function getRowNumberByRecordId($id) {
        return $this->rowNumbers[$id];
    }

    /**
     * get Record ID by given row number
     *
     * @param integer $rowNumber
     * @return string
     */
    public function getRecordIdByRowNumber($rowNumber) {
        return array_search($rowNumber, $this->rowNumbers);
    }

    public function setRecord(MasterTable_Record &$record) {
        $this->data[$record->id] = $record;
        if ($record->isNew()) {
            $this->rowNumbers[$record->id] = $this->total();
            $this->numRecords ++;
        }
    }

    public function getNewRecord() {
        $nextRowNumber = $this->total();
        $datasource = array();
        foreach ($this->fields as $key => $val) {
            $datasource[$key] = '';
        }
        return new MasterTable_Record($datasource, $this->fields, $this->uniqueConstraint, $this->validators, true);
    }
    
}
