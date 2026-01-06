<?php
  
class Minder_View_Helper_JsTemplates_SRRows extends Zend_View_Helper_FormElement
{
    public function SRRows() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"rows-tmpl\" type=\"text/x-jquery-tmpl\">
    <tr row_id=\"\${row_id}\" class=\"ROW-ID-\${row_id}\">
        {{if \$item.parent.parent.parent.data.canSelect}}
            <th style=\"white-space: nowrap;\">
                <input class=\"row_selector {{= \$item.parent.parent.parent.data.namespace}}\" row_id=\"\${row_id}\" selection_namespace=\"{{= \$item.parent.parent.parent.data.namespace}}\" type=\"checkbox\" {{if \$item.data.checked}}checked=\"chcecked\"{{/if}}>
            </th>                    
        {{/if}}
        
        {{tmpl(\$item.parent.parent.parent.commonMethods.getFields(\$item.parent.parent.parent.data, \$item.parent.parent.data.SST_TAB_NAME)) '#cell-tmpl'}}
        
    </tr>
</script>

        ";
    }
}