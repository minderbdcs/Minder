<?php
class Minder_Form_DisplayGroup_FormElementRow extends Zend_Form_DisplayGroup {
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'tr'))
                 ->addDecorator(new Minder_Form_Decorator_HtmlTagTable());
        }
    }

}