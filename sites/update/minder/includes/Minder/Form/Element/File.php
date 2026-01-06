<?php

class Minder_Form_Element_File extends Zend_Form_Element_File {
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('File')
                 ->addDecorator('HtmlTag', array('tag' => 'td'))
                 ->addDecorator('Label', array('tag' => 'td'));
        }
    }

}