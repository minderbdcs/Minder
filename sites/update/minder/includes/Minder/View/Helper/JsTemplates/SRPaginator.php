<?php
  
class Minder_View_Helper_JsTemplates_SRPaginator extends Zend_View_Helper_FormElement
{
    public function SRPaginator() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"paginator-tpl\" type=\"text/x-jquery-tmpl\">
    <div class=\"paginator-container\">
        <label for=\"results_per_page\">View By:</label>
        <select class=\"show_by\" name=\"show_by\">
            {{each $.minderSearchResultCommon.buildShowByRange()}}
                <option {{if (showBy == \$value) }} selected {{/if}} label=\"\${\$value}\" value=\"\${\$value}\">\${\$value}</option>
            {{/each}}
        </select>             
        
        <label for=\"page\">Page:</label>
        <select class=\"pageselector\" name=\"pageselector\">
            {{if \$item.data.maxPages && \$item.data.maxPages < \$item.data.pages}}
                {{each $.minderSearchResultCommon.buildPagesRange(\$item.data.maxPages)}}
                    <option {{if (selectedPage == \$value) }} selected {{/if}} label=\"\${\$value + 1}\" value=\"\${\$value}\">\${\$value + 1}</option>
                {{/each}}
            {{else}}
                {{each $.minderSearchResultCommon.buildPagesRange(\$item.data.pages)}}
                    <option {{if (selectedPage == \$value) }} selected {{/if}} label=\"\${\$value + 1}\" value=\"\${\$value}\">\${\$value + 1}</option>
                {{/each}}
            {{/if}}
        </select>             
    </div>
</script>
        ";
    }
}