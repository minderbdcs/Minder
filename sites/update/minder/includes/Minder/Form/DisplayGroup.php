<?php

class Minder_Form_DisplayGroup extends Zend_Form_DisplayGroup {
    /**
     * @param Zend_Form_Element|Minder_Form_DisplayGroup $element
     * @return Minder_Form_DisplayGroup
     */
    public function addElement($element)
    {
        if (!$element instanceof Zend_Form_Element && !$element instanceof Minder_Form_DisplayGroup) {
            require_once 'Minder/Form/Exception.php';
            throw new Minder_Form_Exception('Element must be Zend_Form_Elements or Minder_Form_DisplayGroup');
        }

        $this->_elements[$element->getName()] = $element;
        $this->_groupUpdated = true;
        return $this;
    }

    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            if (!$element instanceof Zend_Form_Element && !$element instanceof Minder_Form_DisplayGroup) {
                require_once 'Minder/Form/Exception.php';
                throw new Minder_Form_Exception('elements passed via array to addElements() must be Zend_Form_Elements or Minder_Form_DisplayGroup');
            }
            $this->addElement($element);
        }
        return $this;
    }
}