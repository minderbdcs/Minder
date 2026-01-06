<?php

class Minder_SequenceGenerator_Composite implements Iterator {

    /**
     * @var Minder_SequenceGenerator[]
     */
    protected $_generators;

    protected $_key = 0;

    protected $_startBase10;

    protected $_endBase10;

    /**
     * @param Minder_SequenceGenerator[] $generators
     * @param null|string $start
     * @param null|string $end
     */
    function __construct(array $generators, $start = null, $end = null)
    {
        $this->setGenerators($generators);
        $this->_setStart($start);
        $this->_setEnd($end);
    }

    protected function _setStart($start = null) {
        $this->_startBase10 = empty($start) ? 0 : $this->_parseValue($start);
        return $this;
    }

    protected function _setEnd($end = null) {
        $this->_endBase10 = empty($end) ? $this->_getMaxValue() : $this->_parseValue($end);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $result = array();

        foreach ($this->getGenerators() as $generator) {
            $result[$generator->getName()] = $generator->current();
        }

        $this->_key++;

        return $result;
    }

    protected function _innerNext() {
        $index = 0;
        $generators = $this->getGenerators();
        /**
         * @var Minder_SequenceGenerator $current
         */
        $current = $generators[$index];
        $current->next();

        while (!$current->valid() && $index < count($generators) - 1) {
            $current->rewind();
            $index++;
            $current = $generators[$index];
            $current->next();
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_innerNext();

        while ($this->valid() && !$this->_inRange()) {
            $this->_innerNext();
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $generators = $this->getGenerators();
        if (count($this->getGenerators()) < 1) {
            return false;
        }

        $last = array_pop($generators);
        array_push($generators, $last);
        return $last->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        foreach ($this->getGenerators() as $generator) {
            $generator->rewind();
        }

        $this->_key = 0;

        while ($this->valid() && !$this->_inRange()) {
            $this->_innerNext();
        }
    }

    /**
     * @return Minder_SequenceGenerator[]
     */
    public function getGenerators()
    {
        return $this->_generators;
    }

    /**
     * @param Minder_SequenceGenerator[] $registers
     * @return $this
     */
    public function setGenerators(array $registers)
    {
        $this->_generators = $registers;
        return $this;
    }

    private function _getMaxValue()
    {
        $result = 1;
        foreach ($this->getGenerators() as $generator) {
            $result *= $generator->getBase();
        }

        return $result;
    }

    private function _parseValue($value)
    {
        $result = 0;
        $base = 1;

        $value = str_pad($value, $this->_getSequenceSize(), '0', STR_PAD_LEFT);

        foreach ($this->getGenerators() as $generator) {
            $result += $base * $generator->parseValueNormalized(substr($value, -$generator->getSequenceSize()));
            $value = substr($value, 0, -$generator->getSequenceSize());
            $base *= $generator->getBase();
        }

        return $result;
    }

    protected function _getCurrentBase10() {
        $result = 0;
        $base = 1;

        foreach ($this->getGenerators() as $generator) {
            $result += $base * $generator->getNormalizedCurrent();
            $base *= $generator->getBase();
        }

        return $result;
    }

    private function _inRange()
    {
        $currentBase10 = $this->_getCurrentBase10();
        if ($this->_startBase10 < $this->_endBase10) {
            return ($currentBase10 >= $this->_startBase10) && ($currentBase10 <= $this->_endBase10);
        } else {
            return ($currentBase10 <= $this->_startBase10) && ($currentBase10 >= $this->_endBase10);
        }
    }

    protected function _getSequenceSize() {
        $result = 0;

        foreach ($this->getGenerators() as $generator) {
            $result += $generator->getSequenceSize();
        }

        return $result;
    }
}