<?php

/**
 * @method appendScript($script, $type = 'text/javascript', $attrs = array())
 */
class Minder2_View_Helper_AutoloadScript extends Zend_View_Helper_HeadScript {

    const ON_LOAD_START = '$(function(){';
    const ON_LOAD_END   = '});';

    /**
     * Registry key for placeholder
     * @var string
     */
    protected $_regKey = 'Minder2_View_Helper_AutoloadScript';

    /**
     * Return InlineScript object
     *
     * Returns InlineScript helper object; optionally, allows specifying a
     * script or script file to include.
     *
     * @param  string $mode Script or file
     * @param  string $spec Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array $attrs Array of script attributes
     * @param  string $type Script type and/or array of script attributes
     * @return Zend_View_Helper_InlineScript
     */
    public function autoloadScript($mode = Zend_View_Helper_HeadScript::SCRIPT, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return $this->headScript($mode, $spec, $placement, $attrs, $type);
    }

    /**
     * @param $item
     * @param string|int $indent
     * @param string $escapeStart
     * @param string $escapeEnd
     * @return string
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $html = '';
        if (!empty($item->source)) {
              $html .= $item->source;
        }
        return $html;
    }

    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        foreach ($this as $item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
        }

        $return = $indent . '<script type="text/javascript">' . PHP_EOL
                  . $indent . '    ' . $escapeStart . PHP_EOL
                  . $indent . '    ' . self::ON_LOAD_START . PHP_EOL
                  . implode($this->getSeparator(), $items)
                  . $indent . '    ' . self::ON_LOAD_END . PHP_EOL
                  . $indent . '    ' . $escapeEnd . PHP_EOL
                  . $indent . '</script>';
        return $return;
    }


}