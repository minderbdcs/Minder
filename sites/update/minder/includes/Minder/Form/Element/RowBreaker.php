<?php

class Minder_Form_Element_RowBreaker extends Zend_Form_Element {
    public function render(Zend_View_Interface $view = null)
    {
        return '</tr><tr>';
    }


}
