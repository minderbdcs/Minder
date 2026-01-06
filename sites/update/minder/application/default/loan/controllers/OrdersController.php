<?php
/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Minder
 * @author    Dmitriy Suhinin <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 * @todo      refactoring  
 *
 */


class OrdersController extends Minder_Controller_Action
{
    public function init() {
        parent::init();
        if ((!$this->minder->isAdmin) && $this->minder->isInventoryOperator) {
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }
    }

    public function indexAction()
    {

    }

    /*protected function _setupShortcuts() {
        
        $shortcuts = array(
            'Sales Orders' => array(
                'Sales Orders'             => $this->view->url(array('controller' => 'pick-order2', 'action' => 'index'), null, true),
                'Sales Invoices'           => $this->view->url(array('controller' => 'pick-invoice', 'action' => 'index'), null, true),
                'New Order - Full Screen'  => $this->view->url(array('controller' => 'pick-order2', 'action' => 'new', 'pick_order_type' => 'SO'), null, true),
                //'Fast Sales Order' => $this->view->url(array('module' => 'warehouse', 'controller' => 'products', 'action' => 'index', 'from' => 'fso', 'without' => 1), null, true),
                'Import Mapped Order'      => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true),
            ),
            'Transfer Orders' => array(
                'Transfer Orders'   => $this->view->url(array('controller' => 'transfer-order', 'action' => 'index'), null, true),
                'Import Mapped Order' => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true)
            ),
            'Purchase Orders' => array(
//            	'New Purchase Order' => $this->view->url(array('controller' => 'purchase-order', 'action' => 'edit'), null, true),
                'Purchase Orders'       => $this->view->url(array('controller' => 'purchase-order', 'action' => 'index'), null, true),
                'Import Mapped Order' => $this->view->url(array('controller' => 'mapping', 'action' => 'index'), null, true)
            ),
            'Person Details'            => array(
                'PERSON'                =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'default'), null, true)
            )
    	);
        
        
        $this->view->shortcuts = $shortcuts;
        
        return $this;
    }*/
}
