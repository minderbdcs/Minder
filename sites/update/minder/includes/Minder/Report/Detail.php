<?php

require_once(__DIR__."/../../functions.php");

/**
 * @property int reportId;
 * @property int reportDetailId;
 * @property int sequence;
 * @property string queryField;
 * @property string queryPrompt;
 * @property string queryDbField;
 * @property char[1] queryPromptType;
 * @property mixed queryFieldValue;
 */
class Minder_Report_Detail {
    /**
     * @var int
     */
    protected $_reportId;
    /**
     * @var int
     */
    protected $_reportDetailId;
    /**
     * @var int
     */
    protected $_sequence;
    /**
     * @var string
     */
    protected $_queryField;
    /**
     * @var string
     */
    protected $_queryPrompt;
    /**
     * @var string
     */
    protected $_queryDbField;
    /**
     * @var char(1)
     */
    protected $_queryPromptType;

    /**
     * not REPORT_DETAIL member, used to generate reports
     * @var mixed
     */
    protected $_queryFieldValue;

    /**
     * @var string
     */
    protected $_queryCopyField;

    public function __get($name) {
        $name = '_' . $name;
        return $this->$name;
    }

    public function __set($name, $value) {
        $fieldName = '_' . $name;
        switch ($name) {
            case 'reportId':
            case 'reportDetailId':
            case 'sequence':
                $this->$fieldName = intval($value);
                break;
            case 'queryField':
            case 'queryPrompt':
            case 'queryDbField':
            case 'queryCopyField':
                $this->$fieldName = strval($value);
                break;
            case 'queryPromptType':
                $tmpValue = strval($value);
                $this->$fieldName = (strlen($tmpValue) > 0) ? strtoupper($tmpValue[0]) : '';
                break;
            case 'queryFieldValue':
                $this->_queryFieldValue = $value;
                break;
            default:
                throw new Minder_Report_Detail_Exception('Unknown property: ' . $name);
        }
    }

    /**
     * @return int
     */
    public function getDetailId() {
        return $this->__get('reportDetailId');
    }

    /**
     * @param  array $tableRow
     * @return Minder_Report_Detail
     */
    public function setValuesFromTableRow($tableRow) {
        foreach ($tableRow as $fieldName => $fieldValue) {
            $this->__set(transformToObjectProp($fieldName), $fieldValue);
        }

        return $this;
    }

    /**
     * @param array $tableRow
     */
    public function __construct($tableRow = array()) {
        $this->setValuesFromTableRow($tableRow);
    }
}
