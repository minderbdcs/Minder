<?php

class Minder_View_Helper_JsTemplates_VarianceSRTabContainer extends Zend_View_Helper_FormElement
{
    public function varianceSRTabContainer() {
        return $this->render();
    }

    public function render() {
        return "
<script id=\"tab-container-tmpl\" type=\"text/x-jquery-tmpl\">
    <div id=\"\${tabContainerId}\" class=\"ui-tabs-container\">
        {{tmpl(tabs) '#tabs-tmpl'}}
    </div>
</script>
        ";
    }
}