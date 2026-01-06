<?php

/**
 * TransferController
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
 
class MappingController extends Minder_Controller_Action {
    public function init(){
        parent::init();

        if ((!$this->minder->isAdmin) && $this->minder->isInventoryOperator) {
            $this->_redirector->setCode(303)
                              ->goto('index', 'index', '', array());
            return;
        }
    }
    
    public function indexAction(){ 
        
    }
}
    
    
