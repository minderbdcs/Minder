<?php
class Minder_Form_DisplayGroup_Toolbar extends Zend_Form_DisplayGroup {
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'ul', 'class' => 'toolbar'));
        }
    }

}