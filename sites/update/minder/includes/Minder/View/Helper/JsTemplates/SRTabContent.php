<?php
  
class Minder_View_Helper_JsTemplates_SRTabContent extends Zend_View_Helper_FormElement
{
    public function SRTabContent() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"tab-content-tmpl\" type=\"text/x-jquery-tmpl\">
    <table class=\"withborder tablesorter data-table-{{= \$item.parent.data.SS_NAME}}\" style=\"margin-bottom: 0pt; clear: both;\" tab_id=\"TAB-{{= \$item.parent.data.SS_NAME + '-' + \$item.parent.data.SST_TAB_NAME}}\">
        <thead>
            <!-- headers starts -->
                <tr>
                    {{if \$item.parent.parent.data.canSelect}}
                        <th style=\"white-space: nowrap;\">
                            <input {{if \$item.parent.parent.data.paginator.totalOnPage == \$item.parent.parent.data.paginator.selectedOnPage}} checked=\"checked\" {{/if}} class=\"select_all_rows {{= \$item.parent.parent.data.namespace}}\" row_id=\"select_all\" selection_namespace=\"{{= \$item.parent.parent.data.namespace}}\"  {{if \$item.parent.parent.data.paginator.selectionMode == 'one'}} disabled=\"disabled\" {{/if}} type=\"checkbox\">
                            &nbsp;&nbsp;&nbsp;
                            <input selection_mode=\"{{= \$item.parent.parent.data.paginator.selectionMode}}\" {{if \$item.parent.parent.data.paginator.selectionMode == 'one'}} checked=\"true\" {{/if}} class=\"switch_selection {{= \$item.parent.parent.data.namespace}}\" selection_namespace=\"{{= \$item.parent.parent.data.namespace}}\" type=\"radio\">
                        </th>                    
                    {{/if}}
                    {{tmpl(\$item.parent.parent.commonMethods.getFields(\$item.parent.parent.data, \$item.parent.data.SST_TAB_NAME)) '#column-headers-tmpl'}}
                </tr>
            <!-- headers ends -->
        </thead>
        <tbody class='data-set {{= \$item.parent.parent.data.namespace}}'>
            {{if \$item.parent.parent.data.dataset.length > 0}}
                {{tmpl(\$item.parent.parent.data.dataset) '#rows-tmpl'}}
            {{else}}
                <tr>
                    {{if \$item.parent.parent.data.canSelect}}
                        <th style=\"white-space: nowrap;\">&nbsp;</th>                    
                    {{/if}}
                    {{each \$item.parent.parent.commonMethods.getFields(\$item.parent.parent.data, \$item.parent.data.SST_TAB_NAME)}}
                        <td>&nbsp;</td>
                    {{/each}}
                </tr>
            {{/if}}
        </tbody>
    </table>
</script>
        ";
    }
}