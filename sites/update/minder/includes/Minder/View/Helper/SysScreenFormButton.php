<?php

class Minder_View_Helper_SysScreenFormButton extends Zend_View_Helper_FormElement
{
    public function SysScreenFormButton($attribs = array(), $buttonDescription) {

        $attribs['id'] = $buttonId = isset($attribs['id']) ? $attribs['id'] : 'BUTTON_' . $buttonDescription['RECORD_ID'];
        $attribs['content'] = isset($attribs['content']) ? $attribs['content'] : $buttonDescription['SSB_TITLE'];
        $attribs['onclick'] = isset($attribs['onclick']) ? $attribs['onclick'] : 'return false;';
        $attribs['title']   = isset($attribs['title']) ? $attribs['title'] : $buttonDescription['SSB_BUTTON_NAME'];

        $xhtml  = $this->view->formButton($buttonId, null, $attribs);
        $xhtml .= '<script type="text/javascript">'
                        . '$("#' . $buttonId . '").unbind("click.sys-screen-button").bind("click.sys-screen-button", function(evt) {'
                        . htmlspecialchars_decode($buttonDescription['SSB_ACTION'])
                        . '});'
                  . '</script>
        ';
        return $xhtml;
    }

}