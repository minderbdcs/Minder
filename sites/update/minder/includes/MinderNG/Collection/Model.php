<?php

namespace MinderNG\Collection;

use MinderNG\Events;

/**
 * Class Model
 * @package MinderNG\Collection
 */
class Model implements ModelInterface, ModelAggregateInterface, IdAttributeProviderInterface {
    protected static $_defaults = array();
    protected $_attributes = array();
    protected $_changed = array();
    protected $_changing = false;
    protected $_previousAttributes = array();
    protected $_pending = false;
    protected $_id;
    protected $_cid;
    protected $_collection;

    /**
     * @var Events\PublisherInterface
     */
    private $_publisher;

    public final function init($attributes = null, $parse = false, $silent = false, $collection = null) {
        $this->_cid = uniqid('M', true);
        $attributes = is_null($attributes) ? array() : $attributes;

        $this->_collection = $collection;

        if ($parse) { $attributes = $this->parse($attributes); }

        $this->set(array_merge(static::$_defaults, $attributes), $silent, false);
        $this->_changed = array();

        $this->_initialize($attributes, $parse, $silent);
    }

    public final function set(array $values, $silent = false, $unset = false, $parse = false) {
        if (empty($values)) {
            return $this;
        }

        if ($parse) { $values = $this->parse($values); }

        $changes = array();
        $changing = $this->_changing;
        $this->_changing = true;

        if (!$changing) {
            $this->_previousAttributes = $this->_attributes;
            $this->_changed = array();
        }

        $values[$this->getIdAttribute()] = $this->_id = static::calculateId(array_merge($this->_attributes, $values));

        foreach ($values as $field => $value) {
            if (!isset($this->_attributes[$field]) || ($this->_attributes[$field] !== $value)) {
                $changes[] = $field;
            }

            if (!isset($this->_previousAttributes[$field]) || ($this->_previousAttributes[$field] !== $value)) {
                $this->_changed[$field] = $value;
            } else {
                if (isset($this->_changed[$field])) {
                    unset($this->_changed[$field]);
                }
            }

            if ($unset) {
                if (isset($this->_attributes[$field])) {
                    unset($this->_attributes[$field]);
                    $changes[] = $field;
                }

                if (isset($this->_previousAttributes[$field])) {
                    $this->_changed[$field] = $value;
                } else {
                    unset($this->_changed[$field]);
                }
            } else {
                $this->_attributes[$field] = $value;
            }
        }

        if (!$silent) {
            if (count($changes) > 0) {$this->_pending = true;}
            foreach ($changes as $changingField) {
                $newValue = isset($this->_attributes[$changingField]) ? $this->_attributes[$changingField] : null;
                $this->getPublisher()->trigger(new Event\ModelFieldChange($changingField, $this, $newValue));
            }
        }

        if ($changing) {return $this;}

        if (!$silent) {
            while ($this->_pending) {
                $this->_pending = false;
                $this->getPublisher()->trigger(new Event\ModelChange($this));
            }
        }

        $this->_pending = false;
        $this->_changing = false;

        return $this;
    }

    public function isInCollection() {
        return !is_null($this->_collection);
    }

    function __unset($name)
    {
        $this->offsetUnset($name);
    }

    function __isset($name)
    {
        return $this->offsetExists($name);
    }

    function __get($name)
    {
        return $this->offsetGet($name);
    }

    function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }


    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes)
    {
        return $attributes;
    }

    /**
     * By default do nothing.
     *
     * @param array $values
     * @param bool $parse
     * @param bool $silent
     * @internal param Options $options
     */
    protected function _initialize(array $values, $parse = false, $silent = false) {}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->_attributes[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->_attributes[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set(array($offset => $value));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->set(array($offset => ''), false, true, false);
    }

    public function clear($silent = false) {
        $this->set($this->_attributes, $silent, true, false);
    }

    public function hasChanged($field = null) {
        return is_null($field) ? (count($this->_changed) > 0) : isset($this->_changed[$field]);
    }

    public function changedAttributes(array $diff = null) {
        if (is_null($diff)) {return $this->_changed;}

        $old = $this->_changing ? $this->_previousAttributes : $this->_attributes;
        $changed = array();

        foreach ($diff as $fieldName => $value) {
            if (!isset($old[$fieldName]) || ($old[$fieldName] !== $value)) {
                $changed[$fieldName] = $value;
            }
        }

        return $changed;
    }

    /**
     * @return null|Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param null|Collection $collection
     * @return $this
     */
    public function setCollection(Collection $collection = null)
    {
        $this->_collection = $collection;
        return $this;
    }

    public function previous($fieldName) {
        if (empty($fieldName) || empty($this->_previousAttributes)) return null;
        return isset($this->_previousAttributes[$fieldName]) ? $this->_previousAttributes[$fieldName] : null;
    }

    public function previousId() {
        $idAttribute = $this->getIdAttribute();
        return isset($this->_previousAttributes[$idAttribute]) ? $this->_previousAttributes[$idAttribute] : null;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getCid()
    {
        return $this->_cid;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @return Events\PublisherInterface
     */
    public function getPublisher()
    {
        if (empty($this->_publisher)) {
            $this->_publisher = new Events\Publisher();
        }

        return $this->_publisher;
    }

    /**
     * @return ModelInterface
     */
    public function getModel()
    {
        return $this;
    }

    /**
     * @return string
     */
    public static function getIdAttribute()
    {
        return 'id';
    }

    public static function calculateId(array $attributes = array())
    {
        return isset($attributes[static::getIdAttribute()]) ? $attributes[static::getIdAttribute()] : null;
    }

    public function getArrayCopy($full = false)
    {
        return $this->_attributes;
    }

    public function isNew() {
        return is_null($this->_attributes[$this->getIdAttribute()]);
    }

    /**
     * @return array
     */
    public function getPreviousAttributes()
    {
        return $this->_previousAttributes;
    }
}