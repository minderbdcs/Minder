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

    public function indexAction() {
    }
}
