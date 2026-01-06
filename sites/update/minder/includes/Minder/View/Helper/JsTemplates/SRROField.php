<?php
  
class Minder_View_Helper_JsTemplates_SRROField extends Zend_View_Helper_FormElement
{
    public function SRROField() {
        return $this->render();
    }
    
    public function render() {
        $rowId    = '{{= $item.parent.parent.data.row_id}}';
        $columnId = '{{= $item.data.RECORD_ID}}';
        $alias    = '{{= $item.data.SSV_ALIAS}}';

        return "
<script id=\"ro-tmpl\" type=\"text/x-jquery-tmpl\">
    <label class=\"ROW-ID-$rowId $alias COLUMN-ID-$columnId\" >
        {{= \$item.value}}
    </label>    
</script>
        ";
    }
}