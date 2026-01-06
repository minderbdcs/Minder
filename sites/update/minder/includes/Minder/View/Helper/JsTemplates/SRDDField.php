<?php
  
class Minder_View_Helper_JsTemplates_SRDDField extends Zend_View_Helper_FormElement
{
    public function SRDDField() {
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
<script id=\"dd-tmpl\" type=\"text/x-jquery-tmpl\">
    <select class=\"ROW-ID-$rowId $alias COLUMN-ID-$columnId\" style=\"$style\" id=\"$rowId-$alias\" type=\"text\" name=\"$fullName\" original_value=\"$value\" row-id=\"$rowId\" column-id=\"$columnId\">
    {{tmpl(\$item.dropdown) '#dd-option-tmpl'}}
    </select>
</script>

<script id=\"dd-option-tmpl\" type=\"text/x-jquery-tmpl\">
    <option {{each \$item.data}}{{= \$index}}=\"{{= \$value}}\"{{/each}} value=\"{{= \$item.data.value}}\" {{if \$item.parent.value == \$item.data.value}}selected=\"selected\"{{/if}}>{{= \$item.data.label}}</option>
</script>


        ";
    }
}