<?php
class Zend_View_Helper_HtmlSpan extends Zend_View_Helper_FormElement
{
    public function htmlSpan($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        $xhtml = '<span'
                . ' id="' . $this->view->escape($id) . '"'
                . $this->_htmlAttribs($attribs)
                . '>' . $this->view->escape($value)
                . '</span>';

        return $xhtml;
    }
}
