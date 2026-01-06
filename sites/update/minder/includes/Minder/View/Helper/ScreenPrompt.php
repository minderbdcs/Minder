<?php

class Minder_View_Helper_ScreenPrompt extends Zend_View_Helper_Abstract {
    /**
     * @var Minder_Prompt_Manager
     */
    protected $_promptManager;

    public function screenPrompt() {
        return $this;
    }

    public function getOrderCheckPrompts() {
        $result = array();
        try {
            $result = $this->_getPromptManager()->getOrderCheckPrompts();
        } catch (Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }

        return $result;
    }

    public function getRepackSsccPrompts() {
        $result = array();
        try {
            $result = $this->_getPromptManager()->getRePackSsccPrompts();
        } catch (Exception $e) {
            user_error($e->getMessage(), E_USER_ERROR);
        }

        return $result;
    }

    /**
     * @return Minder_Prompt_Manager
     */
    protected function _getPromptManager()
    {
        if (empty($this->_promptManager)) {
            $this->_promptManager = new Minder_Prompt_Manager(new Minder2_Options());
        }

        return $this->_promptManager;
    }
}