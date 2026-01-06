<?php

abstract class Minder2_SysScreen_Decorator_JavaScript_TemplatedElement extends Minder2_SysScreen_Decorator_JavaScript_Abstract {
    const TEMPLATE_PREFIX             = 'template-';

    protected function _getTemplate() {
        $templateSelector = $this->getOption('tempalte');
        if (empty($templateSelector)) {
            $templateSelector = $this->_loadTemplate();
            $this->setOption('template', $templateSelector);
        }

        return $templateSelector;
    }

    protected function _getTemplateClass() {
        $templateClass = $this->getOption('templateClass');
        if (empty($templateClass))
            $templateClass = $this->_getDefaultTemplateClass();

        return $templateClass;
    }

    protected function _loadTemplateFile($file, $templateClass) {
        $view = $this->_getView();
        if (is_null($view))
            return '';

        $selector = '$(".' . $templateClass . '")';

        $view->jQueryTemplate($file, array('class' => $templateClass));
        return $selector;
    }

    protected function _loadTemplate() {
        $templateFile = $this->getOption('templateFile');

        if (empty($templateFile))
            return $this->_loadDefaultTemplate();

        return $this->_loadTemplateFile($templateFile, $this->_getTemplateClass());
    }

    protected function _includeRequiredLibraries()
    {
        parent::_includeRequiredLibraries();
        $this->_getView()->headScript(Zend_View_Helper_HeadScript::FILE, $this->_getView()->baseUrl() . '/scripts/jquery.tmpl.js');
    }


    /**
     * @abstract
     * @return string - template selector
     */
    abstract protected function _loadDefaultTemplate();

    /**
     * @return string - template class name
     */
    protected function _getDefaultTemplateClass()
    {
        return self::TEMPLATE_PREFIX . $this->_getVariableName();
    }

}