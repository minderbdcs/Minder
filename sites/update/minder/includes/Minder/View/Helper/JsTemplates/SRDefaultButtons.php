<?php
  
class Minder_View_Helper_JsTemplates_SRDefaultButtons extends Zend_View_Helper_FormElement
{
    public function SRDefaultButtons() {
        return $this->render();
    }
    
    public function render() {
        return "
<script id=\"buttons-tmpl\" type=\"text/x-jquery-tmpl\">
    <div style=\"clear: both; padding-left: 10px;\">
        <ul class=\"toolbar\">
            <li><input type=\"submit\" onclick=\"return false;\" value=\"REPORT: CSV\" class=\"order-button report_csv_btn\" name=\"report_format\"></li>
            <li><input type=\"submit\" onclick=\"return false;\" value=\"REPORT: XLS\" class=\"order-button report_xls_btn\" name=\"report_format\"></li>
        </ul>
    </div>
</script>
        ";
    }
}