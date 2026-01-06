<?php

abstract class Minder_Collection implements IteratorAggregate, Countable {
    /**
     * @var ArrayAccess[]
     */
    protected $_data = array();

    /**
     * @param ArrayAccess[] $data
     */
    function __construct(array $data = array())
    {
        $this->add($data);
    }

    /**
     * @param ArrayAccess|ArrayAccess[]
     */
    public function add($data) {
        $data = is_array($data) ? $data : array($data);

        foreach ($data as $object) {
            $this->_add($object);
        }
    }

    public function fromArray($data = array()) {
        foreach ($data as $itemData) {
            $this->_add($this->_newItem($itemData));
        }

        return $this;
    }

    abstract protected function _newItem($itemData = array());

    function __get($name)
    {
        return $this->_getPropertyArray($name);
    }

    protected function _getPropertyArray($name) {
        $result = array();

        foreach ($this->_getData() as $object) {
            $result[] = $object->offsetExists($name) ? $object->offsetGet($name) : null;
        }

        return $result;
    }

    /**
     * @param ArrayAccess $object
     */
    protected function _add(ArrayAccess $object) {
        $this->_data[] = $object;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_getData());
    }

    protected function _getData() {
        return $this->_data;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_getData());
    }

    protected function _filter($callback) {
        return new static(array_filter($this->_getData(), $callback));
    }

    public function getArrayCopy() {
        $result = array();

        foreach ($this->getIterator() as $row) {
            /**
             * @var ArrayObject $row
             */

            $result[] = $row->getArrayCopy();
        }

        return $result;
    }
}