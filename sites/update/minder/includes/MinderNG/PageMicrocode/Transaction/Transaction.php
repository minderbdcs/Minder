<?php

namespace MinderNG\PageMicrocode\Transaction;

/**
 * Class Transaction
 * @package MinderNG\PageMicrocode\Transaction
 * @property string RESPONSE_TEXT
 */
class Transaction implements \Transaction_V6Interface, \ArrayAccess {
    const WH_ID = 'WH_ID';
    const LOCN_ID = 'LOCN_ID';
    const OBJECT = 'OBJECT';
    const REFERENCE = 'REFERENCE';
    const QTY = 'QTY';
    const DATE = 'DATE';
    const ERROR_TEXT = 'ERROR_TEXT';

    private $_fieldValues = array();
    private $_transactionType;
    private $_transactionCode;

    function __construct($transactionType, $transactionCode, array $fieldValues = array())
    {
        $this->_transactionType = $transactionType;
        $this->_transactionCode = $transactionCode;
        $this->_fieldValues = $fieldValues;
    }

    /**
     * is utilized for reading data from inaccessible members.
     *
     * @param $name string
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
     */
    function __get($name)
    {
        return isset($this->_fieldValues[$name]) ? $this->_fieldValues[$name] : null;
    }

    /**
     * @return string
     */
    public function getWhId()
    {
        $whId = isset($this->_fieldValues[static::WH_ID]) ? $this->_fieldValues[static::WH_ID] : '';
        return str_pad($whId, 2, ' ');
    }

    /**
     * @return string
     */
    public function getLocnId()
    {
        return isset($this->_fieldValues[static::LOCN_ID]) ? $this->_fieldValues[static::LOCN_ID] : '';
    }

    /**
     * @return string
     */
    public function getObjectId()
    {
        return isset($this->_fieldValues[static::OBJECT]) ? $this->_fieldValues[static::OBJECT] : '';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_transactionType;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_transactionCode;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return empty($this->_fieldValues[static::DATE]) ? date("Y-M-d H:i:s") : $this->_fieldValues[static::DATE];
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return isset($this->_fieldValues[static::REFERENCE]) ? $this->_fieldValues[static::REFERENCE] : '';
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return empty($this->_fieldValues[static::QTY]) ? '0' : $this->_fieldValues[static::QTY];
    }

    /**
     * @return string
     */
    public function getComplete()
    {
        // TODO: Implement getComplete() method.
    }

    public function setResponseText($text) {
        $this->_fieldValues[static::ERROR_TEXT] = $text;
    }

    /**
     * @return string
     */
    public function getErrorText()
    {
        return isset($this->_fieldValues[static::ERROR_TEXT]) ? $this->_fieldValues[static::ERROR_TEXT] : '';
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        // TODO: Implement getInstanceId() method.
    }

    /**
     * @return string
     */
    public function getExported()
    {
        // TODO: Implement getExported() method.
    }

    /**
     * @return string
     */
    public function getSubLocation()
    {
        // TODO: Implement getSubLocation() method.
    }

    /**
     * @return string
     */
    public function getInputSource()
    {
        // TODO: Implement getInputSource() method.
    }

    /**
     * @return string
     */
    public function getPersonId()
    {
        // TODO: Implement getPersonId() method.
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        // TODO: Implement getDeviceId() method.
    }

    /**
     * @return string
     */
    public function getProdId()
    {
        // TODO: Implement getProdId() method.
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        // TODO: Implement getCompanyId() method.
    }

    /**
     * @return string
     */
    public function getOrderNo()
    {
        // TODO: Implement getOrderNo() method.
    }

    /**
     * @return string
     */
    public function getOrderType()
    {
        // TODO: Implement getOrderType() method.
    }

    /**
     * @return string
     */
    public function getOrderSubType()
    {
        // TODO: Implement getOrderSubType() method.
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        // TODO: Implement getFamily() method.
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->_fieldValues[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->_fieldValues[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->_fieldValues[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->_fieldValues[$offset]);
    }
}