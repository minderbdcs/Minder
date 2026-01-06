<?php

class Minder2_Options_PickOrderType_Manager {
    /**
     * @var Minder2_Options
     */
    protected $_options;

    /**
     * @return Minder2_Options_PickOrderType_Model[]
     */
    public function getEdiTypes() {
        return array_filter($this->getTypes(), function($type){
            /**
             * @var Minder2_Options_PickOrderType_Model $type
             */
            return $type->isEdi();
        });
    }

    /**
     * @return Minder2_Options_PickOrderType_Model[]
     */
    protected function getTypes() {
        $result = array();

        foreach ($this->_getOptions()->getPoSubTypes() as $option) {
            $result[] = $this->_fromOption($option);
        }

        return $result;
    }

    protected function _fromOption(Minder2_Model_Options $option) {
        list($subType, $type) = explode('|', $option->CODE);

        return new Minder2_Options_PickOrderType_Model($subType, $type);
    }

    /**
     * @return Minder2_Options
     */
    protected function _getOptions()
    {
        if (empty($this->_options)) {
            $this->_options = new Minder2_Options();
        }
        return $this->_options;
    }
}