<?php
  
class Minder_View_Helper_JsTemplates_SRDefaultHeader extends Zend_View_Helper_FormElement
{
    public function SRDefaultHeader() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"header-tmpl\" type=\"text/x-jquery-tmpl\">
    <ul style=\"clear: both;\" class=\"black-top-border\">
        <li style=\"display: inline;\">Selected: <span class=\"selected-container {{= \$item.data.namespace}}\">{{= \$item.data.paginator.selectedRows}}</span></li>
    </ul>    
</script>
        ";
    }
}