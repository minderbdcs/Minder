<?php

class Minder_Page_FormFiller_EditForm_Default implements Minder_Page_FormFiller_Interface {
    /**
     * @param Zend_Form $form
     * @return Zend_Form
     */
    function fillDefaults(Zend_Form $form)
    {
        /**
         * @var Zend_Form_Element $element
         */
        foreach ($form->getElements() as $element) {
            $element->setValue($this->_getDefaultValue($element, $form));
        }

        return $form;
    }

    function _fetchOptions($sql) {
        $dataSource = new Minder_SysScreen_DataSource_Sql();
        $dataSource->setSql($sql);
        $dataSource->fetchAllAssoc(new Minder_SysScreen_DataSource_SystemParameterProvider());

        return $dataSource->fetchAllAssoc(new Minder_SysScreen_DataSource_SystemParameterProvider());
    }

    function _adoptOptions($options) {
        if (count($options) < 1)
            return $options;

        $firstRow = array_shift($options);
        array_unshift($options, $firstRow);

        $rowKeys = array_keys($firstRow);

        $valueIndex = isset($firstRow['VALUE']) ? 'VALUE' : $rowKeys[0];
        $labelIndex = isset($firstRow['LABEL']) ? 'LABEL' : (isset($rowKeys[1]) ? $rowKeys[1] : $rowKeys[0]);

        foreach ($options as &$option) {
            $option['VALUE'] = $option[$valueIndex];
            $option['LABEL'] = $option[$labelIndex];
        }

        return $options;
    }

    function _getMultiOptionsFromSql($sql, $formElement, $form) {
        return $this->_adoptOptions($this->_fetchOptions($sql));
    }

    function _getMultiOptionsFromFun($expression, $formElement, $form) {
        $options = array();
        $args = array();
        $methodName = substr($expression, 0, strpos($expression, '('));
        eval('$args = array' . strstr($expression, '(') . ';');

        if (is_callable(array(Minder::getInstance(), $methodName))) {
            $options = call_user_func_array(array(Minder::getInstance(), $methodName), $args);
        }

        $result = $options;

        if (count($options) > 0) {
            $firstRow = array_shift($options);

            if (!is_array($firstRow)) {
                $result = array();
                array_unshift($options, $firstRow);
                foreach ($options as $key => $value) {
                    $result[] = array('VALUE' => $key, 'LABEL' => $value);
                }
            }
        }

        return $result;
    }

    /**
     * @param Zend_Form_Element $element
     * @param string $option
     * @return mixed
     */
    protected function _getElementMinderOption($element, $option) {
        $minderOptions = $element->getAttrib('minderOptions');
        if (!is_array($minderOptions))
            return null;

        return isset($minderOptions[$option]) ? $minderOptions[$option] : null;
    }

    /**
     * @param Zend_Form $form
     * @return Zend_Form
     */
    function fillMultiOptions(Zend_Form $form)
    {
        foreach ($this->getMultiOptions($form) as $elementName => $options) {
            $form->getElement($elementName)->setMultiOptions($options);
        }

        return $form;
    }

    /**
     * @param Zend_Form $form
     * @return array
     */
    function getMultiOptions(Zend_Form $form) {
        $result = array();

        /**
         * @var Zend_Form_Element_Multi $formElement
         */
        foreach ($form->getElements() as $formElement) {
            $options = $this->_getElementMultiOptions($formElement, $form);

            if (is_array($options))
                $result[$formElement->getName()] = $options;
        }

        return $result;
    }

    /**
     * @param Zend_Form_Element $formElement
     * @param Zend_Form $form
     * @return array|null
     */
    protected function _getElementMultiOptions($formElement, $form)
    {
        $options = null;

        $dropDownSql = $this->_getElementMinderOption($formElement, 'SSV_DROPDOWN_SQL');

        if (empty($dropDownSql))
            return $options;

        switch (strtoupper(trim($this->_getElementMinderOption($formElement, 'SSV_DROPDOWN_DATA_FROM')))) {
            case 'SQL':
                $options = $this->_getMultiOptionsFromSql($dropDownSql, $formElement, $form);
                break;
            case 'FUN':
                $options = $this->_getMultiOptionsFromFun($dropDownSql, $formElement, $form);
                break;
        }
        return $options;
    }

    /**
     * @param Zend_Form_Element $element
     * @param Zend_Form $form
     * @return string
     */
    private function _getDefaultValue($element, $form)
    {
        if (empty($element->minderOptions) || !is_array($element->minderOptions))
            return '';

        if (empty($element->minderOptions['SSV_DROPDOWN_DEFAULT']))
            return '';

        try {
            return Minder::getInstance()->findValue($element->minderOptions['SSV_DROPDOWN_DEFAULT']);
        } catch (Exception $e) {
            //todo: add logging
        }
        return '';
    }


}