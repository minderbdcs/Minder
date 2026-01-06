<?php

class Minder2_View_Helper_Messages extends Zend_View_Helper_Abstract {
    const MESSAGE = 'MESSAGE';
    const WARNING = 'WARNING';
    const ERROR   = 'ERROR';

    protected $_messages = array();
    protected $_namespace = self::MESSAGE;

    /**
     * @return string
     */
    protected function _getNamespace() {
        if (empty($this->_namespace))
            return self::MESSAGE;

        return $this->_namespace;
    }

    /**
     * @param array|string $messages
     * @param string|null $namespace
     * @return Minder2_View_Helper_Messages
     */
    public function messages($messages = null, $namespace = null) {
        if (!empty($messages))
            $this->addMessage($messages, $namespace);
        
        return $this;
    }

    /**
     * @param array|string $messages
     * @param string|null $namespace
     * @return Minder2_View_Helper_Messages
     */
    public function addMessage($messages, $namespace = null) {
        $messages = is_array($messages) ? $messages : array($messages);
        $namespace = empty($namespace) ? $this->_getNamespace() : $namespace;
        $this->_messages[$namespace] = isset($this->_messages[$namespace]) ? array_merge($this->_messages[$namespace], $messages) : $messages;
        return $this;
    }

    /**
     * @param array|string $warnings
     * @return Minder2_View_Helper_Messages
     */
    public function addWarning($warnings) {
        return $this->addMessage($warnings, self::WARNING);
    }

    /**
     * @param array|string $errors
     * @return Minder2_View_Helper_Messages
     */
    public function addError($errors) {
        return $this->addMessage($errors, self::ERROR);
    }

    /**
     * @param string|null $namespace
     * @return bool
     */
    public function hasMessages($namespace = null) {
        $namespace = empty($namespace) ? $this->_getNamespace() : $namespace;
        return !empty($this->_messages[$namespace]);
    }

    /**
     * @return bool
     */
    public function hasWarnings() {
        return $this->hasMessages(self::WARNING);
    }

    /**
     * @return bool
     */
    public function hasErrors() {
        return $this->hasMessages(self::ERROR);
    }

    /**
     * @param string|null $namespace
     * @return array
     */
    public function getMessages($namespace = null) {
        $namespace = empty($namespace) ? $this->_getNamespace() : $namespace;

        if ($this->hasMessages($namespace))
            return $this->_messages[$namespace];

        return array();
    }

    /**
     * @return array
     */
    public function getWarnings() {
        return $this->getMessages(self::WARNING);
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->getMessages(self::ERROR);
    }

    /**
     * @param string $namespace
     * @return Minder2_View_Helper_Messages
     */
    public function setNamespace($namespace) {
        $this->_namespace = $namespace;
        return $this;
    }

    /**
     * @param string|null $namespace
     * @return Minder2_View_Helper_Messages
     */
    public function clearMessages($namespace = null) {
        $namespace = empty($namespace) ? $this->_getNamespace() : $namespace;
        if (isset($this->_messages[$namespace]))
            unset($this->_messages[$namespace]);

        return $this;
    }

    /**
     * @return Minder2_View_Helper_Messages
     */
    public function clearWarnings() {
        return $this->clearMessages(self::WARNING);
    }

    /**
     * @return Minder2_View_Helper_Messages
     */
    public function clearErrors() {
        return $this->clearMessages(self::ERROR);
    }

    /**
     * @param string|null $namespace
     * @return string
     */
    protected function _getJsMethod($namespace) {
        $namespace = empty($namespace) ? $this->_getNamespace() : $namespace;

        switch (strtoupper($namespace)) {
            case self::ERROR:
                return 'showErrors';
            case self::WARNING:
                return 'showWarnings';
            default:
                return 'showMessage';
        }
    }

    /**
     * @param string|null $namespace
     * @return void
     */
    public function renderMessages($namespace = null) {
        if (!$this->hasMessages($namespace))
            return;

        $script = $this->_getJsMethod($namespace) . '(' . json_encode($this->getMessages($namespace)) . ');';
        $this->view->autoloadScript()->prependScript($script);
        $this->clearMessages($namespace);
    }

    /**
     * @return void
     */
    public function renderWarnings() {
        $this->renderMessages(self::MESSAGE);
    }

    /**
     * @return void
     */
    public function renderErrors() {
        $this->renderMessages(self::ERROR);
    }

    /**
     * @return string
     */
    function __toString()
    {
        $this->renderErrors();
        $this->renderWarnings();
        $this->renderMessages(self::MESSAGE);

        foreach ($this->_messages as $namespace => $messages)
            $this->renderMessages($namespace);

        return '';
    }
}