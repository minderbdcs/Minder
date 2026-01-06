<?php

class Minder2_View_Helper_JQueryTemplate extends Zend_View_Helper_HeadScript {
    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Minder2_View_Helper_JQueryTemplate';

    /**
     * Are arbitrary attributes allowed?
     * @var bool
     */
    protected $_arbitraryAttributes = true;

    function jQueryTemplate($file = null, array $attrs = array()) {
        if (is_null($file))
            return $this;

        $path = $this->view->getScriptPath($file);
        if (false === $path)
            return $this;

        $this->headScript(Zend_View_Helper_HeadScript::SCRIPT, file_get_contents($path), 'APPEND', $attrs, 'text/x-jquery-tmpl');

        return $this;
    }

    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $escapeStart = '';
        $escapeEnd   = '';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        $return = implode($this->getSeparator(), $items);
        return $return;
    }

    public function captureStart($captureType = Zend_View_Helper_Placeholder_Container_Abstract::APPEND, $type = 'text/x-jquery-tmpl', $attrs = array())
    {
        parent::captureStart($captureType, $type, $attrs);
    }
}