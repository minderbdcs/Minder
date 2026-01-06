<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Collection\Model;


/**
 * Class DataSet
 * @package MinderNG\PageMicrocode\Component
 *
 * @property boolean DATA_LOADED
 * @property string SS_NAME
 * @property string SS_VARIANCE
 * @property string FIELD
 * @property string LIMIT
 */
class DataSet extends Model {

    private $_dataSetRowCollection;

    public function isMain() {
        return empty($this->FIELD) && empty($this->ROW);
    }

    public function fieldDataSet() {
        return !empty($this->FIELD);
    }

    public function dependsOnRowData() {
        return !empty($this->ROW);
    }

    /**
     * @param TableCollection $tableCollection
     * @return \Iterator|Table[]
     */
    public function filterDataSetTables(TableCollection $tableCollection) {
        return $tableCollection->where(array(
            'SS_NAME' => $this->SS_NAME,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SST_TABLE_STATUS' => 'OK'
        ));
    }

    public function filterDataSetFields(DataSourceFieldCollection $fieldCollection) {
        return $fieldCollection->where(array(
            'SS_NAME' => $this->SS_NAME,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SSV_FIELD_STATUS' => 'OK',
        ));
    }

    /**
     * todo: move to DataSourceFieldCollection
     *
     * @param DataSourceFieldCollection $fieldCollection
     * @return \Iterator|DataSourceField[]
     */
    public function filterPrimaryKeys(DataSourceFieldCollection $fieldCollection) {
        return $fieldCollection->where(array(
            'SS_NAME' => $this->SS_NAME,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SSV_FIELD_STATUS' => 'OK',
            'SSV_PRIMARY_ID' => 'T',
        ));
    }

    /**
     * @param TransactionCollection $transactions
     * @return \Iterator|Transaction[]
     */
    public function filterUpdateTransactions(TransactionCollection $transactions) {
        return $transactions->where(array(
            'SS_NAME' => $this->SS_NAME,
            'SS_VARIANCE' => $this->SS_VARIANCE,
            'SST_ACTION' => 'UPDATE',
        ));
    }

    protected static $_defaults = array(
        'DATA_LOADED' => false,
        'LIMIT' => 1000,
    );

    public static function calculateId(array $attributes = array())
    {
        return implode('/', array(
            isset($attributes['SS_NAME']) ? $attributes['SS_NAME'] : '',
            isset($attributes['SS_VARIANCE']) ? $attributes['SS_VARIANCE'] : '',
            isset($attributes['FIELD']) ? $attributes['FIELD'] : '',
            isset($attributes['ROW']) ? $attributes['ROW'] : '',
        ));
    }

    public function parse($attributes)
    {
        if (isset($attributes['rows'])) {
            $this->getDataSetRowCollection()->reset($attributes['rows'], new AddOptions(false, true));
            unset($attributes['rows']);
        }

        if (empty($attributes['SS_VARIANCE'])) {
            $attributes['SS_VARIANCE'] = isset($attributes['SS_NAME']) ? $attributes['SS_NAME'] : '';
        }

        return $attributes;
    }

    /**
     * @param array $rowData
     * @param null $collection
     * @param bool|true $parse
     * @param bool|true $silent
     * @return DataSetRow
     */
    public function newRow(array $rowData = array(), $collection = null, $parse = true, $silent = true) {
        //todo: new row handling


        return $this->getDataSetRowCollection()->newModelInstance($rowData, $collection, $parse, $silent);
    }

    /**
     * @return DataSetRow|null
     */
    public function firstRow() {
        return $this->getDataSetRowCollection()->first();
    }

    /**
     * @return DataSetRowCollection|DataSetRow[]
     */
    public function getDataSetRowCollection()
    {
        if (empty($this->_dataSetRowCollection)) {
            $this->_dataSetRowCollection = new DataSetRowCollection();
            $this->_dataSetRowCollection->init();
        }

        return $this->_dataSetRowCollection;
    }

    public function getArrayCopy($full = false)
    {
        $result = $this->_attributes;

        if ($full) {
            $result['rows'] = iterator_to_array($this->getDataSetRowCollection()->getArrayCopy($full));
        }

        return $result;
    }


}