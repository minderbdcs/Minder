<?php
  
class Minder_View_Helper_JsTemplates_SRDefaultFooter extends Zend_View_Helper_FormElement
{
    public function SRDefaultFooter() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"footer-tmpl\" type=\"text/x-jquery-tmpl\">
    <table width=\"100%\" class=\"withborder\">
        <tbody><tr>
            <td>Total <span class=\"total-container {{= \$item.data.namespace}}\">{{= \$item.data.paginator.totalRows}}</span> records. Show from {{= \$item.data.paginator.shownFrom}} to {{= \$item.data.paginator.shownTill}}</td>
        </tr>
    </tbody></table>
</script>
        ";
    }
}