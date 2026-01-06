<?php

class Minder_Form_Decorator_MinderEditForm extends Zend_Form_Decorator_Abstract {

    /**
     * @var null|Minder2_View_Helper_AutoloadScript
     */
    protected $_autoLoadScript = null;

    /**
     * @return Minder2_View_Helper_AutoloadScript
     */
    protected function _getAutoLoadScriptHelper() {
        if (is_null($this->_autoLoadScript))
            $this->_autoLoadScript = $this->getElement()->getView()->autoloadScript();

        return $this->_autoLoadScript;
    }

    protected function _toJson() {
        $result = new Minder_Page_FormController_EditForm_FormData();

        foreach ($this->getElement()->getElements() as $formElement) {

            if ($formElement instanceof Zend_Form_Element_Multi || $formElement instanceof Minder_Form_Element_DropDown) {
                /**
                 * @var Zend_Form_Element_Multi $formElement
                 */
                $tmpElement = new Minder_Page_FormController_EditForm_FormElementData();
                $tmpElement->multiOptions = $formElement->getMultiOptions();
                $result->elements[$formElement->getName()] = $tmpElement;
            }
        }

        return json_encode($result);
    }

    public function render($content)
    {

        $view = $this->getElement()->getView();

        if (!$view instanceof Zend_View)
            return $content;

        $this->_getAutoLoadScriptHelper()->appendScript("
            Minder2.Builder.getEditForm('" . $this->getElement()->getName() . "').fill(" . $this->_toJson() . ");
        ");

        return $content;
    }

}