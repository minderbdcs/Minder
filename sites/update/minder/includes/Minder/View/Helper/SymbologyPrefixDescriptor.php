<?php
  
class Minder_View_Helper_SymbologyPrefixDescriptor extends Zend_View_Helper_Abstract
{

    /**
     * @var Minder_Param_Manager
     */
    protected $_paramManager;

    public function getJSDescription($dataId) {
        return json_encode($this->getDescription($dataId));
    }

    protected function _getEmptyDescription($dataId, Minder2_Model_SysEquip $device) {
        return array(
            'DATA_ID' => $dataId,
            'DATA_BRAND' => $device->getBrandOrDefault(),
            'DATA_MODEL' => $device->getModelOrDefault(),
            'MAX_LENGTH' => 0,
            'DATA_TYPE' => 1,
            'FIXED_LENGTH' => 'F',
            'SYMBOLOGY_PREFIX' => '',
            'DATA_EXPRESSION' => '',
            'GLOBAL_SEARCH' => '',
            'MENU_NAME' => '',
            'TABLE_NAME' => '',
            'FIELD_NAME' => '',
            'MENU_URL' => ''
        );
    }
    
    public function getDescription($dataId) {
        return $this->_fetchExistedOrEmpty(strtoupper($dataId), Minder2_Environment::getCurrentDevice());
    }

    public function getTypeDescription($type) {
        $type = strtoupper($type);
        $device = Minder2_Environment::getCurrentDevice();
        $dataIdentifiers = array();
        $result = array();

        try {
            $deviceDataIdList = $this->_getParamManager()->getDeviceDataIdByType($type, $device);
            $dataIdentifiers = $this->_getParamManager()->getMany($deviceDataIdList, $device);
        } catch (Exception $e) {
            user_error('Error fetching data identifiers for type "' . $type . '": ' . $e->getMessage(), E_USER_WARNING);
        }

        foreach ($dataIdentifiers as $dataId => $dataIdentifier) {
            if (empty($dataIdentifier)) {
                user_error('Data identifier "' . $dataId . '"" was not found.', E_USER_WARNING);
                $dataIdentifier = $this->_getEmptyDescription($dataId, $device);
            } else {
                $dataIdentifier = $dataIdentifier->getArrayCopy();
            }

            $result[] = $this->_initDataIdentifier($dataIdentifier);
        }

        return $result;
    }
    
    
    public function SymbologyPrefixDescriptor($dataId) {
        return $this->getJSDescription($dataId);
    }

    /**
     * @param $dataId
     * @param Minder2_Model_SysEquip $device
     * @return array
     */
    protected function _fetchExistedOrEmpty($dataId, Minder2_Model_SysEquip $device)
    {
        $description = null;

        try {
            $description = $this->_getParamManager()->getDeviceDataIdentifier($dataId, $device);
        } catch (Exception $e) {
            user_error('Error fetching data identifier "' . $dataId . '": ' . $e->getMessage(), E_USER_WARNING);
        }

        $description = $description ? $description->getArrayCopy() : $this->_getEmptyDescription($dataId, $device);
        $result = $this->_initDataIdentifier($description);

        return $result;
    }

    protected function _getParamManager() {
        if (empty($this->_paramManager)) {
            $this->_paramManager = new Minder_Param_Manager();
        }

        return $this->_paramManager;
    }

    /**
     * @param $description
     * @return array
     */
    protected function _fillPrefixArray($description)
    {
        $result = explode(';', $description['SYMBOLOGY_PREFIX']);

        array_pop($result);

        rsort($result, SORT_STRING);

        return $result;
    }

    /**
     * @param $description
     * @return array
     */
    protected function _initDataIdentifier($description)
    {
        $description['PREFIX_ARRAY']    = $this->_fillPrefixArray($description);
        $description['NO_PREFIX']       = (count($description['PREFIX_ARRAY']) < 1);

        return $description;
    }
}