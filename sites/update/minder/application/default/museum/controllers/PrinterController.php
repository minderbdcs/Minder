<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Minder_Printer_Controller_Action
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class PrinterController extends Zend_Controller_Action
{
    public function init()
    {
        $this->minder = Minder::getInstance();

        $this->_redirector = $this->_helper->getHelper('Redirector');
        if ($this->minder->userId == null) {
            $this->_redirector->setCode(303)
                              ->goto('login', 'user', '', array());
            return;
        }

        $this->initView();
        $this->view->addHelperPath(ROOT_DIR . '/includes/helpers/', 'Minder_View_Helper');
        $this->view->minder         = $this->minder;
        $this->view->flashMessenger = $this->_helper->getHelper('flashMessenger');

        $this->view->baseUrl = $this->view->url(array('action' => 'index', 'controller' => 'index'), '', true);

        $this->_controller = $this->getRequest()->getControllerName();
        $this->_action = $this->getRequest()->getActionName();

        /**
         * @todo rewrite for new Controller
         */
        $namespace  = 'warehouse';
        if (!Zend_Session::namespaceIsset($namespace)) {
            $this->session = new Zend_Session_Namespace($namespace);
            $session = $this->_getEvnSession($namespace);
            foreach ($session as $key => $val) {
                $this->session->$key = $val;
            }
        } else {
            $this->session = new Zend_Session_Namespace($namespace);
        }

    }

    public function issnPrintAction()
    {
        $action     = $this->getRequest()->getParam('act');
        $controller = $this->getRequest()->getParam('cnt');
        $printer    = $this->getRequest()->getParam('prn');
        $data = array();

        $clause = 'SSN_ID IN (';
        $countToPrint  = 0;
        
        $show_by = $this->session->navigation[$controller][$action]['show_by'];
        $pageselector = $this->session->navigation[$controller][$action]['pageselector'];
        
        foreach ($this->session->conditions[$controller][$action] as  $key => $val) {
            if ($key == $val) {
                $countToPrint++;
                $clause .= '\'' . $key . '\',';
            }
        }
        $clause = substr($clause, 0,  -1) . ')';
        if ($countToPrint > 0) {
            $flag = true;
            $lines = $this->minder->getIssns(array($clause => '1'), $pageselector, $show_by);
            $lines = $lines['data'];
            
            if (null != $this->minder->limitPrinter) {
                $deviceid = $this->minder->limitPrinter;
            } elseif (null != $printer) {
                $deviceid = $printer;
            } else {
                $data['message'] = 'No printer available';
                $data['status']  = false;
                $flag = false;
            }
            if ($flag) {
                $p = $this->minder->getPrinter($deviceid, 'issn.fmt');
                    if (false != ($data['status'] = $p->printISSNLabel($lines))) {
                        $data['message'] = $countToPrint . ' label(s) sent to printer ' . $deviceid;
                    } else {
                        $data['message'] = 'Error occurred at printer '  . $deviceid . "\n" . implode(";\n", $p->errors);
                        //$data['message'] = 'Error occurred at printer ' . $deviceid;
                    }
            }
        } else {
            $data['status'] = false;
            $data['message'] = 'Nothing to print';
        }
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($data);
    }
    
    public function locationPrintAction()
    {
        $action     = $this->getRequest()->getParam('act');
        $controller = $this->getRequest()->getParam('cnt');
        $printer    = $this->getRequest()->getParam('prn');
        $conditions = $this->session->conditions[$controller][$action];
        
        $pageSelector = $this->session->navigation[$controller][$action]['pageselector'];
        $showBy       = $this->session->navigation[$controller][$action]['show_by'];  
       
        $result = $this->minder->getLocationLines(null, $pageSelector, $showBy);
        $lines  = $result['data'];
      
        $data           = array();
        $clause         = 'LOCN_ID IN (';
        $countToPrint   = 0;
        $toPrint        = array();
        foreach ($lines as $line) {
            if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                 $countToPrint++;
                 $clause .= '\'' . $line->items['LOCN_ID'] . '\',';
                 $toPrint[] = $line;
            }
        }
   
        $clause = substr($clause, 0,  -1) . ')';
        if ($countToPrint > 0) {
            $flag = true;
            $lines = $this->minder->getLocationLines(array($clause => '1'));
            if (null != $this->minder->limitPrinter) {
                $deviceid = $this->minder->limitPrinter;
            } elseif (null != $printer) {
                $deviceid = $printer;
            } else {
                $data['message'] = 'No printer available';
                $data['status']  = false;
                $flag = false;
            }
            if ($flag) {
                    $result = $this->minder->printLocationLabel($printer, $toPrint);
                    if (false != ($data['status'] = $result)) {
                        $data['message'] = $countToPrint . ' label(s) sent to printer ' . $deviceid;
                    } else {
                        $data['message'] = 'Error occurred at printer '  . $deviceid . "\n";
                        //$data['message'] = 'Error occurred at printer ' . $deviceid;
                    }
            }
        } else {
            $data['status'] = false;
            $data['message'] = 'Nothing to print';
        }
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($data);
    }

    public function issnPrintForHumanAction()
    {
        $action     = $this->getRequest()->getParam('act');
        $controller = $this->getRequest()->getParam('cnt');
        $printer    = $this->getRequest()->getParam('prn');
        $id 		= $this->getRequest()->getParam('id');
        
        $data = array();

        $clause = 'SSN_ID IN (';
        $countToPrint  = 0;
        
                $countToPrint++;
                $clause .= '\'' . $id . '\',';
                
        $clause = substr($clause, 0,  -1) . ')';
        if ($countToPrint > 0) {
            $flag = true;
            $lines = $this->minder->getIssns(array($clause => '1'));
            if (null != $this->minder->limitPrinter) {
                $deviceid = $this->minder->limitPrinter;
            } elseif (null != $printer) {
                $deviceid = $printer;
            } else {
                $data['message'] = 'No printer available';
                $data['status']  = false;
                $flag = false;
            }
            if ($flag) {
                $p = $this->minder->getPrinter($deviceid, 'issn.fmt');
                    if (false != ($data['status'] = $p->printISSNLabel($lines))) {
                        $data['message'] = count($lines) . ' label(s) sent to printer ' . $deviceid;
                    } else {
                        $data['message'] = 'Error occurred at printer '  . $deviceid . "\n" . implode(";\n", $p->errors);
                        //$data['message'] = 'Error occurred at printer ' . $deviceid;
                    }
            }
        } else {
            $data['status'] = false;
            $data['message'] = 'Nothing to print';
        }
        $this->_helper->viewRenderer->setNoRender();
        echo json_encode($data);
    }    
    
}
?>