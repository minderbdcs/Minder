<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

/**
 * Class FormCollection
 * @package MinderNG\PageMicrocode\Component
 *
 * @method Form get($idOrAggregate)
 */
class FormCollection extends Collection {

    public function findScreenEditForm(Screen $screen, $formName) {
        return $this->findForm(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::EDIT_FORM,
            Form::FIELD_FORM_NAME => $formName
        ));
    }

    /**
     * @param string|array|Form $idOrAggregate
     * @return Form
     * @throws Exception\FormNotFound
     */
    public function findForm($idOrAggregate) {
        $form = ($idOrAggregate instanceof Form) ? $idOrAggregate : $this->newForm($idOrAggregate);

        $foundForm = $this->get($form);

        if (empty($foundForm)) {
            throw new Exception\FormNotFound($form);
        }

        return $foundForm;
    }

    /**
     * @param DataSet $dataSet
     * @return Form|null
     */
    public function getDataSetSearchForm(DataSet $dataSet) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $dataSet->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::SEARCH_FORM,
        ));
    }

    /**
     * @param $formData
     * @return Form
     */
    public function newForm($formData) {
        //todo: split Id to formData array
        return $this->newModelInstance($formData);
    }

    /**
     * @param Screen $screen
     * @param string $formName
     * @return Form|null
     */
    public function getScreenForm(Screen $screen, $formName) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_NAME => $formName,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    /**
     * @param Screen $screen
     * @return Form|null
     */
    public function getScreenSearchForm(Screen $screen) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::SEARCH_FORM,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    /**
     * @param Screen $screen
     * @return Form|null
     */
    public function getScreenSearchResultForm(Screen $screen) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::SEARCH_RESULT_FORM,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    public function getScreenDefaultEditForm(Screen $screen) {
        return $this->getScreenEditForm($screen, Form::DEFAULT_FORM_NAME);
    }

    /**
     * @param Screen $screen
     * @param null $name
     * @return Form|null
     */
    public function getScreenEditForm(Screen $screen, $name) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::EDIT_FORM,
            Form::FIELD_FORM_NAME => $name,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    /**
     * @param Screen $screen
     * @return Form|null
     */
    public function getScreenAnyEditForm(Screen $screen) {
        return $this->findWhere(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::EDIT_FORM,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    /**
     * @param Screen $screen
     * @return Form[]
     */
    public function getAllScreenEditForms(Screen $screen) {
        return $this->where(array(
            Form::FIELD_SCREEN_NAME => $screen->SS_NAME,
            Form::FIELD_FORM_TYPE => Form::EDIT_FORM,
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }

    /**
     * @param Screen $screen
     * @return Form[]
     */
    public function getPageForms() {
        return $this->where(array(
            Form::FIELD_STATUS => Form::STATUS_OK,
        ));
    }


    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Form';
    }
}