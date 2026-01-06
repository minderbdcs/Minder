<?php
  
class Minder_View_Helper_JsTemplates_SRTabContainer extends Zend_View_Helper_FormElement
{
    public function SRTabContainer() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"tab-container-tmpl\" type=\"text/x-jquery-tmpl\">
    <div id=\"\${tabContainerId}\" class=\"ui-tabs-container\">
        <ul class=\"ui-tabs-nav\">
            {{tmpl(tabs) '#tabs-headers-tmpl'}}
        </ul>
        <div class=\"ui-tabs-panel-header\">
            <div class=\"sys-screen-title\" title=\"\${sysScreenName}\">\${sysScreenCaption}</div>
            {{if canSelect}}
                <div class=\"paginator-container\">
                    <label disablefor=\"1\">Select All Rows on all pages</label>
                    <input name=\"select_complete\" {{if \$item.data.paginator.totalRows == \$item.data.paginator.selectedRows}} checked=\"checked\" {{/if}} {{if \$item.data.paginator.selectionMode == 'one'}} disabled=\"disabled\" {{/if}} id=\"select_complete\" row_id=\"select_complete\" class=\"select_complete \${namespace}\" selection_namespace=\"\${namespace}\" type=\"checkbox\">
                </div>
            {{/if}}
            {{if usePagination}}
                {{tmpl(paginator) '#paginator-tpl'}}
            {{/if}}
            
            {{if hasHeader}}
                {{tmpl(\$item.data) '#header-tmpl'}}
            {{/if}}
        </div>
        
        {{tmpl(tabs) '#tabs-tmpl'}}
    </div>
</script>
        ";
    }
}