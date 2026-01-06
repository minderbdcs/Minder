<?php
  
class Minder_View_Helper_JsTemplates_SRINField extends Zend_View_Helper_FormElement
{
    public function SRINField() {
        return $this->render();
    }
    
    public function render() {
        
        $rowId    = '{{= $item.parent.parent.data.row_id}}';
        $columnId = '{{= $item.data.RECORD_ID}}';
        $alias    = '{{= $item.data.SSV_ALIAS}}';
        $fullName = '{{if $item.data.SSV_TABLE != ""}}{{= $item.data.SSV_TABLE}}-{{/if}}{{= $item.data.SSV_ALIAS}}';
        $value    = '{{= $item.value}}';
        
        $style    = 'width: {{= $item.data.SSV_FIELD_WIDTH}}px;';
        
        return "
<script id=\"in-tmpl\" type=\"text/x-jquery-tmpl\">
    <input class=\"ROW-ID-$rowId $alias COLUMN-ID-$columnId\" style=\"$style\" id=\"$rowId-$alias\" type=\"text\" name=\"$fullName\" value=\"$value\" original_value=\"$value\" row-id=\"$rowId\" column-id=\"$columnId\"/>
</script>
        ";
    }
}