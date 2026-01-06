<?php

class Minder_Form_Decorator_FormTabsContainer extends Zend_Form_Decorator_Abstract {

    protected function _getHtmlAttributes() {
        return $this->getElement()->getAttribs();
    }

    public function render($content)
    {
        $htmltag = new Zend_Form_Decorator_HtmlTag();
        $htmltag->setElement($this->getElement());

        $options = $this->_getHtmlAttributes();
        $options = array_merge($options, array(
                    'tag' => 'div',
                    'class' => 'ui-tabs-container',
                    'data-type' => 'MinderElement'
        ));

        $htmltag->setOptions($options);

        return $htmltag->render($content);
    }

}