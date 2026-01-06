<?php

class Minder_View_Helper_SysScreenFormDatePicker extends Zend_View_Helper_FormText
{
    public function sysScreenFormDatePicker($attribs = array(), $fieldDesc, $actions) {
        
        if (empty($fieldDesc['SSV_TABLE']))
            $name  = $fieldDesc['SSV_ALIAS'];
        else 
            $name  = $fieldDesc['SSV_TABLE'] . '-' . $fieldDesc['SSV_ALIAS'];
        
        $value = '';
        
        if (isset($attribs['search_element'])) {
            $name  = 'SEARCH-' . $name;
            if (isset($fieldDesc['SEARCH_VALUE']))
                $value = $fieldDesc['SEARCH_VALUE'];
        } else {
            if (isset($fieldDesc['ENTERED_VALUE'])) {
                $value = $fieldDesc['ENTERED_VALUE'];
            } elseif (isset($fieldDesc['DEFAULT_VALUE'])) {
                $value = $fieldDesc['DEFAULT_VALUE'];
            }
        }
        
        $id = $name;
        if (isset($attribs['id'])) {
            $id = $attribs['id'];
            unset($attribs['id']);
        }
        
//        $actionSourceCode = '';
        $tmpAttribs                   = $attribs;
        $tmpAttribs['original_value'] = $value;
        
        if (!empty($fieldDesc['SSV_ACTION'])) {
        
            foreach ($actions as $actionDesc) {
                if ($fieldDesc['SSV_ACTION'] == $actionDesc['SSV_NAME']) {
                    
//                    $actionSourceCode                           = $actionDesc['SSA_ACTION'];
                    $tmpAttribs[$actionDesc['SSA_ACTION_TYPE']] = $actionDesc['SSA_FUNCTION'];
//                    break;
                }
            }
        }
        

        $xhtml  = $this->formText($name, $value, $tmpAttribs);
        $xhtml .= '
            <script type="text/javascript">
                $("#' . $id . '").datepicker({dateFormat: "yy-mm-dd"});
            </script>
        ';
        
        return $xhtml;
    }

}