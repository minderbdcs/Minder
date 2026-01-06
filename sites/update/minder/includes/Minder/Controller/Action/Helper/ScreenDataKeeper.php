<?php

class Minder_Controller_Action_Helper_ScreenDataKeeper extends Zend_Controller_Action_Helper_Abstract 
{
    protected $session   = null;
    
    protected $namespace = 'default';
    protected $instance  = '';
    protected $fieldsPrefix = 'SD-';

    public function init() {
        if (!Zend_Registry::isRegistered('screenDataSession')) {
            Zend_Registry::set('screenDataSession', new Zend_Session_Namespace('search'));
        }
        
        $this->session = Zend_Registry::get('screenDataSession');
    }
    
    public function setNamespace($namespace = 'default') {
        if (empty($namespace)) {
            throw new Minder_Controller_Action_Helper_ScreenDataKeeper_Exception('Namespace couldn\'t be null.');
        }
        
        $this->namespace = $namespace;
        
        return $this;
    }
    
    public function setInstanceId($instance = '') {
        if (empty($instance)) {
            throw new Minder_Controller_Action_Helper_ScreenDataKeeper_Exception('Instance ID couldn\'t be null.');
        }
        
        $this->instance = $instance;

        return $this;
    }
    
    public function setFieldsPrefix($prefix = '') {
        if (!is_string($prefix)) {
            throw new Minder_Controller_Action_Helper_ScreenDataKeeper_Exception('Prefix should be string.');
        }
        
        $this->fieldsPrefix = $prefix;

        return $this;
    }
    
    public function direct($action = 'load', $fieldsDesc, $instance = null, $namespace = null, $prefix = null) {
        switch (strtolower($action)) {
            case 'load' :
                return $this->loadData($fieldsDesc, $instance, $namespace, $prefix);
            case 'save' :
                return $this->saveData($fieldsDesc, $instance, $namespace, $prefix);
            default:
                throw new Minder_Controller_Action_Helper_ScreenDataKeeper_Exception("Unsupported action '$action'. Valid actions are 'load', 'save'");
        }
        
        return array();
    }
    
    public function saveData($fieldsDesc, $instance = null, $namespace = null, $prefix = null) {
        if (is_null($instance))
            $this->setInstanceId($this->instance);
        else
            $this->setInstanceId($instance);
            
        if (is_null($namespace))
            $this->setNamespace($this->namespace);
        else 
            $this->setNamespace($namespace);
            
        if (is_null($prefix)) 
            $this->setFieldsPrefix($this->fieldsPrefix);
        else 
            $this->setFieldsPrefix($prefix);
            
        if (!isset($this->session->data[$this->instance][$this->namespace]))
            $this->session->data[$this->instance][$this->namespace] = array();
            
        $savedData = $this->session->data[$this->instance][$this->namespace];
        $params = $this->getRequest()->getParams();
        $params = array_change_key_case($params, CASE_UPPER);
        foreach ($fieldsDesc as &$fieldDesc) {
            $tmpFieldId = $fieldDesc['SSV_ALIAS'];
            if (isset($fieldDesc['SSV_TABLE'])) 
                $tmpFieldId = $fieldDesc['SSV_TABLE'] . "-" . $tmpFieldId;
            
            if (!empty($this->fieldsPrefix))
                $tmpFieldId = $this->fieldsPrefix . '-' . $tmpFieldId;
                
            $value = null;
                
            if (isset($params[$tmpFieldId])) {
                $value = $params[$tmpFieldId];
                $value = is_array($value) ? array_map(trim, $value) : trim($value);
            } 
            
            if (!empty($value) || is_numeric($value)) {
                $fieldDesc['ENTERED_VALUE'] = $params[$tmpFieldId];
            } else {
                unset($fieldDesc['ENTERED_VALUE']);
            }
            
            $savedData[$fieldDesc['SSV_ALIAS']] = $fieldDesc;
        }
        
        $this->session->data[$this->instance][$this->namespace] = $savedData;
        
        return $fieldsDesc;
    }
    
    public function loadData($fieldsDesc, $instance = null, $namespace = null, $prefix = null) {
        if (is_null($instance))
            $this->setInstanceId($this->instance);
        else
            $this->setInstanceId($instance);
            
        if (is_null($namespace))
            $this->setNamespace($this->namespace);
        else 
            $this->setNamespace($namespace);
            
        if (is_null($prefix)) 
            $this->setFieldsPrefix($this->fieldsPrefix);
        else 
            $this->setFieldsPrefix($prefix);
        
        if (!isset($this->session->data[$this->instance][$this->namespace]))
            $this->session->data[$this->instance][$this->namespace] = array();
            
        $savedData = $this->session->data[$this->instance][$this->namespace];
        foreach ($fieldsDesc as &$fieldDesc) {

            if (isset($savedData[$fieldDesc['SSV_ALIAS']]['ENTERED_VALUE'])) {
                
                $fieldDesc['ENTERED_VALUE'] = $savedData[$fieldDesc['SSV_ALIAS']]['ENTERED_VALUE'];
            } 
        }
        
        return $fieldsDesc;
    }

    public function getField($fieldName, $instance = null, $namespace = null, $prefix = null) {
        if (is_null($instance))
            $this->setInstanceId($this->instance);
        else
            $this->setInstanceId($instance);
            
        if (is_null($namespace))
            $this->setNamespace($this->namespace);
        else 
            $this->setNamespace($namespace);
            
        if (is_null($prefix)) 
            $this->setFieldsPrefix($this->fieldsPrefix);
        else 
            $this->setFieldsPrefix($prefix);
            
        if (!empty($this->fieldsPrefix))
            $fieldName = $this->fieldsPrefix . '-' . $fieldName;
        
        if (!isset($this->session->data[$this->instance][$this->namespace]))
            $this->session->data[$this->instance][$this->namespace] = array();
            
        $savedData = $this->session->data[$this->instance][$this->namespace];
        if (isset($savedData[$fieldName]))
            return $savedData[$fieldName];
        
        return null;
    }
    
    public function getParam($paramName, $instance = null, $namespace = null, $prefix = null) {
        $fieldDesc = $this->getField($paramName, $instance, $namespace, $prefix);
        if (is_null($fieldDesc))
            return null;
            
        if (isset($fieldDesc['ENTERED_VALUE'])) 
            return $fieldDesc['ENTERED_VALUE'];
            
        return null;
    }
    
    public function getFields($instance = null, $namespace = null, $prefix = null) {
        if (is_null($instance))
            $this->setInstanceId($this->instance);
        else
            $this->setInstanceId($instance);
            
        if (is_null($namespace))
            $this->setNamespace($this->namespace);
        else 
            $this->setNamespace($namespace);
            
        if (is_null($prefix)) 
            $this->setFieldsPrefix($this->fieldsPrefix);
        else 
            $this->setFieldsPrefix($prefix);
        
        if (!isset($this->session->data[$this->instance][$this->namespace]))
            $this->session->data[$this->instance][$this->namespace] = array();
        
        $savedData = $this->session->data[$this->instance][$this->namespace];
        
        if (!empty($this->fieldsPrefix)) {
            $fields = array();
            
            $stripLen = strlen($this->fieldsPrefix) + 1;
            
            foreach ($savedData as $fieldName => $fieldDesc) {
                $tmpFieldName = substr($fieldName, $stripLen);
                $fields[$tmpFieldName] = $fieldDesc;
            }
            
            return $fields;
        } else {
            return $savedData;
            
        }
    }
    
    public function getParams($instance = null, $namespace = null, $prefix = null) {
        $fields = $this->getFields($instance, $namespace, $prefix);
        
        $params = array();
        foreach ($fields as $fieldName => $fieldDesc) {
            $params[$fieldName] = null;
            
            if (isset($fieldDesc['ENTERED_VALUE'])) 
                $params[$fieldName] = $fieldDesc['ENTERED_VALUE'];
        }
        
        return $params;
    }

}

class Minder_Controller_Action_Helper_ScreenDataKeeper_Exception extends Minder_Exception {}
