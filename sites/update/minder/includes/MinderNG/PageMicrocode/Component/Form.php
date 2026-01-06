<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Collection\Model;

/**
 * Class Form
 * @package MinderNG\PageMicrocode\Component
 * @property string SS_NAME
 * @property string SSF_TYPE
 * @property string SSF_NAME
 * @property string SSF_SEARCH_RESULT_NAME
 * @property boolean EXECUTE_SEARCH_ON_CLEAR
 * @property string SSF_STATUS
 */
class Form extends Model {
    const DEFAULT_FORM_NAME = 'DEFAULT';

    const EDIT_FORM = 'ER';
    const SEARCH_FORM = 'SE';
    const SEARCH_RESULT_FORM = 'SR';
    const STATUS_COMMENTED = 'CM';
    const STATUS_OK = 'OK';

    const FIELD_SCREEN_NAME = 'SS_NAME';
    const FIELD_FORM_TYPE = 'SSF_TYPE';
    const FIELD_FORM_NAME = 'SSF_NAME';
    const FIELD_STATUS = 'SSF_STATUS';
    const FIELD_SEARCH_RESULT_NAME = 'SSF_SEARCH_RESULT_NAME';

    private $_workingDataSetCollection;

    protected static $_defaults = array(
        'EXECUTE_SEARCH_ON_CLEAR' => true,
    );

    private static function _getFormType($attributes) {
        $type = isset($attributes[static::FIELD_FORM_TYPE]) ? strtoupper(trim($attributes[static::FIELD_FORM_TYPE])) : '';
        switch ($type) {
            case static::SEARCH_FORM:
                return static::SEARCH_FORM;
            case static::EDIT_FORM:
                return static::EDIT_FORM;
            case static::SEARCH_RESULT_FORM:
            default:
                return static::SEARCH_RESULT_FORM;
        }
    }

    public static function calculateId(array $attributes = array())
    {
        $attributes = static::_parse($attributes);
        $parts = array(
            $attributes[static::FIELD_SCREEN_NAME],
            $attributes[static::FIELD_FORM_TYPE],
            $attributes[static::FIELD_FORM_NAME],
            $attributes[static::FIELD_STATUS],
        );
        return implode('/', $parts);
    }

    public function parse($attributes)
    {
        $this->getWorkingDataSetCollection()->init(
            isset($attributes['workingDataSetCollection']) ? $attributes['workingDataSetCollection'] : array(),
            new AddOptions(true, true)
        );

        if (isset($attributes['workingDataSetCollection'])) {
            unset($attributes['workingDataSetCollection']);
        }
        return static::_parse($attributes);
    }

    private static function _parse($attributes) {
        $attributes[static::FIELD_FORM_NAME] = empty($attributes[static::FIELD_FORM_NAME]) ? static::DEFAULT_FORM_NAME : $attributes[static::FIELD_FORM_NAME];
        $attributes[static::FIELD_FORM_TYPE] = static::_getFormType($attributes);
        $attributes[static::FIELD_SCREEN_NAME] = isset($attributes[static::FIELD_SCREEN_NAME]) ? $attributes[static::FIELD_SCREEN_NAME] : '';
        $attributes[static::FIELD_SEARCH_RESULT_NAME] = isset($attributes[static::FIELD_SEARCH_RESULT_NAME]) ? $attributes[static::FIELD_SEARCH_RESULT_NAME] : '';
        $attributes[static::FIELD_STATUS] = empty($attributes[static::FIELD_STATUS]) ? static::STATUS_COMMENTED : strtoupper(trim($attributes[static::FIELD_STATUS]));

        return $attributes;
    }

    public function isStatusOk() {
        return $this->SSF_STATUS == static::STATUS_OK;
    }

    public function isEditResultForm() {
        return $this->SSF_TYPE === static::EDIT_FORM;
    }

    public function isSearchForm() {
        return $this->SSF_TYPE == static::SEARCH_FORM;
    }

    public function startEditDataSetRow(DataSet $dataSet, DataSetRow $dataSetRow) {
        $workingDataSet = $this->getWorkingDataSetCollection()->add(array($dataSet->getArrayCopy()), new AddOptions(false, true, true));
        $workingDataSet = array_shift($workingDataSet);
        /**
         * @var DataSet $workingDataSet
         */

        $workingDataSet->getDataSetRowCollection()->reset(array($dataSetRow->getArrayCopy(true)), new AddOptions(false, true));
    }

    public function syncFormDataSet(Form $sourceForm) {
        foreach($sourceForm->getWorkingDataSetCollection() as $dataSet) {
            foreach ($dataSet->getDataSetRowCollection() as $dataSetRow) {
                /** @var DataSetRow  $dataSetRow */
                $this->syncDataSetRow($dataSet, $dataSetRow);
            }
        }
    }

    public function syncDataSetRow(DataSet $dataSet, DataSetRow $dataSetRow) {
        $foundDataSet = $this->getWorkingDataSetCollection()->getDataSet($dataSet);

        if (empty($foundDataSet)) {
            return;
        }

        if ($dataSetRow->isNew()) {
            $foundRow = $foundDataSet->getDataSetRowCollection()->getDataSetRowByCID($dataSetRow->_CID_);
        } else {
            $rowId = $dataSetRow->hasChanged() ? $dataSetRow->previousId() : $dataSetRow->getId();
            $foundRow = $foundDataSet->getDataSetRowCollection()->getDataSetRow($rowId);
        }

        if (empty($foundRow)) {
            return;
        }

        $foundRow->set($dataSetRow->getAttributes());
    }

    /**
     * @return DataSetCollection|DataSet[]
     */
    public function getWorkingDataSetCollection()
    {
        if (empty($this->_workingDataSetCollection)) {
            $this->_workingDataSetCollection = new DataSetCollection();
        }

        return $this->_workingDataSetCollection;
    }

    public function getArrayCopy($full = false)
    {
        $result = $this->_attributes;

        if ($full) {
            $result['workingDataSetCollection'] = iterator_to_array($this->getWorkingDataSetCollection()->getArrayCopy($full));
        }

        return $result;
    }


}