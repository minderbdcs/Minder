<?php

class Minder_Controller_Action_Helper_DatasetToJson extends Zend_Controller_Action_Helper_Abstract {

    /**
     * @param Zend_View_Interface $view
     * @return Minder_Controller_Action_Helper_DatasetToJson|string
     */
    public function datasetToJson(Zend_View_Interface $view = null) {
        return is_null($view) ? $this : $this->render($view);
    }

    /**
     * @param Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null) {
        $view = is_null($view) ? $this->getActionController()->view : $view;
        $data = get_object_vars($view);

        $unsetFields = array(
            'minder' => 'minder',
            'shortcuts' => 'shortcuts',
            'topMenu' => 'topMenu',
            'baseUrl' => 'baseUrl',
        );

        $data = array_diff_key($data, $unsetFields);

        return json_encode($data);
    }

    function __toString()
    {
        return __CLASS__;
    }


}