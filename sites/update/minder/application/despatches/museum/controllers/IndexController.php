<?php
/**
 * Minder
 *
 * PHP version 5.2.5
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Golovin <sergey.golovin@binary-studio.com>
 * @copyright 2010 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 * 
 * @todo move all controllers connected with despatch menu into despatches module
 *
 */


class Despatches_IndexController extends Minder_Controller_Action_Despatches
{
    public function init() {
        parent::init();
        
        $this->view->pageTitle = 'Despatches';
    }

    public function indexAction() {}

    /*protected function _setupShortcuts() {
        
        $shortcuts = array();
        
        if ($this->minder->isAllowedAsseblyDispatch()=='T') {
            $shortcuts['Assembly']                =   $this->view->url(array('controller' => 'trolley', 'action' => 'index', 'module' => 'default'), null, true);
        } else {
            $shortcuts['Awaiting Checking']       =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-checking', 'module' => 'despatches'), null, true);   
        }

        $shortcuts['Consignment Exit']            =   $this->view->url(array('action' => 'index', 'controller' => 'awaiting-exit', 'module' => 'despatches'), null, true);
        $shortcuts['Scan Exit']                   =   $this->view->url(array('action' => 'index', 'controller' => 'despatch-exit', 'module' => 'despatches'), null, true);
        $shortcuts['Austpost Manifest']           =   $this->view->url(array('action' => 'index', 'controller' => 'austpost-manifest', 'module' => 'despatches'), null, true);
        $shortcuts['Person Details']              =   array(
            'PERSON'                              =>  $this->view->url(array('action' => 'index', 'controller' => 'person', 'module' => 'despatches'), null, true)
        );
        
        
        $this->view->shortcuts = $shortcuts;
        
        return $this;
    }*/
}
