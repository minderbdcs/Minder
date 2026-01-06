<?php

/**
 * @throws Minder_Exception
 * @property boolean $existed
 */
class Minder2_Model implements Minder2_Model_Interface {
    const DECORATOR = 'DECORATOR';

    protected $_dbFields = array();
    protected $_fields = array();

    protected $_decorators = array();
    protected $_loaders    = array();

    protected $_view       = null;

    function __construct($fields = array())
    {
        $this->_setDbFields($fields);
    }

    protected function _setDbFields($fields) {
        $this->_dbFields = $fields;
        return $this;
    }

    function _getFieldValue($name) {
        if (isset($this->_fields[$name]))
            return $this->_fields[$name];

        if (isset($this->_dbFields[$name]))
            return $this->_dbFields[$name];

        return null;
    }

    function _getBooleanFieldsValue($name) {
        if (isset($this->_fields[$name]))
            return $this->_fields[$name];

        if (isset($this->_dbFields[$name]))
            return $this->_dbFields[$name] == 'T';

        return false;
    }

    function __get($name)
    {
        switch ($name) {
            case 'existed':
                return $this->_getBooleanFieldsValue($name);
        }
        return $this->_getFieldValue($name);
    }

    function __set($name, $value)
    {
        switch ($name) {
            case 'existed':
                return $this->_setBooleanFieldValue($name, $value);
        }
        return $this->_fields[$name] = $value;
    }

    function __isset($name)
    {
        return isset($this->_dbFields[$name]) || isset($this->_fields[$name]) ;
    }


    protected function _setBooleanFieldValue($name, $value) {
        if (is_string($value))
            $value = ($value == 'true') ? true : false;
        
        $this->_fields[$name] = $value;
    }

    function setFieldValue($name, $value) {
        $this->$name = $value;
    }

    function getFieldValue($name) {
        return $this->$name;
    }

    function getFields() {
        $fields = array_merge(array_keys($this->_dbFields), array_keys($this->_fields));

        $result = array();
        foreach ($fields as $field)
            $result[$field] = $this->$field;

        return $result;
    }

    function setFields($fields) {
        foreach ($fields as $name => $value)
            $this->$name = $value;
    }

    /**
     * @param string|Zend_Form_Decorator_Abstract $decorator
     * @param array $options
     * @return void
     */
    function addDecorator($decorator, array $options = array()) {
        if ($decorator instanceof Zend_Form_Decorator_Abstract) {
            $decorator->setOptions(array_merge($decorator->getOptions(), $options));
            $this->_decorators[$decorator->getOption('name')] = $decorator;
            return;
        } elseif (is_string($decorator)) {
            $this->_decorators[$decorator] = $options;
            return;
        }

        throw new Minder_Exception('$decorator should be string or Zend_Form_Decorator_Abstract instance.');
    }

    public function addPrefixPath($prefix, $path, $type) {
        $type = strtoupper($type);
        switch ($type) {
            case self::DECORATOR:
                $loader = $this->_getPluginLoader($type);
                $loader->addPrefixPath($prefix, $path);
                return $this;
            default:
                throw new Minder_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
        }
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    protected  function _getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (!isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $this->_loaders[$type] = new Zend_Loader_PluginLoader(
                        array('Zend_Form_Decorator_' => 'Zend/Form/Decorator/')
                    );
                    break;
                default:
                    throw new Minder_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }

        }

        return $this->_loaders[$type];
    }

    /**
     * @param array $options
     * @return Zend_Form_Decorator_Interface
     */
    function _loadDecorator(array $options) {
        $decorator = $options['decorator'];
        $class = $this->_getPluginLoader(self::DECORATOR)->load($decorator);

        return new $class($options);
    }

    /**
     * @param string $name
     * @return null|Zend_Form_Decorator_Interface
     */
    function getDecorator($name) {
        if (!isset($this->_decorators[$name]))
            return null;

        $decorator = $this->_decorators[$name];
        if ($decorator instanceof Zend_Form_Decorator_Interface)
            return $decorator;

        return $this->_loadDecorator($decorator);
    }

    /**
     * Remove a single decorator
     *
     * @param  string $name
     * @return bool
     */
    public function removeDecorator($name)
    {
        $decorator = $this->getDecorator($name);
        if ($decorator) {
            if (array_key_exists($name, $this->_decorators)) {
                unset($this->_decorators[$name]);
            } else {
                $class = get_class($decorator);
                if (!array_key_exists($class, $this->_decorators)) {
                    return false;
                }
                unset($this->_decorators[$class]);
            }
            return true;
        }

        return false;
    }

    function __toString()
    {
        try {
            $content = '';
            foreach ($this->_decorators as $name => $decorator) {
                $decorator = $this->getDecorator($name);
                $decorator->setElement($this);
                $content = $decorator->render($content);
            }
        } catch (Exception $e) {
            $content = "Error: " . $e->getMessage();
        }

        return $content;
    }

    /**
     * Retrieve view object
     *
     * If none registered, attempts to pull from ViewRenderer.
     *
     * @return Zend_View_Interface|null
     */
    public function getView()
    {
        if (null === $this->_view) {
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $this->setView($viewRenderer->view);
        }

        return $this->_view;
    }

    /**
     * @param Zend_View_Interface $view
     * @return void
     */
    function setView(Zend_View_Interface $view) {
        $this->_view = $view;
    }

    /**
     * @return Minder2_Model_StateManager
     */
    protected function _getStateManager() {
        return Minder2_Model_StateManager::getInstance();
    }

    /**
     * @return mixed
     */
    function getState()
    {
        return $this->getFields();
    }

    /**
     * @param mixed $state
     * @return Minder2_Model_Interface
     */
    function setState($state)
    {
        $this->setFields($state);
        return $this;
    }


    public function restoreState() {
        return $this->_restoreState();
    }

    public function saveState() {
        return $this->_saveState();
    }

    protected function _saveState() {
        $this->_getStateManager()->saveState($this);
        return $this;
    }

    protected function _restoreState() {
        $this->_getStateManager()->restoreState($this);
        return $this;
    }

    /**
     * @return string
     */
    function getName()
    {
        throw new Exception('Not implemented.');
    }

    /**
     * @return int
     */
    function getOrder()
    {
        throw new Exception('Not implemented.');
    }

    /**
     * @return string
     */
    function getStateId()
    {
        throw new Exception('Not implemented.');
    }
}