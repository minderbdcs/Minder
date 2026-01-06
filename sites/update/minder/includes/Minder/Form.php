<?php

class Minder_Form extends Zend_Form {
    /**
     * Default display group class
     * @var string
     */
    protected $_defaultDisplayGroupClass = 'Minder_Form_DisplayGroup';

    public function __construct($options = null)
    {
        $this->addPrefixPath('Minder_Form_Element_', 'Minder/Form/Element/', self::ELEMENT);
        $this->addPrefixPath('Minder_Form_Decorator_', 'Minder/Form/Decorator', self::DECORATOR);
        parent::__construct($options);
    }

    public function getElementsErrorMessages() {
        $result = array();
        /**
         * @var Zend_Form_Element $formElement
         */
        foreach ($this->getElements() as $formElement) {
            if ($formElement->hasErrors()) {
                foreach ($formElement->getMessages() as $error)
                    $result[] = '"' . $formElement->getLabel() . '": ' . $error;
            }
        }

        return $result;
    }

    public function addDisplayGroup(array $elements, $name, $options = null)
    {
        $group = array();
        foreach ($elements as $element) {
            $add = null;
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
            } elseif (isset($this->_displayGroups[$element])) {
                $add = $this->getDisplayGroup($element);
            }

            if (null !== $add) {
                unset($this->_order[$element]);
                $group[] = $add;
            }
        }
        if (empty($group)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for display group');
        }

        $name = (string) $name;

        if (is_array($options)) {
            $options['elements'] = $group;
        } elseif ($options instanceof Zend_Config) {
            $options = $options->toArray();
            $options['elements'] = $group;
        } else {
            $options = array('elements' => $group);
        }

        if (isset($options['displayGroupClass'])) {
            $class = $options['displayGroupClass'];
            unset($options['displayGroupClass']);
        } else {
            $class = $this->getDefaultDisplayGroupClass();
        }

        if (!class_exists($class)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }
        $this->_displayGroups[$name] = new $class(
            $name,
            $this->getPluginLoader(self::DECORATOR),
            $options
        );

        if (!empty($this->_displayGroupPrefixPaths)) {
            $this->_displayGroups[$name]->addPrefixPaths($this->_displayGroupPrefixPaths);
        }

        $this->_order[$name] = $this->_displayGroups[$name]->getOrder();
        $this->_orderUpdated = true;
        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function removeDisplayGroup($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_displayGroups)) {
            /**
             * @var Zend_Form_Element | Minder2_Form_DisplayGroup $element
             */
            foreach ($this->_displayGroups[$name] as $key => $element) {
                if (array_key_exists($key, $this->_elements) || array_key_exists($key, $this->_displayGroups)) {
                    $this->_order[$key]  = $element->getOrder();
                    $this->_orderUpdated = true;
                }
            }
            unset($this->_displayGroups[$name]);

            if (array_key_exists($name, $this->_order)) {
                unset($this->_order[$name]);
                $this->_orderUpdated = true;
            }
            return true;
        }

        return false;
    }
}