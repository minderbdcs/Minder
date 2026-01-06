<?php

/**
 * Provide row selection functionality for legacy pages
 *
 * @deprecated do not use for new controllers
 * @class Minder_Controller_Action_Helper_LegacyRowSelector
 */
class Minder_Controller_Action_Helper_LegacyRowSelector extends Zend_Controller_Action_Helper_Abstract {
    const SELECTION_MODE_ALL = 'all';
    const SELECTION_MODE_ONE = 'one';
    const SESSION_NAMESPACE = 'LEGACY_ROW_SELECTOR_NAMESPACE';

    protected $_namespace  = null;

    /**
     * @return Zend_Session_Namespace
     */
    protected function _getSession() {
        if (Zend_Registry::isRegistered(self::SESSION_NAMESPACE))
            return Zend_Registry::get(self::SESSION_NAMESPACE);

        $session = new Zend_Session_Namespace(self::SESSION_NAMESPACE);
        Zend_Registry::set(self::SESSION_NAMESPACE, $session);

        return $session;
    }

    /**
     * @return array
     */
    protected function _getSavedSelection() {
        $session    = $this->_getSession();
        $selections = isset($session->selections) ? $session->selections : array();
        $namespace  = $this->_getNamespace();

        return isset($selections[$namespace]) ? $selections[$namespace] : array('rows' => array(), 'mode' => self::SELECTION_MODE_ALL);
    }

    /**
     * @param array $val
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _saveSelection($val) {
        $session = $this->_getSession();
        $selections = isset($session->selections) ? $session->selections : array();

        $selections[$this->_getNamespace()] = $val;
        $session->selections = $selections;

        return $this;
    }

    /**
     * @return string
     */
    protected function _getSelectionMode() {
        $selection = $this->_getSavedSelection();
        return $selection['mode'];
    }

    /**
     * @param string $mode
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _setSelectionMode($mode) {
        $selection = $this->_getSavedSelection();
        $selection['mode'] = $mode;
        $this->_saveSelection($selection);

        return $this;
    }

    /**
     * @param string $val
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _setNamespace($val) {
        $this->_namespace = strval($val);
        return $this;
    }

    /**
     * @return string
     */
    protected function _getNamespace() {
        if (empty($this->_namespace))
            $this->_setNamespace(implode('_', array($this->getRequest()->getModuleName(), $this->getRequest()->getControllerName(), $this->getRequest()->getActionName())));

        return $this->_namespace;
    }

    /**
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _clearSelection() {
        $this->_saveSelection(array('rows' => array(), 'mode' => $this->_getSelectionMode()));
        return $this;
    }

    /**
     * @param array|string $rowIds
     * @param array $lines
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _selectRowIds($rowIds, $lines) {
        $rowIds = is_array($rowIds) ? $rowIds : array($rowIds);

        if (count($rowIds) < 1 || count($lines) < 1)
            return $this;

        if ($this->_getSelectionMode() == self::SELECTION_MODE_ONE) {
            $rowIds = array(array_shift($rowIds));
        }

        $selection = $this->_getSavedSelection();

        foreach ($rowIds as $rowId) {
            if (isset($lines[$rowId]))
                $selection['rows'][$rowId] = $rowId;
        }

        $this->_saveSelection($selection);
        return $this;
    }

    /**
     * @param array|string $rowIds
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _unselectRowIds($rowIds) {
        $rowIds = is_array($rowIds) ? $rowIds : array($rowIds);

        if (count($rowIds) < 1)
            return $this;

        $selection = $this->_getSavedSelection();

        foreach ($rowIds as $rowId) {
            if (isset($selection['rows'][$rowId]))
                unset($selection['rows'][$rowId]);
        }

        $this->_saveSelection($selection);
        return $this;
    }

    /**
     * @param array $lines
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _selectLines($lines) {
        if (count($lines) < 1)
            return $this;

        if ($this->_getSelectionMode() == self::SELECTION_MODE_ONE) {
            $lines = array(array_shift($lines));
        }

        $selection = $this->_getSavedSelection();

        foreach ($lines as $rowId => $lineData) {
            $selection['rows'][$rowId] = $rowId;
        }

        $this->_saveSelection($selection);
        return $this;
    }

    /**
     * @param array $lines
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    protected function _unselectLines($lines) {
        if (count($lines) < 1)
            return $this;

        $selection = $this->_getSavedSelection();

        foreach ($lines as $rowId => $lineData) {
            if (isset($selection['rows'][$rowId]))
                unset($selection['rows'][$rowId]);
        }

        $this->_saveSelection($selection);
        return $this;
    }

    /**
     * @param string | array $rowIds
     * @param string $method
     * @param array $lines
     * @param null|string $namespace
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    public function selectRows($rowIds, $method, $lines, $namespace = null) {
        $this->_setNamespace($namespace);

        switch ($method) {
            case 'true':
                if ($this->_getSelectionMode() == self::SELECTION_MODE_ONE)
                    $this->_clearSelection();

                if ($rowIds == 'select_all')
                    $this->_selectLines($lines);
                else
                    $this->_selectRowIds($rowIds, $lines);

                break;

            case 'false':
                if ($this->_getSelectionMode() == self::SELECTION_MODE_ONE)
                    $this->_clearSelection();

                if ($rowIds == 'select_all')
                    $this->_unselectLines($lines);
                else
                    $this->_unselectRowIds($rowIds);

                break;
        }

        return $this;
    }

    /**
     * @param null|string $namespace
     * @return array
     */
    public function getSelectedRowIds($namespace = null) {
        $this->_setNamespace($namespace);
        $selection = $this->_getSavedSelection();

        return $selection['rows'];
    }

    /**
     * @param null|string $namespace
     * @return string
     */
    public function getSelectionMode($namespace = null) {
        return $this
                    ->_setNamespace($namespace)
                    ->_getSelectionMode();
    }

    /**
     * @param string $mode
     * @param null|string $namespace
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    public function setSelectionMode($mode, $namespace = null) {
        $this
            ->_setNamespace($namespace)
            ->_setSelectionMode($mode);

        return $this;
    }

    /**
     * @param null|string $namespace
     * @return Minder_Controller_Action_Helper_LegacyRowSelector
     */
    public function clearSelection($namespace = null) {
        $this->_setNamespace($namespace)->_clearSelection();
        return $this;
    }
}