<?php
class Zend_View_Helper_HtmlA extends Zend_View_Helper_FormElement
{
    public function htmlA($name, $value = null, $attribs = null, $href = '')
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        $xhtml = '<a href="' . $href . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . '>' . $this->view->escape($value)
                . '</a>';

        return $xhtml;
    }
}
