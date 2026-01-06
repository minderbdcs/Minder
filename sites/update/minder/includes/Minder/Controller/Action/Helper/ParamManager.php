<?php

/**
 * Class Minder_Controller_Action_Helper_ParamManger
 *
 * @method getDeviceDataIdentifier($dataId, Minder2_Model_SysEquip $device): Minder_Param_Param|null
 * @method getDeviceDataIdByType($dataTypes, Minder2_Model_SysEquip $device): array
 */
class Minder_Controller_Action_Helper_ParamManager extends Zend_Controller_Action_Helper_Abstract {
    protected $_paramManger;

    protected $_errors = array();

    public function hasErrors() {
        return count($this->_errors) > 0;
    }

    public function getErrors() {
        return $this->_errors;
    }

    /**
     * @param $dataIds
     * @param $dataTypes
     * @return array
     */
    public function generateSymbologyPrefixDescriptors($dataIds, $dataTypes) {
        $this->_errors = array();

        $tmpDataIds = $this->getCurrentDeviceDataIdByType($dataTypes);

        $dataIds = array_unique(array_merge($dataIds, array_values($tmpDataIds)));

        $result = array();

        foreach ($dataIds as $dataId) {
            try {
                $result[] = $this->generateSymbologyPrefixDescriptor($dataId);
            } catch (Exception $e) {
                $this->_errors[] = 'Cannot get description for ' . $dataId . ': ' . $e->getMessage();
            }
        }

        return $result;
    }

    public function generateSymbologyPrefixDescriptor($dataId) {
        return $this->getActionController()->view->SymbologyPrefixDescriptor($dataId);
    }

    public function getCurrentDeviceDataIdByType($dataTypes) {
        return $this->getDeviceDataIdByType($dataTypes, Minder2_Environment::getCurrentDevice());
    }


    function __call($name, $arguments) {
        return call_user_func_array(array($this->_getParamManager(), $name), $arguments);
    }

    protected function _getParamManager() {
        if (empty($this->_paramManger)) {
            $this->_paramManger = new Minder_Param_Manager();
        }

        return $this->_paramManger;
    }
}