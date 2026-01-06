<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class FieldCollection extends Collection {

    /**
     * @param $idOrAggregate
     * @return Field|null
     */
    public function getField($idOrAggregate) {
        return $this->get($idOrAggregate);
    }

    /**
     * @param DataSet $dataSet
     * @return \Iterator|Field[]
     */
    public function filterDataSetSearchFields(DataSet $dataSet) {
        return $this->where(array(
            'SS_NAME' => $dataSet->SS_NAME,
            'SS_VARIANCE' => $dataSet->SS_VARIANCE,
            'SSV_FIELD_TYPE' => 'SE',
            'SSV_INPUT_METHOD' => function($inputMethod) {return !in_array($inputMethod, array('RO', 'NONE'));},
            'SSV_FIELD_STATUS' => Field::STATUS_OK,
        ));
    }

    /**
     * @param DataSet $dataSet
     * @return \Iterator|Field[]
     */
    public function filterDataSetStaticConditionFields(DataSet $dataSet) {
        return $this->where(array(
            'SS_NAME' => $dataSet->SS_NAME,
            'SS_VARIANCE' => $dataSet->SS_VARIANCE,
            'SSV_FIELD_TYPE' => 'SE',
            'SSV_INPUT_METHOD' => function($inputMethod) {return in_array($inputMethod, array('RO', 'NONE'));},
            'SSV_FIELD_STATUS' => Field::STATUS_OK,
        ));
    }

    /**
     * @return \Iterator|Field[]
     */
    public function filterDataSourceFields() {
        return $this->where(array(
            'SSV_FIELD_TYPE' => function($fieldType) {return in_array($fieldType, array('SR', 'ER'));},
        ));
    }

    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Field';
    }

    /**
     * @param Form $form
     * @param DataSet $dataSet
     * @return \Iterator|Field[]
     */
    public function getFormDataSetFields(Form $form, DataSet $dataSet) {
        return $this->where(array(
            Field::FIELD_STATUS => Field::STATUS_OK,
            Field::FIELD_TYPE => $form->SSF_TYPE,
            Field::FIELD_SCREEN_NAME => $form->SS_NAME,
            Field::FIELD_SCREEN_NAME => $dataSet->SS_NAME,
            Field::FIELD_VARIANCE => $dataSet->SS_VARIANCE,
            Field::FIELD_FORM_NAME => function($formName) use($form) {return (!$formName) || $formName == $form->SSF_NAME;},
        ));
    }

    public function getFormDataSetFieldDefaults(Form $form, DataSet $dataSet) {
        $result = array();

        foreach ($this->getFormDataSetFields($form, $dataSet) as $field) {
            $result[$field->SSV_ALIAS] = $field->DEFAULT; //todo: implement default value retrieval
        }

        return $result;
    }
}