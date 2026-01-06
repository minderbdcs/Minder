<?php

class Minder_View_Helper_JsTemplates_VarianceSRTabs extends Zend_View_Helper_FormElement
{
    public function varianceSRTabs() {
        return $this->render();
    }

    public function render() {
        return "
<script id=\"tabs-tmpl\" type=\"text/x-jquery-tmpl\">
    <div class=\"ui-tabs-panel\" id=\"TAB-{{= SS_NAME + '-' + SST_TAB_NAME}}\">
        <!-- tab content starts -->
            {{tmpl(\$item.parent.data) '#variance-tab-header-tmpl'}}

            {{tmpl(\$item.data) '#tab-content-tmpl'}}

            {{if \$item.parent.data.hasFooter}}
                {{tmpl(\$item.parent.data) '#footer-tmpl'}}
            {{/if}}

            {{if \$item.parent.data.hasButtons}}
                {{tmpl(\$item.parent.data) '#buttons-tmpl'}}
            {{/if}}
        <!-- tab content ends -->
    </div>
</script>
        ";
    }
}