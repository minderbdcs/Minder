<?php
  
class Minder_View_Helper_JsTemplates_SRTabHeaders extends Zend_View_Helper_FormElement
{
    public function SRTabHeaders() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"tabs-headers-tmpl\" type=\"text/x-jquery-tmpl\">
        <li tab_name=\"{{= SST_TAB_NAME}}\" tab_id=\"TAB-{{= SS_NAME + '-' + SST_TAB_NAME}}\">
            <a href=\"#TAB-{{= SS_NAME + '-' + SST_TAB_NAME}}\">
                <b>
                    <span>\${SST_TITLE}</span>
                </b>
            </a>
        </li>
</script>
        ";
    }
}