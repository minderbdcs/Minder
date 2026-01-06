<?php

/**
 * @property array(string) $itemIdList
 * @property string $addMode
 */
class Minder_AddPickItemRoutine_Request {

    const ADD_MODE_DEFAULT          = 'ADD_MODE_DEFAULT';
    const ADD_MODE_FORCE_AVAILABLE  = 'ADD_MODE_FORCE_AVAILABLE';
    const ADD_MODE_FORCE_REQUESTED  = 'ADD_MODE_FORCE_REQUESTED';

    /**
     * @var int
     */
    public $requiredForOrder = 0;

    /**
     * @var int
     */
    public $toAddAmount      = 0;

    /**
     * @var float
     */
    public $defaultPrice     = 0;

    /**
     * @var string
     */
    public $orderNo          = '';

    /**
     * @var array
     */
    protected $_itemIdList   = null;

    /**
     * @var string
     */
    protected $_addMode      = null;

    public function __construct($orderNo, $itemIdList, $requiredForOrder, $defaultPrice, $addMode = Minder_AddPickItemRoutine_Request::ADD_MODE_DEFAULT) {
        $this->orderNo          = strval($orderNo);
        $this->itemIdList       = $itemIdList;
        $this->requiredForOrder = intval($requiredForOrder);
        $this->defaultPrice     = floatval($defaultPrice);
        $this->addMode          = $addMode;
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'itemIdList':
                $this->_setItemIdList($value);
                break;
            case 'addMode':
                $this->_setAddMode($value);
                break;
        }
    }

    function __get($name)
    {
        switch ($name) {
            case 'itemIdList':
                return $this->_itemIdList;
            case 'addMode':
                return $this->_addMode;
        }
    }

    protected function _setItemIdList($itemIdList) {
        $this->_itemIdList = is_array($itemIdList) ? $itemIdList : array($itemIdList);
    }

    protected function _isAddModeSupported($addMode) {
        return in_array($addMode, array(self::ADD_MODE_DEFAULT, self::ADD_MODE_FORCE_AVAILABLE, self::ADD_MODE_FORCE_REQUESTED));
    }

    protected function _setAddMode($addMode) {
        if (!$this->_isAddModeSupported($addMode))
            throw new Minder_AddPickItemRoutine_Exception('Add mode "' . $addMode . '" is not supported.');

        $this->_addMode = $addMode;
    }
}