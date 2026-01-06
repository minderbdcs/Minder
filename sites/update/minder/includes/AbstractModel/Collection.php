<?php

class AbstractModel_Collection implements Iterator, Countable {
    /**
     * @var array $_items
     */
    protected $_items = array();

    /**
     * @var bool $_isValid
     */
    protected $_isValid = true;

    /**
     * @var AbstractModel_Prototype_Interface $_prototype
     */
    protected $_prototype = null;

    /**
     * @param AbstractModel_Prototype_Interface $prototype
     */
    public function __construct(AbstractModel_Prototype_Interface $prototype) {
        $this->_prototype = $prototype;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_isValid = (count($this->_items) > 0);
        reset($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->_isValid;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return key($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->_isValid = (false !== next($this->_items));
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return AbstractModel
     */
    public function current()
    {
        return current($this->_items);
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
        return count($this->_items);
    }

    /**
     * @param AbstractModel $element
     * @return void
     */
    public function addElement($element) {
        $this->_items[] = $element;
    }

    /**
     * @param int $index
     * @return AbstractModel|null
     */
    public function getElement($index) {
        return (count($this->_items) > $index) ? $this->_items[$index] : $this->_prototype->getNullObject();
    }

    public function loadFromTableRows($rows) {
        foreach ($rows as $tableRow) {
            $this->addElement($this->_prototype->getNewObject($tableRow));
        }
    }
}