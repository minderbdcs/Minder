<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 07.10.11
 * Time: 16:19
 * To change this template use File | Settings | File Templates.
 */
 
class Minder_Form_Decorator_BarcodeUppercase extends Zend_Form_Decorator_Abstract {
    public function render($content) {
        $element = $this->getElement();
        $view = $element->getView();

        $helper = $view->autoloadScript();
        
        // query SYS_SCREEN_VAR
        $sql = "SELECT SSV_INPUT_METHOD FROM SYS_SCREEN_VAR WHERE SS_NAME = 'GLOBAL_INPUT' AND SSV_ALIAS = 'G_I'";
        $row = Minder::getInstance()->fetchAssoc($sql);

        $isUppercase = false;
        if ($row != false) {
            $isUppercase =  (false !== stripos($row["SSV_INPUT_METHOD"], 'UPPERCASE'));
        }

        $str = '$('.'\''.'#barcode'.'\''.").minderBarcodeInput({'isUppercase': " . json_encode($isUppercase) . "});";

        $helper->appendScript($str);
        return $content;
    }
}
