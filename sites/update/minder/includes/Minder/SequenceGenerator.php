<?php

class Minder_SequenceGenerator implements Iterator 
{
    /**
    * @var integer
    */
    protected $startValue   = 0;
    
    /**
    * @var integer
    */
    protected $endValue     = 0;
    
    /**
    * @var integer
    */
    protected $step    = 0;
    
    /**
    * @var integer
    */
    protected $sequenceSize = 1;
    
    /**
    * @var array(Minder_SequenceGenerator_RegisterBase_Interface)
    */
    protected $registers    = array();
    
    /**
    * @var integer
    */
    protected $_rawValue    = 0;
    
    /**
    * @var integer
    */
    protected $_maxValue    = 1;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @param $name
     * @param $startValue
     * @param $endValue
     * @param $increment
     * @param $sequenceSize
     * @param Minder_SequenceGenerator_RegisterBase_Interface[]|Minder_SequenceGenerator_RegisterBase_Interface $registers
     */
    public function __construct($name, $startValue, $endValue, $increment, $sequenceSize, $registers = array()) {
        //order of method calling is significant
        $this->setSequenceSize($sequenceSize)
                ->setName($name)
                ->setRegisters($registers)
                ->setStartValue($startValue)
                ->setEndValue($endValue)
                ->setStep($increment);
    }
    
    /**
    * @param mixed $startValue
    *
    * @return $this
    */
    public function setStartValue($startValue) {
        $this->startValue = $this->parseValue($startValue);
        return $this;
    }
    
    /**
    * @param mixed $endValue
    *
    * @return $this
    */
    public function setEndValue($endValue) {
        $this->endValue = $this->parseValue($endValue);
        return $this;
    }

    /**
     * @param integer $increment
     *
     * @throws Minder_SequenceGenerator_Exception
     * @return $this
     */
    public function setStep($increment) {
        $this->step = intval($increment);
        
        if ($this->step === 0)
            throw new Minder_SequenceGenerator_Exception('Step could not be null.');
            
        return $this;
    }

    /**
     * @param integer $sequenceSize
     *
     * @throws Minder_SequenceGenerator_Exception
     * @return $this
     */
    public function setSequenceSize($sequenceSize) {
        $this->sequenceSize = intval($sequenceSize);
        
        if ($this->sequenceSize < 1) {
            throw new Minder_SequenceGenerator_Exception('Sequence size should be greater then 0.');
        }
        
        return $this;
    }

    /**
     * @param Minder_SequenceGenerator_RegisterBase_Abstract $register
     * @throws Minder_SequenceGenerator_Exception
     */
    protected function checkRegisterType($register) {
        if (!($register instanceof Minder_SequenceGenerator_RegisterBase_Abstract))
            throw new Minder_SequenceGenerator_Exception('Unsupported Register provided.');
    }

    /**
    * @param Minder_SequenceGenerator_RegisterBase_Abstract | array(Minder_SequenceGenerator_RegisterBase_Abstract) $registers
    *
    * @return $this
    */
    public function setRegisters($registers) {
        if (is_array($registers))
            $this->registers = $registers;
        else
            $this->registers = array_fill(0, $this->sequenceSize, $registers);
            
        foreach ($this->registers as $register) 
            $this->checkRegisterType($register);
                
        return $this;
    }
    
    /**
    * @param Minder_SequenceGenerator_RegisterBase_Abstract $register
    *
    * @return $this
    */
    public function addRegister($register) {
        $this->checkRegisterType($register);
        array_push($this->registers, $register);
        
        return $this;
    }
    
    /**
    * @param string $valueToParse
    * 
    * @return integer
    */
    protected function parseValue($valueToParse) {
        $result = 0;
        $valueToParse = strtoupper($valueToParse);
        $base = 1;
        /**
        * @var Minder_SequenceGenerator_RegisterBase_Interface $register
        */
        $register = null;
        foreach ($this->registers as $register) {
            $result       += $base * $register->toBase10(substr($valueToParse, -1));
            $base         *= $register->getBase();
            $valueToParse  = substr($valueToParse, 0, -1);
            
            if (strlen($valueToParse) < 1)
                break;
        }
        
        return $result;
    }
    
    protected function getCurrentValue() {
        return $this->_rawValue % $this->_maxValue;
    }
    
    /**
    * @param integer $value
    *
    * @return string
    */
    protected function formatValue($value) {
        $result = '';
        $tmpRegisters = $this->registers;
        
        while ($value > 0) {
            /**
            * @var Minder_SequenceGenerator_RegisterBase_Interface $register
            */
            $register = array_shift($tmpRegisters);
            
            $result = $register->fromBase10($value % $register->getBase()) . $result;
            $value  = floor($value / $register->getBase());
        }
        
        foreach ($tmpRegisters as $register) {
            $result = $register->fromBase10(Minder_SequenceGenerator_RegisterBase_Abstract::NAN) . $result;
        }
        
        return $result;
    }
    
    protected function calcMaxValue() {
        $this->_maxValue = 1;
        foreach ($this->registers as $register)
            /**
            * @var Minder_SequenceGenerator_RegisterBase_Interface $register
            */
            $this->_maxValue *= $register->getBase();
    }   
    
    /**
    * @return string
    */
    public function current() {
        return $this->formatValue($this->getCurrentValue());
    }
    
    /**
    * Return same value as current()
    * 
    * @return string
    */
    public function key() {
        return $this->current();
    }
    
    public function next() {
        if ($this->_goingUp()) {
            $this->_rawValue += $this->step;
        } else {
            $this->_rawValue -= $this->step;
        }
    }
    
    public function rewind() {
        if ($this->step > 0) {
            $this->_rawValue = $this->startValue;
        } else {
            $this->_rawValue = $this->endValue;
        }
        $this->calcMaxValue();
    }
    
    public function valid() {
        if ($this->_goingUp()) {
            if ($this->step > 0) {
                return $this->_rawValue <= $this->endValue;
            } else {
                return $this->_rawValue >= $this->startValue;
            }
        } else {
            if ($this->step > 0) {
                return $this->_rawValue >= $this->endValue;
            } else {
                return $this->_rawValue <= $this->startValue;
            }
        }
    }

    protected function _goingUp() {
        return $this->endValue >= $this->startValue;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getStartBase10() {
        return $this->startValue;
    }

    public function getEndBase10() {
        return $this->endValue;
    }

    public function getBase() {
        return abs($this->getEndBase10() - $this->getStartBase10()) + 1;
    }

    public function getNormalizedCurrent() {
        if ($this->_goingUp()) {
            return $this->_rawValue - $this->getStartBase10();
        } else {
            return $this->_rawValue - $this->getEndBase10();
        }
    }

    /**
     * @return int
     */
    public function getSequenceSize()
    {
        return $this->sequenceSize;
    }

    public function parseValueNormalized($value) {
        if ($this->_goingUp()) {
            return $this->parseValue($value) - $this->getStartBase10();
        } else {
            return $this->parseValue($value) - $this->getEndBase10();
        }
    }
}