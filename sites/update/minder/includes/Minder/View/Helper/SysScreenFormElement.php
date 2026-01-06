<?php
class Minder_View_Helper_SysScreenFormElement extends Zend_View_Helper_FormElement
{
    public function sysScreenFormElement($attribs = array(), $fieldDesc, $actions) {

        $isNew = false;
        if (isset($attribs['is_new'])) {
            $attribs['is_new'] = 'true';
            $isNew             = true;
        } else {
            $attribs['is_new'] = 'false';
        }
        
        if ($isNew) {
            $tmpInputMethod = explode('|', $fieldDesc['SSV_INPUT_METHOD_NEW']);
        } else {
            $tmpInputMethod = explode('|', $fieldDesc['SSV_INPUT_METHOD']);
        }
        if ($tmpInputMethod[0] == 'dl')
            return '';
        
        switch ($tmpInputMethod[0]) {
            case 'RO':
            case '':
                //empty string will asume as READONLY
                return $this->view->sysScreenFormLabel($attribs, $fieldDesc, $actions);
            case 'IN':
                return $this->view->sysScreenFormText($attribs, $fieldDesc, $actions);
            case 'DP':
                return $this->view->sysScreenFormDatePicker($attribs, $fieldDesc, $actions);
                
            case 'DR':
                $fieldDesc['READ-ONLY'] = true;
            case 'DD':
                return $this->view->sysScreenFormDropDown($attribs, $fieldDesc, $actions);
                
            case 'DL':
                return $this->view->sysScreenFormDL($attribs, $fieldDesc);
                
            default:
                if ($isNew) {
                    throw new Minder_Exception("Unsupported SSV_INPUT_METHOD_NEW = '" . $fieldDesc['SSV_INPUT_METHOD_NEW'] . "' for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
                } else {
                    throw new Minder_Exception("Unsupported SSV_INPUT_METHOD = '" . $fieldDesc['SSV_INPUT_METHOD'] . "' for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
                }
        }
        
        return '';
    }
}