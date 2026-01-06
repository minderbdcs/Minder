<?php

class Minder_Controller_Action_Helper_SearchKeeper extends Zend_Controller_Action_Helper_Abstract 
{
    protected $session   = null;

    public function init() {
        if (!Zend_Registry::isRegistered('searchSession')) {
            Zend_Registry::set('searchSession', new Zend_Session_Namespace('search'));
        }
        
        $this->session = Zend_Registry::get('searchSession');
    }

    /**
     * @param array $searchFields
     * @param string $searchFieldSsvName
     * @return array | null
     */
    public function findSearchFieldByName($searchFieldSsvName, $searchFields) {
        foreach ($searchFields as $fieldDesc) {
            if ($fieldDesc['SSV_NAME'] == $searchFieldSsvName)
                return $fieldDesc;
        }

        return null;
    }

    public function isLabelSearch($searchFields, array $labels = null) {
        $tmp = array_filter($searchFields, function($searchField) use($labels) {
            $tmpInputMethod = explode('|', $searchField['SSV_INPUT_METHOD']);

            if (strtoupper($tmpInputMethod[0]) !== 'GI') {
                return false;
            }

            if (empty($searchField['SEARCH_VALUE'])) {
                return false;
            }

            if (!empty($labels)) {
                if (!in_array($searchField['SSV_EXPRESSION'], $labels)) {
                    return false;
                }
            }

            return true;
        });

        return count($tmp) > 0;
    }

    public function getLabelSearch($searchFields, $labelName) {
        foreach ($searchFields as $searchField) {
            $tmpInputMethod = explode('|', $searchField['SSV_INPUT_METHOD']);

            if (strtoupper($tmpInputMethod[0]) === 'GI' && strtoupper($searchField['SSV_EXPRESSION']) == strtoupper($labelName)) {
                return $searchField;
            }
        }

        return null;
    }

    public function makeSearch($searchFields = array(), $namespace = 'default', $actionName = null, $controllerName = null) {
        if (empty($namespace))
            throw new Minder_Controller_Action_Helper_SearchKeeper_Exception('search namespace couldn\'t be null.');
            
        $request = $this->getRequest();
            
        if (empty($actionName))
            $actionName = $request->getActionName();
            
        if (empty($controllerName))
            $controllerName = $request->getControllerName();
            
        if (!isset($this->session->search[$controllerName][$actionName][$namespace]))
            $this->session->search[$controllerName][$actionName][$namespace] = $this->_getSearchFieldStateCollection($searchFields);
            
        /**
         * @var SearchFieldState_Collection $savedSearch
         */
        $savedSearch = $this->session->search[$controllerName][$actionName][$namespace];
        
        $requestParams = $request->getParams();
            
        $requestParams = array_change_key_case($requestParams, CASE_UPPER);
        
        
        $pickModeParamName = null;
        if (isset($requestParams['PICK_MODE_PARAM_NAME']))
            $pickModeParamName = $requestParams['PICK_MODE_PARAM_NAME'];
        
        foreach ($searchFields as &$fieldDescription) {
            $fieldId = $this->formatSearchFieldId($fieldDescription);
            $value   = null;
            
            //check for pick mode param
            if ($pickModeParamName == $fieldId) {
                //this is pick mode param
                
                $value = null;
                if (isset($requestParams['PICK_MODE_PARAM_VALUE']))
                    $value = $requestParams['PICK_MODE_PARAM_VALUE'];
                
                
                if (empty($value) && !is_numeric($value)) {
                    unset($fieldDescription['SEARCH_VALUE']);
                    unset($fieldDescription['PICK_PARAM']);
                } else {
                    $fieldDescription['SEARCH_VALUE'] = $value;
                    $fieldDescription['PICK_PARAM'] = array(
                        'PICK_PROC'           => $this->getRequest()->getParam('PICK_PROC'),
                        'PICK_PARAM_TYPE'     => $this->getRequest()->getParam('PICK_PARAM_TYPE'),
                        'PICK_PARAM_MODE'     => $this->getRequest()->getParam('PICK_PARAM_MODE'),
                        'PICK_PARAM_ORDNO'    => $this->getRequest()->getParam('PICK_PARAM_ORDNO'),
                        'PICK_PARAM_STATUS'   => $this->getRequest()->getParam('PICK_PARAM_STATUS'),
                        'PICK_PARAM_PRIORITY' => $this->getRequest()->getParam('PICK_PARAM_PRIORITY'),
                        'PICK_PARAM_ID'       => $this->getRequest()->getParam('PICK_PARAM_ID'),
                        'ONE_OR_ACCEPT'       => $this->getRequest()->getParam('ONE_OR_ACCEPT')
                    );
                }
            } else {
                //this is normal param
                if (isset($requestParams[$fieldId])) {
                    $value = is_array($requestParams[$fieldId]) ? array_map('trim', $requestParams[$fieldId]) : trim($requestParams[$fieldId]);
                }
    
                if (empty($value) && !is_numeric($value)) {
                    unset($fieldDescription['SEARCH_VALUE']);
                } else {
                    $fieldDescription['SEARCH_VALUE'] = $value;
                    
                    $tmpInputMethod = explode('|', $fieldDescription['SSV_INPUT_METHOD']);
                    
                    if ($tmpInputMethod[0] == 'DL') {
                        foreach ($fieldDescription['ELEMENTS'] as &$elemDesc) {
                            if (empty($elemDesc['SSV_TABLE']))
                                $elemId = strtoupper('SEARCH-' . $elemDesc['SSV_ALIAS']);
                            else 
                                $elemId = strtoupper('SEARCH-' . $elemDesc['SSV_TABLE'] . '-' . $elemDesc['SSV_ALIAS']);
                            
                            $value = null;
    
                            if (isset($requestParams[$elemId])) {
                                $value = is_array($requestParams[$elemId]) ? array_map('trim', $requestParams[$elemId]) : trim($requestParams[$elemId]);
                            }
                            
                            if (empty($value) && !is_numeric($value)) {
                                unset($fieldDescription['SEARCH_VALUE']);
                            } else {
                                $elemDesc['SEARCH_VALUE'] = $value;
                            }
                        }
                    }

                    /*if($tmpInputMethod[0] == 'IN' && count($tmpInputMethod) > 1) {
                        // defines new 5-state IN|(ON,OFF,RIGHT,LEFT, BOTH) for modifying SEARCH_VALUE with wildcards
                        if (isset($tmpInputMethod[1])) {
                            switch($tmpInputMethod[1]) {
                                case 'ON':
                                    $fieldDescription['SEARCH_VALUE'] = $value;
                                    break;

                                case 'OFF':
                                    $fieldDescription['SEARCH_VALUE'] = trim($value, '%');
                                    break;

                                case 'LEFT':
                                    $fieldDescription['SEARCH_VALUE'] = '%' . $value;
                                    break;

                                case 'RIGHT':
                                    $fieldDescription['SEARCH_VALUE'] = $value . '%';
                                    break;

                                case 'BOTH':
                                    $fieldDescription['SEARCH_VALUE'] = '%' . $value . '%';
                                    break;
                            }
                        }
                    }*/
                }
            }
            $savedSearch[$fieldId]      = new SearchFieldState($fieldId, $fieldDescription);
        }

        $savedSearch->setWasSearch(true);
        $this->session->search[$controllerName][$actionName][$namespace] = $savedSearch;
        
        return $searchFields;
    }

    public function hasSearch($namespace = 'default', $actionName = null, $controllerName = null) {
        if (empty($namespace))
            throw new Minder_Controller_Action_Helper_SearchKeeper_Exception('search namespace couldn\'t be null.');

        $request = $this->getRequest();

        if (empty($actionName))
            $actionName = $request->getActionName();

        if (empty($controllerName))
            $controllerName = $request->getControllerName();

        if (!isset($this->session->search[$controllerName][$actionName][$namespace])) {
            return false;
        }

        /**
         * @var SearchFieldState_Collection $savedSearch
         */
        $savedSearch = $this->session->search[$controllerName][$actionName][$namespace];
        return $savedSearch->wasSearch();
    }
    
    public function getSearch($searchFields = array(), $namespace = 'default', $actionName = null, $controllerName = null) {
        if (empty($namespace))
            throw new Minder_Controller_Action_Helper_SearchKeeper_Exception('search namespace couldn\'t be null.');
            
        $request = $this->getRequest();
            
        if (empty($actionName))
            $actionName = $request->getActionName();
            
        if (empty($controllerName))
            $controllerName = $request->getControllerName();
            
        if (!isset($this->session->search[$controllerName][$actionName][$namespace]))
            $this->session->search[$controllerName][$actionName][$namespace] = $this->_getSearchFieldStateCollection($searchFields);

        /**
         * @var SearchFieldState_Collection $savedSearch
         */
        $savedSearch = $this->session->search[$controllerName][$actionName][$namespace];
        foreach ($searchFields as &$fieldDescription) {
            $fieldId = $this->formatSearchFieldId($fieldDescription);

            if ($savedSearch[$fieldId]->wasSaved) {
                $fieldDescription = $savedSearch[$fieldId]->state;
            } else {
                if (empty($fieldDescription['DEFAULT_VALUE'])) {
                    if (isset($fieldDescription['SEARCH_VALUE']))
                        unset($fieldDescription['SEARCH_VALUE']);
                } else {
                    $fieldDescription['SEARCH_VALUE'] = $fieldDescription['DEFAULT_VALUE'];
                }
                if (isset($fieldDescription['PICK_PARAM']))
                    unset($fieldDescription['PICK_PARAM']);
            }
        }

        return $searchFields;
    }

    public function formatSearchFieldId($fieldDescription) {
        if (empty($fieldDescription['SSV_TABLE']))
            return strtoupper('SEARCH-' . $fieldDescription['SSV_ALIAS']);
        
        return strtoupper('SEARCH-' . $fieldDescription['SSV_TABLE'] . '-' . $fieldDescription['SSV_ALIAS']);
    }

    protected function _getSearchFieldStateCollection($fields = array()) {
        $result = new SearchFieldState_Collection();
        foreach ($fields as $fieldDesc) {
            $tmpFieldName = $this->formatSearchFieldId($fieldDesc);
            $result[$tmpFieldName] = new SearchFieldState($tmpFieldName);
        }
        return $result;
    }

    public function getScreenSearchFields($screenName, $namespace, $action, $controller) {
        $screenBuilder                 = new Minder_SysScreen_Builder();
        list($searchFields, , , $GISearchFields) = $screenBuilder->buildSysScreenSearchFields($screenName);

        return $this->getSearch(array_merge($searchFields, $GISearchFields), $namespace, $action, $controller);
    }
}

class Minder_Controller_Action_Helper_SearchKeeper_Exception extends Minder_Exception {}