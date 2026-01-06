<?php

class Minder_Controller_Action_Helper_MasterSlave extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Minder_SysScreen_View_RelationBuilder
     */
    protected $_relationBuilder;

    /**
     * Just return helper instance to use
     * 
     * @return Minder_Controller_Action_Helper_MasterSlave
     */
    public function masterSlave() {
        return $this;
    }

    public function buildMasterSlaveChain($sysScreens) {
        $sysScreens = (is_array($sysScreens)) ? $sysScreens : array($sysScreens);

        $relations = $this->_getRelations($sysScreens);

        return Minder_ArrayUtils::recursiveGroup($relations, array('MASTER_SCREEN', 'SLAVE_SCREEN'));
    }

    protected function _getRootScreens($relations) {
        $masterScreens = Minder_ArrayUtils::mapField($relations, 'MASTER_SCREEN');
        $slaveScreens = Minder_ArrayUtils::mapField($relations, 'SLAVE_SCREEN');

        return array_diff($masterScreens, $slaveScreens);
    }

    protected function _buildMasterSlaveChain($relations) {
        return Minder_ArrayUtils::recursiveGroup($relations, array('MASTER_SCREEN', 'SLAVE_SCREEN'));
    }

    protected function _buildSlaveMasterChain($relations) {
        return Minder_ArrayUtils::recursiveGroup($relations, array('SLAVE_SCREEN', 'MASTER_SCREEN'));
    }

    protected function _getRelations($sysScreens) {
        $result = array();
        $sysScreens = array_flip($sysScreens);
        foreach ($sysScreens as $screenName => $index) {
            foreach ($this->_getRelationBuilder()->getSlaveSysScreens($screenName) as $relations) {
                foreach ($relations as $relation) {
                    $slaveScreen = $relation['SLAVE_SCREEN'];
                    $result[] = $relation;

                    if (!isset($sysScreens[$slaveScreen])) {
                        $sysScreens[$slaveScreen] = count($sysScreens) + 1;
                    }
                }
            }
        }

        return $result;
    }

    protected function _getRelationBuilder() {
        if (empty($this->_relationBuilder)) {
            $this->_relationBuilder = new Minder_SysScreen_View_RelationBuilder();
        }

        return $this->_relationBuilder;
    }

    protected function _hasCycles($chain) {
        $slaveScreens  = $masterScreens = array_flip(array_keys($chain));

        foreach ($chain as $masterScreen => $tmpSlaveScreens) {
            $masterScreens[$masterScreen] = is_array($masterScreens[$masterScreen]) ? $masterScreens[$masterScreen] : array();
            $slaveScreens[$masterScreen]  = is_array($slaveScreens[$masterScreen])  ? $slaveScreens[$masterScreen]  : array();

            foreach ($tmpSlaveScreens as $slaveScreen => $relations) {
                $masterScreens[$masterScreen][$slaveScreen] = '';

                $slaveScreens[$slaveScreen] = (is_array($slaveScreens[$slaveScreen])) ? $slaveScreens[$slaveScreen] : array();
                $slaveScreens[$slaveScreen][$masterScreen] = '';
            }
        }

        reset($masterScreens);
        while (false !== ($arrayElement = each($masterScreens))) {
            $testingScreen = $arrayElement['key'];
            $unset = false;

            if (empty($masterScreens[$testingScreen])) {
                foreach ($slaveScreens[$testingScreen] as $tmpScreen => $val) {
                    if (isset($masterScreens[$tmpScreen][$testingScreen]))
                        unset($masterScreens[$tmpScreen][$testingScreen]);
                }

                $unset = true;
            }

            if (empty($slaveScreens[$testingScreen])) {
                foreach ($masterScreens[$testingScreen] as $tmpScreen => $val) {
                    if (isset($slaveScreens[$tmpScreen][$testingScreen]))
                        unset($slaveScreens[$tmpScreen][$testingScreen]);
                }
                $unset = true;
            }

            if ($unset) {
                unset($masterScreens[$testingScreen]);
                unset($slaveScreens[$testingScreen]);
                reset($masterScreens);
            }
        }

        return !empty($masterScreens);
    }

    protected function _hasCycles1($msChain) {
        $toCheck = array_keys($msChain);
        $visited = array();
        $checked = array();

        while (count($toCheck) > 0) {
            $checking = array_pop($toCheck);
            array_push($toCheck, $checking);

            $slaveScreens = isset($msChain[$checking]) ? array_keys($msChain[$checking]) : array();
            $allSlaveChecked = true;

            foreach ($slaveScreens as $slaveScreen) {
                if (isset($checked[$slaveScreen]) ) {
                    continue;
                }

                if (isset($visited[$slaveScreen])) {
                    return true;
                }

                $visited[$checking] = true;
                $allSlaveChecked = false;
                array_push($toCheck, $slaveScreen);
                break;
            }

            if ($allSlaveChecked) {
                $checked[$checking] = true;
                unset($visited[$checking]);
                array_pop($toCheck);
            }
        }

        return false;
    }

    protected function _sortSlaveMasterChain($masterSlaveChain, $slaveMasterChain, $rootSysScreens) {
        $result = array();

        foreach ($rootSysScreens as $sysScreen => $val) {
            $slaveSysScreens    = isset($masterSlaveChain[$sysScreen]) ? $masterSlaveChain[$sysScreen] : array();
            $masterScreens      = isset($slaveMasterChain[$sysScreen]) ? $slaveMasterChain[$sysScreen] : array();
            $result[] = array($sysScreen => $masterScreens);

            if (!empty($slaveSysScreens))
                $result   = array_merge($result, $this->_sortSlaveMasterChain($masterSlaveChain, $slaveMasterChain, $slaveSysScreens));
        }

        return $result;
    }

    protected function _buildSortedSMChain($sysScreens) {
        $relations      = $this->_getRelations($sysScreens);
        $masterSlaveChain          = $this->_buildMasterSlaveChain($relations);
        $slaveMasterChain          = $this->_buildSlaveMasterChain($relations);
        $rootSysScreens = $this->_getRootScreens($relations);

        if ($this->_hasCycles1($relations))
            throw new Minder_Exception('Error init Master-Slave datasets: ' . implode(',', array_keys($masterSlaveChain)) . ' sys screens have cycling dependeces.');

        return $this->_sortSlaveMasterChain($masterSlaveChain, $slaveMasterChain, array_flip($rootSysScreens));
    }

    protected function _doSubDatasetsInit($slaveScreen, $masterSysScreens, $modelMap, $disabledRelations, $selectionAction, $selectionController) {
        if (empty($masterSysScreens)) return;

        $namespaceMap = array_flip($modelMap);

        if (!isset($namespaceMap[$slaveScreen]))
            throw new Minder_Exception('Unsupported sys screen ' . $slaveScreen);

        /**
        * @var Minder_Controller_Action_Helper_RowSelector $rowSelector
        */
        $rowSelector = $this->_actionController->getHelper('RowSelector');
        /**
         * @var Minder_SysScreen_Model $slaveModel
         */
        $slaveModel = $rowSelector->getModel($namespaceMap[$slaveScreen], $selectionAction, $selectionController);
        $slaveModel->startMasterSelectionConditions();

        foreach ($masterSysScreens as $masterSSName => $relations) {
            if (!isset($namespaceMap[$masterSSName]))
                throw new Minder_Exception('Unsupported sys screen ' . $masterSSName);

            if (isset($disabledRelations[$namespaceMap[$slaveScreen]]) && $disabledRelations[$namespaceMap[$slaveScreen]] == 'true') {
                continue;
            }

            /**
             * @var Minder_SysScreen_Model $masterModel
             */
            $masterModel = $rowSelector->getModel($namespaceMap[$masterSSName], $selectionAction, $selectionController);
            $originalConditions = $masterModel->getConditions();
            $masterModel->addConditions($rowSelector->getSelectConditions($namespaceMap[$masterSSName], $selectionAction, $selectionController));

            $selectedRows = $rowSelector->getSelectedCount($namespaceMap[$masterSSName], $selectionAction, $selectionController);

            foreach ($relations as $relation) {
                if ($selectedRows < 1) {
                    $slaveModel->addEmptyMasterSelectionConditions($relation);
                } else {
                    $masterValues = $masterModel->selectForeignKeyValues($relation, 0, $selectedRows);

                    $filterValues = array();
                    foreach ($masterValues as $valueRow) {
                        $filterValues[] = current($valueRow);
                    }

                    $slaveModel->createAndAddMasterSelectionConditions($relation, $filterValues);
                }
            }


            $masterModel->setConditions($originalConditions);
        }

        $slaveModel->applyMasterSelectionConditions();
        $rowSelector->setRowSelection('select_complete', 'init', null, null, $slaveModel, true, $namespaceMap[$slaveScreen], $selectionAction, $selectionController);
    }

    public function initSubDatasets($modelMap, $namespace = null, array $disabledRelations, $selectionAction, $selectionController) {
        if (is_null($namespace))
            $sysScreens = array_values($modelMap);
        else
            $sysScreens = array($modelMap[$namespace]);

        $msChain = $this->_buildSortedSMChain($sysScreens);
        foreach ($msChain as $chainElement) {
            reset($chainElement);
            $this->_doSubDatasetsInit(key($chainElement), current($chainElement), $modelMap, $disabledRelations, $selectionAction, $selectionController);
        }
    }

}