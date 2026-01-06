<?php
  
class Minder_View_Helper_FormFilteredDD extends Zend_View_Helper_FormSelect
{
    public function formFilteredDD($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n") {
        return $this->render($name, $value, $attribs, $options, $listsep);
    }
    
    public function render($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n") {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
        
        $options = empty($options) ? array(array('KEY' => '', 'VALUE' => '')) : $options;
        list($valueColumn, $labelColumn) = array_keys(current($options));
        if (isset($attribs['value_column'])) {
            $valueColumn = $attribs['value_column'];
            unset($attribs['value_column']);
        }
        
        if (isset($attribs['label_column'])) {
            $labelColumn = $attribs['label_column'];
            unset($attribs['label_column']);
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
//        $xhtml .= '<script type="text/javascript">$("#' . $this->view->escape($id) . '").minderFilteredDD();</script>';
                    
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