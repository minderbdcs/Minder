<?php

interface Minder_Page_FormFiller_Interface {
    /**
     * @abstract
     * @param Zend_Form $form
     * @return Zend_Form
     */
    function fillDefaults(Zend_Form $form);

    /**
     * @abstract
     * @param Zend_Form $form
     * @return Zend_Form
     */
    function fillMultiOptions(Zend_Form $form);

    /**
     * @abstract
     * @param Zend_Form $form
     * @return array
     */
    function getMultiOptions(Zend_Form $form);
}