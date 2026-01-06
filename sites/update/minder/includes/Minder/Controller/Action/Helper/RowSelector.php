<?php

/**
 * Minder_Controller_Action_Helper_RowSelector
 *
 * Provide common functionality to select and get selected rows.
 *
 * To reduce data stored in session, when user select huge amount of rows using select_compleate check box
 * will use two different arrays: one for selected items, other for unselected.
 * This shoud work in such manner:
 * - user select one or several rows on page - rows Ids saved in selected section;
 * - user select all rows on all pages - use virtual element in selected section with key '__all';
 * - user select all rows, but then unchecked some of them manually - store such rows Ids in unselected section.
 * 
 * So to find what rows user selected:
 * - search in selected section for '__all' key, if it is - select all rows from dataset except that is in unselected section;
 * - if now '__all' key in selected section, then select only those rows which is in selected section.
 * 
 * @copyright 2010 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
 
class Minder_Controller_Action_Helper_RowSelector extends Zend_Controller_Action_Helper_Abstract 
{
    static protected $validSelectionModes  = array('all', 'one');
    static protected $defaultSelectionMode = 'all';
    
    protected $session   = null;
    /**
     * @var Minder_SysScreen_Model
     */
    protected $dataModel = null;
    
    public function init() {
        if (!Zend_Registry::isRegistered('selectorSession')) {
            Zend_Registry::set('selectorSession', new Zend_Session_Namespace('selector'));
        }
        
        $this->session = Zend_Registry::get('selectorSession');
    }
    
    /**
    * Set data model to use
    * 
    * @param Minder_SysScreen_Model $model
    * 
    * @returns Minder_Controller_Action_Helper_RowSelector $this
    */
    protected function setModel($model) {
        
        $implements = class_implements($model);   
        if (!isset($implements['Minder_SysScreen_Model_Interface'])) {
            throw new Minder_Controller_Action_Helper_RowSelector_Exception('Unsupported model type.');
        }
        
        $this->dataModel = $model;
        
        return $this;
    }
    
    /**
    * Allow to set default selection mode for specific namespace.
    * There are such selecteion modes in priority of order (from lower to higher)
    * - harcoded Minder_Controller_Action_Helper_RowSelector::$defaultSelectionMode
    * - database defained OPTIONS.SCN_RADIOB
    * - defined with setDefaultSelectionMode
    * - defined with setSelectionMode
    * 
    * @param mixed $mode
    * @param mixed $nameSpace
    * @param string $actionName
    * @param string $controllerName
    */
    public function setDefaultSelectionMode($mode, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        
        $selection['default_mode'] = (in_array($mode, self::$validSelectionModes)) ? $mode : $this->getDefaultSelectionMode($nameSpace = 'default', $actionName = null, $controllerName = null);
        
        $this->session->selection[$controllerName][$actionName][$nameSpace] = $selection;
        return $this;
    }
    
    /**
    * Returs default selection mode for specific namespace.
    * There are such default selecteion modes in priority of order (from lower to higher)
    * - harcoded Minder_Controller_Action_Helper_RowSelector::$defaultSelectionMode
    * - database defained OPTIONS.SCN_RADIOB
    * - defined with setDefaultSelectionMode
    * 
    * @param mixed $nameSpace
    * @param string $actionName
    * @param string $controllerName
    */
    public function getDefaultSelectionMode($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];

        $optionRecord              = Minder_SysScreen_Legacy_OptionManager::getScnRadioButton();
        $tmpDbDefaultSelectionMode = (empty($optionRecord)) ? self::$defaultSelectionMode : 'one';
        
        return (isset($selection['default_mode'])) ? $selection['default_mode'] : $tmpDbDefaultSelectionMode;
    }
    
    public function setSelectionMode($mode, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        
        $mode                      = (in_array($mode, self::$validSelectionModes)) ? $mode : $this->getDefaultSelectionMode($nameSpace, $actionName, $controllerName);
        $selection['mode']         = $mode;
        
        $this->session->selection[$controllerName][$actionName][$nameSpace] = $selection;
        return $this;
    }
    
    public function getSelectionMode($defaultMode, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $defaultMode    = (in_array($defaultMode, self::$validSelectionModes)) ? $defaultMode : $this->getDefaultSelectionMode($nameSpace, $actionName, $controllerName);

        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection   = $this->session->selection[$controllerName][$actionName][$nameSpace];
        $defaultMode = (in_array($defaultMode, self::$validSelectionModes)) ? $defaultMode : $this->getDefaultSelectionMode($nameSpace, $actionName, $controllerName);
        $mode        = isset($selection['mode']) ? $selection['mode'] : $defaultMode;
        
        return (in_array($mode, self::$validSelectionModes)) ? $mode : $defaultMode;
    }

    /**
     * @param $model
     * @param string $nameSpace
     * @param null $actionName
     * @param null $controllerName
     * @return Minder_Controller_Action_Helper_RowSelector
     */
    public function setSelectionModel($model, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        return $this->setRowSelection('select_complete', 'init', null, null, $model, true, $nameSpace, $actionName, $controllerName);
    }
    
    /**
    * Provide mechanism to select/un
    * 
    * @param mixed $rowId
    * @param mixed $state
    * @param integer $offset      - zero based page number (first page - 0, second page - 1, and so on ...)
    * @param integer $rowsPerPage - number of rows per page
    * @param mixed $nameSpace
    * @param string $actionName
    * @param string $controllerName
    * 
    * @returns Minder_Controller_Action_Helper_RowSelector  $this
    */
    public function setRowSelection($rowId = 'select_complete', $state = 'true', $pageNo = null, $rowsPerPage = null, $dataModel = null, $keepModel = false, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $pageNo = ($pageNo) ? $pageNo : 0;
        $offset = $pageNo * $rowsPerPage;
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        
        if ($keepModel === true) {
            //save and use given model
            $this->setModel(clone $dataModel);
            $selection['data_model'] = $this->dataModel;
        } else {
            if (is_null($dataModel)) {
                //if no model given try to use saved model
                $this->setModel($selection['data_model']);
            } else {
                //try to use given model
                $this->setModel($dataModel);
            }
        }
        
        $mode = $this->getSelectionMode('', $nameSpace, $actionName, $controllerName);
        
        if ($state != 'init' && $mode == 'one') {
            //only one row at a time can be selected, so clear prevoise selection
            $selection['selected']          = array();
            $selection['unselected']        = array();
        }
        
        $tmpRowId = strtolower($rowId);
        
        if ($tmpRowId == 'select_complete') {
            if ($state === 'true' && $mode != 'one') {
                //mark all rows as selected
                $selection['selected']['__all'] = $rowId;
                $selection['unselected']        = array();
            } elseif ($state === 'false') {
                //mark all rows as unselected, so cleanup both arrays
                $selection['selected']          = array();
                $selection['unselected']        = array();
            }
        } elseif ($tmpRowId == 'select_all' && $mode != 'one') {
            $dataRows = $this->dataModel->getItems($offset, $rowsPerPage, false);
            if ($state === 'true') {
                //should mark all rows on page as selected
                foreach ($dataRows as $tmpRowId => $rowData) {
                    $selection['selected'][$tmpRowId] = $tmpRowId;
                    if (isset($selection['unselected'][$tmpRowId]))
                        //if row was unselected, remove it from unselected section
                        unset($selection['unselected'][$tmpRowId]);
                }
            } elseif ($state === 'false') {
                //should mark all rows on page as unselected
                foreach ($dataRows as $tmpRowId => $rowData) {
                    $selection['unselected'][$tmpRowId] = $tmpRowId;
                    if (isset($selection['selected'][$tmpRowId]))
                        //if row was selected, remove it from selected section
                        unset($selection['selected'][$tmpRowId]);
                }
            }
        } else {
            if ($state === 'true') {
                $selection['selected'][$rowId] = $rowId;
                if (isset($selection['unselected'][$rowId]))
                    //if row was unselected, remove it from unselected section
                    unset($selection['unselected'][$rowId]);
            } elseif ($state === 'false') {
                $selection['unselected'][$rowId] = $rowId;
                if (isset($selection['selected'][$rowId]))
                    //if row was selected, remove it from selected section
                    unset($selection['selected'][$rowId]);
            }
        }
        $this->session->selection[$controllerName][$actionName][$nameSpace] = $selection;
        return $this;
    }


    /**
     * Returns used data model,
     *
     * @param string $nameSpace
     * @param null|string $actionName
     * @param null|string $controllerName
     * @return null|Minder_SysScreen_Model
     *
     */
    public function getModel($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);

        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];

        if (is_object($selection['data_model']))
            return clone $selection['data_model'];
        else 
            return null;
    }
    
    public function getSelectedCount($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        $this->setModel($selection['data_model']);
        
        $implements = class_implements($this->dataModel);
        if (!isset($implements['Countable']))
            throw new Minder_Controller_Action_Helper_RowSelector_Exception("Model should provide Countable interface.");
            
        if (count($selection['selected']) == 0)
            return 0;

        $tmpModelConditions = $this->dataModel->getConditions();
        $conditions         = $this->getSelectConditions($nameSpace, $actionName, $controllerName);
        $this->dataModel->addConditions($conditions);
        
        $count              = count($this->dataModel);
        $this->dataModel->setConditions($tmpModelConditions);
        return $count;
    }

    public function hasSelected($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();

        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);

        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        $this->setModel($selection['data_model']);

        if (count($selection['selected']) == 0)
            return false;

        $tmpModelConditions = $this->dataModel->getConditions();
        $conditions         = $this->getSelectConditions($nameSpace, $actionName, $controllerName);
        $this->dataModel->addConditions($conditions);

        $result              = $this->dataModel->hasRecords();
        $this->dataModel->setConditions($tmpModelConditions);
        return $result;
    }

    // new selection method
    public function getSelectedRows($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $selectedCount = $this->getSelectedCount($nameSpace, $actionName, $controllerName);

        if ($selectedCount == 0)
            return array();

        $selection = $this->_getSelectionLegacy($nameSpace, $actionName, $controllerName);
        $this->setModel($selection['data_model']);
        $tmpConditions = $this->dataModel->getConditions();

        $selectedRowsConditions = $this->getSelectConditions($nameSpace, $actionName, $controllerName);
        $this->dataModel->addConditions($selectedRowsConditions);
        $selectedRows = $this->dataModel->getItems(0, $selectedCount, false);

        $this->dataModel->setConditions($tmpConditions);

        return $selectedRows;
    }
    

    public function getSelectedCountNoDbModel($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        return count($selection['selected']);
    }

    public function getSelectedRowsNoDbModel($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        return $selection['selected'];
    }

    public function getTotalCount($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        $this->setModel($selection['data_model']);

        $implements = class_implements($this->dataModel);
        if (!isset($implements['Countable']))
            throw new Minder_Controller_Action_Helper_RowSelector_Exception("Model should provide Countable interface.");
        
        $count = count($this->dataModel);
        return $count;
    }

    /**
    * @deprecated This method should return rows selected on specified page. Now it behave
    * differently. But I will leave it untill all previouse code depends on it will be checked 
    * and rewritten using new getSelectedOnPage() method.
    * 
    * @param int $pageNo - zero-based page index
    * @param int $rowsPerPage - rows per page
    * @param boolean $getPKeysOnly - should method return all rows or only prymary keys
    * @param string $nameSpace - namespace to use
    * @param string $actionName - DEPRECATED. action name to use
    * @param string $controllerName - DEPRECATED. controller name to use
    * @return array
    */
    public function getSelected($pageNo = null, $rowsPerPage = null, $getPKeysOnly = true, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $pageNo = ($pageNo) ? $pageNo : 0;
        $offset = $pageNo * $rowsPerPage;
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        $this->setModel($selection['data_model']);
        if (count($selection['selected']) < 1) {
            //nothing selected
            return array();
        }
        
        $tmpModelConditions = $this->dataModel->getConditions();
        $dataRows           = $this->dataModel->getItems($offset, $rowsPerPage, true);
        
        if ($getPKeysOnly) {
            foreach ($dataRows as $rowId => $row) {
                if (isset($selection['selected']['__all'])) {
                    if (isset($selection['unselected'][$rowId])) {
                        //all rows is selected but this one is unselected so remove it from results
                        unset($dataRows[$rowId]);
                    }
                } else {
                    if (!isset($selection['selected'][$rowId])) {
                        //this row is not selected so remove it from results
                        unset($dataRows[$rowId]);
                    }
                }
            }
            
            return $dataRows;
        }
        
        $conditions = $this->getSelectConditions($nameSpace, $actionName, $controllerName);
        if (count($dataRows) > 0) {
            $visibleRowsCond = $this->dataModel->makeConditionsFromId(array_keys($dataRows), false);
            $combinedCondStr = implode(' AND ', array_keys($visibleRowsCond));
            
            if (!empty($conditions)) {
                $combinedCondStr .= ' AND ' . implode(' AND ', array_keys($conditions));
                $combinedArgs     = array_merge(array_values($visibleRowsCond), array_values($conditions));
            } else {
                $combinedArgs     = array_values($visibleRowsCond);
            }
            
            $reduceMethod = create_function('$val, $item', '$val = (empty($val)) ? array() : $val; return array_merge($val, $item);');
            $combinedArgs = array_reduce($combinedArgs, $reduceMethod, array());
            $conditions   = array($combinedCondStr => $combinedArgs);
        } else {
            //nothing is selected
            return array();
        }
        $this->dataModel->setConditions($conditions);
        $selectedRows = $this->dataModel->getItems(0, count($this->dataModel), $getPKeysOnly);
        $this->dataModel->setConditions($tmpModelConditions);
        
        return $selectedRows;
    }

    /**
     * @param string $nameSpace
     * @return array
     */
    protected function _getSelection($nameSpace = 'default') {
        return $this->_getSelectionLegacy($nameSpace, Minder_Controller_Action::$defaultSelectionAction, Minder_Controller_Action::$defaultSelectionController);
    }

    /**
     * <p>Returns rows selected on specfied 'page'.</p>
     * <p><strong>Important! Method performs DB query to fetch model rows.
     * If you already has rows for required page, use filterSelectedRows() as it much faster!</strong></p>
     *
     * @param int|null $pageNo
     * @param int|null $rowsPerPage
     * @param bool $getPKeysOnly
     * @param string $nameSpace
     * @return array
     */
    public function getSelectedOnPage($pageNo = null, $rowsPerPage = null, $getPKeysOnly = false, $nameSpace = 'default') {
        $selection = $this->_getSelection($nameSpace);
        if (count($selection['selected']) < 1) {
            //nothing selected
            return array();
        }
        $this->setModel($selection['data_model']);

        $pageNo       = ($pageNo) ? $pageNo : 0;
        $offset       = $pageNo * $rowsPerPage;
        $rowsOnPage   = $this->dataModel->getItems($offset, $rowsPerPage, $getPKeysOnly);
        
        foreach ($rowsOnPage as $rowId => $row) {
            if (isset($selection['selected']['__all'])) {
                if (isset($selection['unselected'][$rowId])) {
                    //all rows is selected but this one is unselected so remove it from results
                    unset($rowsOnPage[$rowId]);
                }
            } else {
                if (!isset($selection['selected'][$rowId])) {
                    //this row is not selected so remove it from results
                    unset($rowsOnPage[$rowId]);
                }
            }
        }
        
        return $rowsOnPage;
    }

    /**
     * Filters provided rows list, removing all rows wich was not selected.
     *
     * @param array $rowsToMark
     * @param string $nameSpace
     * @return array
     */
    public function filterSelectedRows($rowsToMark, $nameSpace = 'default') {
        return $this->_filterSelectedRows($this->_getSelection($nameSpace), $rowsToMark);
    }

    /**
     * @param array $selection
     * @param array $rowsToFilter
     * @return array
     */
    protected function _filterSelectedRows($selection, $rowsToFilter)
    {
        if (count($selection['selected']) < 1) {
            //nothing selected
            return array();
        }

        foreach ($rowsToFilter as $rowId => $row) {
            if (isset($selection['selected']['__all'])) {
                if (isset($selection['unselected'][$rowId])) {
                    //all rows is selected but this one is unselected so remove it from results
                    unset($rowsToFilter[$rowId]);
                }
            } else {
                if (!isset($selection['selected'][$rowId])) {
                    //this row is not selected so remove it from results
                    unset($rowsToFilter[$rowId]);
                }
            }
        }

        return $rowsToFilter;
    }

    /**
     * @deprecated
     * @param string $nameSpace
     * @param null $actionName
     * @param null $controllerName
     * @return mixed
     */
    protected function _getSelectionLegacy($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();

        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);

        return $this->session->selection[$controllerName][$actionName][$nameSpace];
    }

    public function filterSelectedRowsLegacy($rowsToMark, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        return $this->_filterSelectedRows($this->_getSelectionLegacy($nameSpace, $actionName, $controllerName), $rowsToMark);
    }

    public function countSelectedLegacy($rowsToCount, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $selectedRows = $this->filterSelectedRowsLegacy($rowsToCount, $nameSpace, $actionName, $controllerName);
        return count($selectedRows);
    }

    public function getSelectConditions($nameSpace = 'default', $actionName = null, $controllerName = null) {
        $request = $this->getRequest();
        
        if ($controllerName === null)
            $controllerName = $request->getControllerName();

        if ($actionName === null)
            $actionName = $request->getActionName();
            
        if (!isset($this->session->selection[$controllerName][$actionName][$nameSpace]))
            $this->session->selection[$controllerName][$actionName][$nameSpace] = array('selected' => array(), 'unselected' => array(), 'data_model' => null);
            
        $selection = $this->session->selection[$controllerName][$actionName][$nameSpace];
        
        $oldModel      = $this->getModel($nameSpace, $actionName, $controllerName);
        $this->setModel($selection['data_model']);
        
        $conditions    = array();
//        $tmpWrapMethod = create_function('$el','return "?";');
        
        
        if (isset($selection['selected']['__all'])) {
            if (count($selection['unselected']) > 0) {
                $conditions = array_merge($conditions, $this->dataModel->makeConditionsFromId($selection['unselected'], true));
            }
        } else {
            if (count($selection['selected']) > 0) {
                $conditions = array_merge($conditions, $this->dataModel->makeConditionsFromId($selection['selected'], false));
            }
        }
        if (!is_null($oldModel)) {
            $this->setModel($oldModel);
        } else {
            $this->dataModel = null;
        }
        
        return $conditions;
    }

    public function getSelectedRowsData($paramsMap, $companyId, $whId, $nameSpace = 'default', $actionName = null, $controllerName = null) {
        $selectedCount = $this->getSelectedCount($nameSpace, $actionName, $controllerName);

        if ($selectedCount < 1)
            return array();

        $model = $this->getModel($nameSpace, $actionName, $controllerName);
        $model
            ->setLimitCompanyId($companyId)
            ->setLimitWhId($whId)
            ->addConditions($this->getSelectConditions($nameSpace, $actionName, $controllerName));

        $fields = array_map(array($this, '_implodeFields'), $paramsMap, array_keys($paramsMap));

        return $model->selectArbitraryExpression(0, $selectedCount, 'DISTINCT ' . implode(', ', $fields));
    }

    public function setScreenSelection($sysScreen, $pagination, $namespace, $actionName, $controllerName) {
        $tmpSysScreen = new stdClass();
        $tmpSysScreen->selectedRows = array();
        $tmpSysScreen->selectedRowsTotal = 0;
        $tmpSysScreen->selectedRowsOnPage = 0;


        if (isset($sysScreen['paginator'])) {
            $pagination['selectedPage']  = (isset($sysScreen['paginator']['selectedPage']))  ? $sysScreen['paginator']['selectedPage']  : $pagination['selectedPage'];
            $pagination['showBy']        = (isset($sysScreen['paginator']['showBy']))        ? $sysScreen['paginator']['showBy']        : $pagination['showBy'];
            $pagination['selectionMode'] = (isset($sysScreen['paginator']['selectionMode'])) ? $sysScreen['paginator']['selectionMode'] : $pagination['selectionMode'];
        }

        if (isset($sysScreen['rowId']) && isset($sysScreen['state'])) {
            $this->setSelectionMode($pagination['selectionMode'], $namespace, $actionName, $controllerName);
            $this->setRowSelection($sysScreen['rowId'], $sysScreen['state'], $pagination['selectedPage'], $pagination['showBy'], null, false, $namespace, $actionName, $controllerName);
        }

        $tmpSysScreen->selectedRowsTotal = $this->getSelectedCount($namespace, $actionName, $controllerName);
        if ($tmpSysScreen->selectedRowsTotal > 0) {
            $tmpSysScreen->selectedRows = $this->getSelectedOnPage($pagination['selectedPage'], $pagination['showBy'], false, $namespace);
            $tmpSysScreen->selectedRowsOnPage = count($tmpSysScreen->selectedRows);
        }

        return $tmpSysScreen;
    }

    protected function _implodeFields($key, $value) {
        return $key . ' AS ' . $value;
    }
}
