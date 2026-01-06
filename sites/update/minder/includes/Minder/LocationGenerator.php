<?php

class Minder_LocationGenerator {
    const AISLE     = 'AISLE';
    const BAY       = 'BAY';
    const SHELF     = 'SHELF';
    const POSITION  = 'POSITION';
    const SUB       = 'SUB';

    protected $sequenceArray = array();
    
    protected $whId  = '';
    protected $zoneC = '';
    
    protected $items = array();
    
    public function __get($key) {
        return $this->$key;
    }
    
    public function __construct($whId, array $items = array()) {
        $this->whId  = $whId;
        $this->items = $items;
    }
    
    public function setSequence($sequenceName, $startValue = '00', $endValue = '00', $sequenceType = 'N2', $increment = 1) {
        try {
            switch ($sequenceName) {
                case Minder_LocationGenerator::AISLE:
                case Minder_LocationGenerator::BAY:
                case Minder_LocationGenerator::SHELF:
                case Minder_LocationGenerator::POSITION:
                    $this->sequenceArray[$sequenceName] = Minder_SequenceGenerator_Factory::createGenerator($sequenceName, $startValue, $endValue, $sequenceType, $increment);
                    break;
                default:
                    throw new Minder_LocationGenerator_Exception('Unknown Sequence Name "' . $sequenceName . '"');
            }
        } catch (Minder_SequenceGenerator_RegisterBase_Exception $e) {
            throw new Minder_LocationGenerator_Exception('Check "' . $sequenceName . '" Sequence type.');
        }
        
        
        return $this;
    }
    
    protected function checkSequenceInitialization() {
        switch (true) {
            case !isset($this->sequenceArray[Minder_LocationGenerator::AISLE]):
            case is_null($this->sequenceArray[Minder_LocationGenerator::AISLE]):
                throw new Minder_LocationGenerator_Exception(Minder_LocationGenerator::AISLE . ' is not initialized.');

            case !isset($this->sequenceArray[Minder_LocationGenerator::BAY]):
            case is_null($this->sequenceArray[Minder_LocationGenerator::BAY]):
                throw new Minder_LocationGenerator_Exception(Minder_LocationGenerator::BAY . ' is not initialized.');

            case !isset($this->sequenceArray[Minder_LocationGenerator::SHELF]):
            case is_null($this->sequenceArray[Minder_LocationGenerator::SHELF]):
                throw new Minder_LocationGenerator_Exception(Minder_LocationGenerator::SHELF . ' is not initialized.');

            case !isset($this->sequenceArray[Minder_LocationGenerator::POSITION]):
            case is_null($this->sequenceArray[Minder_LocationGenerator::POSITION]):
                throw new Minder_LocationGenerator_Exception(Minder_LocationGenerator::POSITION . ' is not initialized.');
        }
    }

    protected function _prepareParams($params) {
        $result = array();
        foreach ($params as $key => $value) {
            $result['%' . $key . '%'] = $value;
        }

        return $result;
    }

    protected function _fillParameters($template, $parameters) {
        return str_replace(array_keys($parameters), array_values($parameters), $template);
    }
    
    public function doGenerate() {
        $this->checkSequenceInitialization();
        
        $result = array();
        $generator = new Minder_SequenceGenerator_Composite(array(
            $this->sequenceArray[Minder_LocationGenerator::POSITION],
            $this->sequenceArray[Minder_LocationGenerator::SHELF],
            $this->sequenceArray[Minder_LocationGenerator::BAY],
            $this->sequenceArray[Minder_LocationGenerator::AISLE]
        ));

        $params                     = $this->_prepareParams($this->items);
        $params['%WH_ID%']          = $this->whId;
        $this->items['LOCN_ID']     = '%AISLE%%BAY%%SH%%POS%';

        foreach ($generator as $element) {
            $params['%AISLE%']                      = $element[Minder_LocationGenerator::AISLE];
            $params['%BAY%']                        = $element[Minder_LocationGenerator::BAY];
            $params['%SH%']                         = $element[Minder_LocationGenerator::SHELF];
            $params['%POS%']                        = $element[Minder_LocationGenerator::POSITION];

            $newLocation                            = new Location();
            $newLocation->items                     = $this->items;
            $newLocation->items['LOCN_ID']          = $this->_fillParameters($this->items['LOCN_ID'], $params);
            $newLocation->items['WH_ID']            = $this->whId;
            $newLocation->items['LOCN_NAME']        = $this->_fillParameters($this->items['LOCN_NAME'], $params);
            $result[]                               = $newLocation;
        }
        return $result;
    }
}