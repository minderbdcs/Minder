<?php
  
class Minder_View_Helper_SysScreenFormDropDown extends Zend_View_Helper_FormSelect
{
    protected function bindParams($sql, $params, $data) {
        
        $replaceArr = array_map(create_function('$item', 'return $item["PARAM"];'), $params);
        $aliases    = array_map(create_function('$item', 'return $item["ALIAS"];'), $params);
        
        $args = array();
        foreach ($aliases as $alias) {
            $args[] = (isset($data[$alias])) ? $data[$alias] : '';
        }
        
        $sql = str_replace($replaceArr, '?', $sql);
        
        return array($sql, $args);
    }
    
    protected function getOptionsFromSql($fieldDesc, $data) {
        $options = array();
    
        list($sql, $args) = $this->bindParams($fieldDesc['SSV_DROPDOWN_SQL'], $fieldDesc['SQL_PARAMS'], $data);
        
        array_unshift($args, $sql);
        
        $options = call_user_func_array(array($this->view->minder, 'fetchAllAssoc'), $args);
        
        return $options;
    }
    
    protected function getOptionsFromFun($fieldDesc, $data) {
        $options = array();
        
        $dataExpression = $fieldDesc['SSV_DROPDOWN_SQL'];
            
        $methodName = substr($dataExpression, 0, strpos($dataExpression, '('));
        eval('$args = array' . strstr($dataExpression, '(') . ';');

        if (is_callable(array($this->view->minder, $methodName))) {
            $options = call_user_func_array(array($this->view->minder, $methodName), $args);
        } else {
            throw new Minder_Exception("Cannot call '" . $dataExpression . "' needed for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
        }
        
        return $options;
    }
    
    protected function prepareOptionsList($options = array()) {
        if (empty($options))
            return $options;
        
        $firsOption = current($options);
        if (is_array($firsOption))
            return $options;
            
        $newOptionsArray = array();
        foreach ($options as $key => $value)
            $newOptionsArray[] = array('value' => $key, 'label' => $value);
            
        return $newOptionsArray;
    }
    
    protected function addEmptyOption($options = array()) {
        if (empty($options))
            $options = array(array('VALUE' => '', 'LABEL' => ''));
        else {
            list($valueColumn, $labelColumn) = array_keys(current($options));
            $options = array_merge(array(array($valueColumn => '', $labelColumn => '')), $options);
        }
        
        return $options;
    }
    
    protected function getOptions($fieldDesc, $data) {
        $dataSource     = strtoupper(trim($fieldDesc['SSV_DROPDOWN_DATA_FROM']));
        
        if (trim($fieldDesc['SSV_DROPDOWN_SQL']) == '') 
            throw new Minder_Exception('SYS_SCREEN_VAR #' . $fieldDesc['RECORD_ID'] . ' has empty SSV_DROPDOWN_SQL.');
        
        if ($dataSource == 'SQL') {
            $options = $this->getOptionsFromSql($fieldDesc, $data);
        } elseif ($dataSource == 'FUN') {
            $options = $this->getOptionsFromFun($fieldDesc, $data);
        } else {
            throw new Minder_Exception("Unsupported SSV_DROPDOWN_DATA_FROM = '" . $fieldDesc['SSV_DROPDOWN_DATA_FROM'] . "' in Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
        }
        
        $options = $this->prepareOptionsList($options);
        
        return $options;
    }
    
    protected function getValue($fieldDesc, $options, $attribs) {
        $value = '';
        
        if (isset($attribs['search_element'])) {
            if (isset($fieldDesc['SEARCH_VALUE']))
                $value = $fieldDesc['SEARCH_VALUE'];
        } else {
            if (isset($fieldDesc['ENTERED_VALUE'])) {
                $value = $fieldDesc['ENTERED_VALUE'];
            } elseif (isset($fieldDesc['DEFAULT_VALUE'])) {
                $value = $fieldDesc['DEFAULT_VALUE'];
            }
        }
        
        $defaultValue = (isset($fieldDesc['DEFAULT_VALUE'])) ? $fieldDesc['DEFAULT_VALUE'] : $value;
        
        if (empty($options))
            return $defaultValue;
        
        list($valueCollumn) = array_keys(current($options));
        foreach ($options as $optionRow) {
            if ($optionRow[$valueCollumn] == $value) 
                return $value;
        }
        
        return $defaultValue;
    }
    
    public function sysScreenFormDropDown($attribs = array(), $fieldDesc, $actions) {
        
        if (empty($fieldDesc['SSV_TABLE']))
            $name  = $fieldDesc['SSV_ALIAS'];
        else 
            $name  = $fieldDesc['SSV_TABLE'] . '-' . $fieldDesc['SSV_ALIAS'];
        $value = '';
        
        if (isset($attribs['search_element'])) {
            $name  = 'SEARCH-' . $name;
        }
        
        $rowData = array();
        if (isset($attribs['row-data'])) {
            $rowData = $attribs['row-data'];
            unset($attribs['row-data']);
        }
        $options = $this->getOptions($fieldDesc, $rowData);
        $value   = $this->getValue($fieldDesc, $options, $attribs);

        if (!isset($fieldDesc['READ-ONLY']))
            $options = $this->addEmptyOption($options);

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
        

        $xhtml  = $this->view->formFilteredDD($name, $value, $tmpAttribs, $options);

        $info = $this->_getInfo($name, $value, $attribs, $tmpAttribs);
        extract($info); // name, id, value, attribs, options, listsep, disable
        $xhtml .= "
            <script type=\"text/javascript\">
                \$(document).ready(function () { 
                    var onInitMethod = \$('#" . $this->view->escape($id) . "').attr('oninit');
                    
                    if (onInitMethod != '')
                        eval(onInitMethod);
                });
            </script>
        ";
        
//        if (!empty($actionSourceCode)) {
//            $xhtml .= '
//                <script type="text/javascript">
//                    ' . $actionSourceCode . '
//                </script>
//            ';
//        }
        
        return $xhtml;
    }
    
}
