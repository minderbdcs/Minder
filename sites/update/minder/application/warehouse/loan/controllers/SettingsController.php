<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 */

/**
 * @category  Minder
 * @package   Minder
 * @author    Strelnikov Evgeniy <strelnikov.evgeniy@binary-studio.com@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Warehouse_SettingsController extends Zend_Controller_Action
{
    protected $_session;

    public function init()
    {
        $this->minder = Minder::getInstance();
        if ($this->minder->userId == null) {
            $this->_redirector = $this->_helper->getHelper('Redirector');
            $this->_redirector->setCode(303)
                              ->goto('login', 'user', '', array());
            return;
        }
        $this->_session = new Zend_Session_Namespace('warehouse');
    }

    public function savePositionAction()
    {
        $this->view->result = true;

        $params = array(
            'table_id'   => $this->_getParam('table_id'),
            'controller' => $this->_getParam('ctrl'),
            'action'     => $this->_getParam('act'),
            'src'        => $this->_getParam('src'),
            'dst'        => $this->_getParam('dst')
        );

        // Check parameters for change position.
        if (empty($params['controller'])) {
            $this->view->result = false;
        } else {
            foreach ($params as $key => $value) {
                if (empty($value)) {
                    $this->view->result = false;
                    break;
                }
            }
        }

        // Save columns position in session.
        if ($this->view->result) {
            if (!isset($this->_session->headers[$params['controller']][$params['action']][$params['table_id']])) {
                $this->view->result = false;
            } else {
                $headers = $this->_session->headers[$params['controller']][$params['action']][$params['table_id']];

                $keys = array_keys($headers);
                if (false !== array_search($params['src'], $keys) && false !== array_search($params['dst'], $keys)) {
                    $result = array();
                    $src = $headers[$params['src']];
                    $dst = $headers[$params['dst']];
                    foreach ($headers as $key => $value) {
                        if ($key == $params['src']) {
                            $result[$params['dst']] = $dst;
                        } elseif ($key == $params['dst']) {
                            $result[$params['src']] = $src;
                        } else {
                            $result[$key] = $value;
                        }
                    }

                    $this->_session->headers[$params['controller']][$params['action']][$params['table_id']] = $result;
                    $this->view->result = $this->minder->saveEnvSession($this->minder->userId,
                        'warehouse',
                        serialize($this->_session->getIterator()));
                } else {
                    $this->view->result = false;
                }
            }
        }
    }
}
