<?php

class Minder_View_Helper_JsTemplates_SRButtons extends Zend_View_Helper_FormElement
{
    public function SRButtons() {
        return $this->render();
    }

    public function render() {
        return "
<script id=\"buttons-tmpl\" type=\"text/x-jquery-tmpl\">
    <div style=\"clear: both; padding-left: 10px;\">
        <ul class=\"toolbar\">
            {{each \$item.data.buttons}}
            <li>
                <input
                    type=\"button\"
                    onclick=\"return false;\"
                    class=\"green-button SCREEN_BUTTON_{{= \$value.RECORD_ID}}\"
                    name=\"{{= \$value.SSB_BUTTON_NAME}}\"
                    value=\"{{= \$value.SSB_TITLE}}\"
                    title=\"{{= \$value.SSB_MOUSE_OVER_MESSAGE}}\"
                    data-barcode=\"{{= \$value.SSB_SCANNING_CODE}}\"
                />
            </li>
            {{/each}}
        </ul>
    </div>
</script>
        ";
    }
}