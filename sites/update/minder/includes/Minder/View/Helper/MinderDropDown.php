<?php

class Minder_View_Helper_MinderDropDown extends Zend_View_Helper_FormElement {

    public function minderDropDown($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n") {

        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
        $options = empty($options) ? array(array('KEY' => '', 'VALUE' => '')) : $options;
        list($valueColumn, $labelColumn) = array_keys(current($options));
        if (isset($attribs['valueField'])) {
            $valueColumn = $attribs['valueField'];
            unset($attribs['valueField']);
        }

        if (isset($attribs['labelField'])) {
            $labelColumn = $attribs['labelField'];
            unset($attribs['labelField']);
        }

        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }

        $xhtml = '<select'
                . ' name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs)
                . ">\n    ";

        $list = array();
        foreach ($options as $optionsRow) {
            $list[] = $this->_build($optionsRow, $value, $valueColumn, $labelColumn);
        }

        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";

        return $xhtml;
    }

    protected function _build($optionsRow, $val, $valueColumn, $labelColumn) {
        //use first fields as value => label pair
        $value = isset($optionsRow[$valueColumn]) ? $optionsRow[$valueColumn] : '';
        $label = isset($optionsRow[$labelColumn]) ? $optionsRow[$labelColumn] : '';

        $label = $this->view->escape($label);

        $xhtml = '<option'
               . ' value="' . $this->view->escape($value) . '"'
               . ' label="' . $label . '"';

        if ($value == $val) {
            $xhtml .= ' selected="selected"';
        }

        $xhtml .= ' ' . $this->_htmlAttribs($optionsRow) . '>' . $label . '</option>';

        return $xhtml;
    }
}