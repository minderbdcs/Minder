<?php
class Minder_View_Helper_Messenger
{
    protected $_messenger;

    public function __construct()
    {
        $this->_messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
    }

    public function messenger()
    {
        return $this;
    }

    /**
    * Give direct access to $_messenger property
    * 
    */
    public function getMessenger() 
    {
        return $this->_messenger;
    }

    public function errors($area = null)
    {
        if ($area) {
            $area .= '_errors';
        }
        return $this->_getHtml($area);
    }

    public function messages($area = null)
    {
        if ($area) {
            $area .= '_messages';
        }
        return $this->_getHtml($area);
    }

    public function warnings($area = null)
    {
        if ($area) {
            $area .= '_warnings';
        }
        return $this->_getHtml($area);
    }

    public function all($area = null)
    {
        $namespaces = array('errors', 'warnings', 'messages');
        $html = '';
        if ($area) {
            foreach ($namespaces as $namespace) {
                $html .= $this->_getHtml($area . '_' . $namespace);
            }
        } else {
            foreach ($namespaces as $namespace) {
                $html .= $this->_getHtml($namespace);
            }
        }
        return $html;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    protected function _getHtml($namespace)
    {
        $messages = $this->_messenger->setNamespace($namespace)->getCurrentMessages();
        if (empty($messages)) {
            $messages = $this->_messenger->getMessages();
        }

        $this->_messenger->clearMessages();
        $this->_messenger->clearCurrentMessages();

        if (!count($messages)) {
            return '';
        }

        if (strpos($namespace, '_') !== false) {
            $class = substr(strrchr($namespace, '_'), 1);
        } else {
            $class = $namespace;
        }

        $html = '<ul class="' . $this->view->escape($class) . '">';
        foreach ($messages as $message) {
            $html .= '<li>' . $message . '</li>';
        }
        $html .= '</ul>' . PHP_EOL;
        return $html;
    }
}
