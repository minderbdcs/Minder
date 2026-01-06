<?php
  
class Minder_View_Helper_JsSearchResultDataset extends Zend_View_Helper_FormElement
{
    protected $fields = array();
    
    public function jsSearchResultDataset($ssName, $rows, $selectedRows, $paginator) {

 
        return (isset($this->view->dataSource) && $this->view->dataSource == 'file') ?
            $this->renderNoDbScreenData($ssName, $rows, $selectedRows, $paginator) :
            $this->render($ssName, $rows, $selectedRows, $paginator);
    }
    
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
    
    protected function getOptionsFromSql($fieldDesc, $dataRow) {
        $options = array();
    
        list($sql, $args) = $this->bindParams($fieldDesc['SSV_DROPDOWN_SQL'], $fieldDesc['SQL_PARAMS'], $dataRow);
        
        array_unshift($args, $sql);
        
        $options = call_user_func_array(array($this->view->minder, 'fetchAllAssoc'), $args);
        
        return $options;
    }
    
    protected function getOptionsFromFun($fieldDesc, $dataRow) {
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
        if (is_array($firsOption)) {
            list($valueField, $labelField) = array_keys($firsOption);
            foreach ($options as &$optionRow) {
                $optionRow['value'] = $optionRow[$valueField];
                $optionRow['label'] = $optionRow[$labelField];
            }
            return $options;
        } else {
            $newOptionsArray = array();
            foreach ($options as $key => $value)
                $newOptionsArray[] = array('value' => $key, 'label' => $value);
            
            return $newOptionsArray;
        }
            
    }
    
    protected function getOptions($fieldName, $dataRow) {
        if (!isset($this->fields[$fieldName]))
            return array();
        
        $fieldDesc = $this->fields[$fieldName];
        if (empty($fieldDesc['SSV_DROPDOWN_SQL']))
            return array();
        
        $dataSource     = strtoupper(trim($fieldDesc['SSV_DROPDOWN_DATA_FROM']));
        if ($dataSource == 'SQL') {
            $options = $this->getOptionsFromSql($fieldDesc, $dataRow);
        } elseif ($dataSource == 'FUN') {
            $options = $this->getOptionsFromFun($fieldDesc, $dataRow);
        } else {
            throw new Minder_Exception("Unsupported SSV_DROPDOWN_DATA_FROM = '" . $fieldDesc['SSV_DROPDOWN_DATA_FROM'] . "' in Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
        }
        
        $options = $this->prepareOptionsList($options);
        
        return $options;
    }
    
    protected function prepareFieldList($ssName) {
        $screenDescription = $this->view->jsSearchResult($ssName, '');
        
        foreach ($screenDescription['fields'] as $fieldDesc)
            $this->fields[$fieldDesc['SSV_ALIAS']] = $fieldDesc;
    }
    
    /**
    * Builds JS representation of dataset
    * 
    * @param string $ssName
    * @param array  $rows
    * @param array  $selectedRows
    * @param array  $paginator
    */
    public function render($ssName, $rows, $selectedRows, $paginator) {
        $resultDataset = array();
        
        $this->prepareFieldList($ssName);
        
        foreach ($rows as $rowId => $dataRow) {
            $tmpResultRow = array();
            $tmpResultRow['row_id']   = $rowId;
            $tmpResultRow['checked'] = false;
            $tmpResultRow['values']  = array();
            
            if (isset($selectedRows[$rowId]))
                $tmpResultRow['checked'] = true;
            
            foreach ($dataRow as  $fieldName => $fieldValue) {
                $tmpResultRow['values'][$fieldName] = array('value' => $fieldValue, 'dropdown' => $this->getOptions($fieldName, $dataRow));
            }
            
            $resultDataset[] = $tmpResultRow;
        }
        
        return array('dataset' => $resultDataset, 'paginator' => $paginator);
        
    }

    public function renderNoDbScreenData($ssName, $rows, $selectedRows, $paginator) {
        $resultDataset = array();

        foreach ($rows as $rowId => $dataRow) {
            $tmpResultRow = array();
            $tmpResultRow['row_id']   = $rowId;
            $tmpResultRow['checked'] = false;
            $tmpResultRow['values']  = array();

            if (isset($selectedRows[$rowId]))
                $tmpResultRow['checked'] = true;

            foreach ($dataRow as  $fieldName => $fieldValue) {
                $tmpResultRow['values'][$fieldName] = array('value' => $fieldValue, 'dropdown' => $this->getOptions($fieldName, $dataRow));
            }

            $resultDataset[] = $tmpResultRow;
        }

        return array('dataset' => $resultDataset, 'paginator' => $paginator);
    }
}