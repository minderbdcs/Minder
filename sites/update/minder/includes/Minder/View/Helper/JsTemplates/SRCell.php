<?php
  
class Minder_View_Helper_JsTemplates_SRCell extends Zend_View_Helper_FormElement
{
    public function SRCell() {
        return $this->render();
    }
    
    public function render() {
        /** @noinspection PhpUndefinedVariableInspection */
        return "
<!--suppress HtmlUnknownAttribute -->
<script type=\"text/x-jquery-tmpl\" id=\"cell-tmpl\">
    {{if typeof \$item.parent.data.values[\$item.data.COLOR_FIELD_ALIAS] == 'undefined'}}
        <td style=\"width: \${SSV_FIELD_WIDTH}px;\" column-id=\"\${RECORD_ID}\">
    {{else}}
        <td style=\"width: \${SSV_FIELD_WIDTH}px; background-color: {{= \$item.parent.data.values[\$item.data.COLOR_FIELD_ALIAS].value}}\" column-id=\"\${RECORD_ID}\">
    {{/if}}
        {{if SSV_INPUT_METHOD == 'RO'}}
            {{tmpl(\$item.data, \$item.parent.data.values[\$item.data.SSV_ALIAS]) '#ro-tmpl'}}
        {{else SSV_INPUT_METHOD == 'IN'}}
            {{tmpl(\$item.data, \$item.parent.data.values[\$item.data.SSV_ALIAS]) '#in-tmpl'}}
        {{else SSV_INPUT_METHOD == 'DD'}}
            {{tmpl(\$item.data, \$item.parent.data.values[\$item.data.SSV_ALIAS]) '#dd-tmpl'}}
        {{else SSV_INPUT_METHOD == 'DP'}}
            {{tmpl(\$item.data, \$item.parent.data.values[\$item.data.SSV_ALIAS]) '#dp-tmpl'}}
        {{/if}}
    </td>
</script>
        ";
    }
}