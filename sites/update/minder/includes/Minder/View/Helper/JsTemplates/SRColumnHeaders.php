<?php
  
class Minder_View_Helper_JsTemplates_SRColumnHeaders extends Zend_View_Helper_FormElement
{
    public function SRColumnHeaders() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"column-headers-tmpl\" type=\"text/x-jquery-tmpl\">
    <th class=\"header\" style=\"width: \${SSV_FIELD_WIDTH}px;\" column-id=\"\${RECORD_ID}\">\${SSV_TITLE}</th>
</script>
        ";
    }
}