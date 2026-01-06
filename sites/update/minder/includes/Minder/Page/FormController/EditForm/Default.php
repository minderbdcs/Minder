<?php

class Minder_Page_FormController_EditForm_Default implements Minder_Page_FormController_EditForm_Interface {
    protected $_sysScreenName = null;

    function __construct($sysScreenName)
    {
        $this->_sysScreenName = $sysScreenName;
    }


    /**
     * @return Minder_Page_SysScreenMapper_Default
     */
    protected function _getMapper() {
        return new Minder_Page_SysScreenMapper_Default($this->_sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM);
    }

    protected function _getFormFiller() {
        return new Minder_Page_FormFiller();
    }

    protected function _getFormBuilder() {
        return new Minder_Page_FormBuilder();
    }

    /**
     * @return Minder_Form
     */
    protected function _getForm() {
        return new Minder_Form($this->_getFormBuilder()->build($this->_sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM));
    }

    /**
     * @param string $recordId
     * @return Minder_Page_FormController_EditForm_FormData
     */
    function load($recordId)
    {
        $result = new Minder_Page_FormController_EditForm_FormData();

        $searchResult = $this->_getMapper()->find($recordId);
        $form         = $this->_getForm();

        if ($searchResult->count() > 0) {
            $result->modelData = $searchResult->current()->toArray();
            $form->populate($result->modelData);
        }

        foreach ($this->_getFormFiller()->getMultiOptions($form, $this->_sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM) as $elementName => $elementMultiOptions) {
            /**
             * @var Minder_Page_FormController_EditForm_FormElementData $tmpElement
             */
            $tmpElement = isset($result->elements[$elementName]) ? $result->elements[$elementName] : new Minder_Page_FormController_EditForm_FormElementData();
            $tmpElement->multiOptions = $elementMultiOptions;
            $result->elements[$elementName] = $tmpElement;
        }

        $result->multiOptions = $this->_getFormFiller()->getMultiOptions($form, $this->_sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM);

        return $result;
    }

    protected function _getFilledForm($record) {
        $editForm = $this->_getForm();
        $formFiller = $this->_getFormFiller();
        $editForm->populate($record);
        return $formFiller->fillMultiOptions($editForm, $this->_sysScreenName, Minder_Page::FORM_TYPE_EDIT_FORM);
    }

    public function update($params) {
        $formData = $params['data'];
        $editForm = $this->_getFilledForm($formData);

        if (!$editForm->isValid($formData))
            throw new Exception('Bad form data.');

        $dataMapper = $this->_getMapper();
        return $dataMapper->update($editForm->getValues())->toArray();
    }
}