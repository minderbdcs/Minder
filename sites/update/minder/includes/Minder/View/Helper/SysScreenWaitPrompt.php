<?php
  
class Minder_View_Helper_SysScreenWaitPrompt extends Zend_View_Helper_FormElement
{
    public function SysScreenWaitPrompt($prompt = '') {
        return $this->render($prompt);
    }
    
    public function render($prompt) {
        $defaultPrompt = 'Loading data. Please wait...';
        
        $prompt = empty($prompt) ? $defaultPrompt : $prompt;
        
        return "<div class=\"mdr-wait-prompt\"><center><h2>" . $prompt . "</h2></center></div>";
    }
}
