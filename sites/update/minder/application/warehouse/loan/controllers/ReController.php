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
 * Warehouse_ReController
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
class Warehouse_ReController extends Minder_Controller_Action
{

    /**
     * RePack ISSN
     *
     * @return void
     */
    public function packAction()
    {
        $this->view->pageTitle = 'RePack';
        $this->view->conditions = array();

        if (count($this->getRequest()->getPost('action')) > 0) {
            $params = array();
            switch ($this->getRequest()->getPost('action')) {
                case 'CANCEL':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto($this->session->action,
                                             $this->session->controller,
                                             $this->session->module,
                                             $this->session->savedParams);
                    return;
                    break;
            }
        }
        //-- setup conditions
        $isAllowed = array('SSN_ID'            => 'SSN_ID = ?',
                           'WH_ID'             => 'WH_ID = ?',
                           'LOCN_ID'           => 'LOCN_ID = ?',
                           'PROD_ID'           => 'PROD_ID = ?',
                           'CURRENT_QTY'       => 'CURRENT_QTY = ?',
                           'SSN_CREATE_DATE'   => 'SSN_CREATE_DATE = ?',
                           'COMPANY_ID'        => 'COMPANY_ID = ?',
                           'ISSN_STATUS'       => 'ISSN_STATUS = ?',
                           'SSN_DESCRIPTION'   => 'SSN_DESCRIPTION = ?',
                           'ISSN_PACKAGE_TYPE' => 'ISSN_PACKAGE_TYPE = ?');

        $this->_preProcessNavigation();

        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        $this->view->tableId = "table";
        $this->_setupHeaders('table');
        $conditions = $this->_setupConditions(null, $isAllowed);
        $clause     = $this->session->conditions['re']['pack']['clause'];

        $temp       = $this->minder->getIssns($clause);
        $lines      = array();
        $this->view->repackAllowed = true;
        unset($originalSsn);
        foreach ($temp as $line) {
            if (array_search($line->id, $conditions['original'])) {
                if (isset($originalSsn)) {
                    if ($originalSsn == $line->items['ORIGINAL_SSN']) {
                        $lines[] = $line;
                    } else {
                        $lines[] = $line;
                        $this->view->repackAllowed = false;
                        $this->addError("Not all ISSN have same ORIGINAL_SSN");
                    }
                } else {
                    $originalSsn = $line->items['ORIGINAL_SSN'];
                    $lines[] = $line;
                }
            }
        }
        $this->view->lines = $lines;
        $this->view->searchFields = array();

        $data = array();
        foreach ($lines as $line) {
            if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                $data[$line->id] = $line->items;
                $lines4update[$line->id] = $line;
            }
        }
        $this->view->data = $data;

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'UPDATE':
                if ($this->view->repackAllowed) {
                    //$this->view->flash = "NOT IMPLEMENTED.";
                    //-- get Qty and # Packages
                    $newStack = array('QTY' => $this->getRequest()->getPost('qty'),
                                      'PKG' => $this->getRequest()->getPost('pkg'),
                                      'PACK_TYPE' => $this->getRequest()->getPost('pack_type'),
                                      'PRINTER' =>  $this->getRequest()->getPost('printer')
                                     );
                    array_multisort($newStack['PKG'], SORT_DESC, SORT_NUMERIC,
                                    $newStack['QTY'], SORT_DESC, SORT_NUMERIC);
                    if (false == ($list = $this->minder->rePack($lines4update, $newStack))) {
                        $this->addError($this->minder->lastError);
                    } else {
                        $this->addMessage('RePacked successfully');
                        
                        if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'F'){
                            
                            $printerObj = $this->minder->getPrinter(null, $newStack['PRINTER']);
                        
                            $result     = false;
                            $count      = 0;
                            
                            foreach ($list as $line) {
                               $issnList = $this->minder->getIssnForPrint($line->id); 
                               try{
                                    $result    =    $printerObj->printIssnLabel($issnList);
                                
                                    if($result['RES'] < 0){
                                        $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                                    }             
                               } catch(Exception $ex){
                                       $this->addError($ex->getMessage());
                                       break;    
                               }
                               $count++;    
                            }
                        
                            if($result['RES'] >= 0){
                                $this->addMessage($count . ' label(s) printed successfully');
                            }    
                        } else {
                            $template = 'issn.fmt';
                            $p = $this->minder->getPrinter($newStack['PRINTER'], $template);
                            foreach ($list as $line) {
                                if (!$p->printISSNLabel($line)) {
                                    $this->addWarning('Label for ' . $line->id . ' are not printed');
                                }
                            }    
                        }
                        $this->view->lines = $list;
                    }
                }
                break;
            default:
                break;
        }
        $this->_processReportTo();

        $this->view->packagingTypeList = $this->fillList('', "getPackagingTypeList");
        $this->view->printerList       = $this->minder->getPrinterList(); //$this->fillList('', "getPrinterList");

        $this->_postProcessNavigation($this->view->lines);

        $this->view->lines = array_slice($this->view->lines,
                                         $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
                                         $this->view->maxno);
    }

    public function packInitAction() {
        $request = $this->getRequest();
        $request->setParam('method', 'true');
        $request->setParam('id', 'select_complete');
        $request->setParam('inAction', 'pack');
        $this->calcAction();

        $this->_redirector->setCode(303)
                          ->gotoSimple('pack');
    }

    public function prePackAction()
    {
        $this->view->pageTitle = "Pre Pack List";

        $this->_preProcessNavigation();
        $this->_processReportTo();

        $this->_postProcessNavigation();

    }

    /**
     * ReSort ISSN
     *
     * @return void
     */
    public function sortAction()
    {
        $this->view->pageTitle = 'ReSort';
        $this->view->conditions = array();

        if (count($this->getRequest()->getPost('action')) > 0) {
            $params = array();
            switch (strtoupper($this->getRequest()->getPost('action'))) {
                case 'CANCEL':
                    $this->_redirector = $this->_helper->getHelper('Redirector');
                    $this->_redirector->setCode(303)
                                      ->goto($this->session->action,
                                             $this->session->controller,
                                             $this->session->module,
                                             $this->session->savedParams);
                    return;
                    break;
            }
        }


        //-- preprocess input of navigation values
        if (isset($this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()])) {
            foreach ($this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()] as $key => $val) {
                if (null != $this->getRequest()->getParam($key)) {
                    $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()][$key] = (int)$this->getRequest()->getParam($key);
                }
            }
        } else {
            $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()]['show_by']      = 15;
            $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()]['pageselector'] = 0;
        }
        //-- end process input navigation values

        $this->view->other1Name = $this->minder->getFieldFromSsnGroup('FIELD21');
        //-- setup conditions
        $isAllowed = array('SSN_ID'          => 'SSN_ID = ?',
                           'WH_ID'           => 'WH_ID = ?',
                           'LOCN_ID'         => 'LOCN_ID = ?',
                           'PROD_ID'         => 'PROD_ID = ?',
                           'CURRENT_QTY'     => 'CURRENT_QTY = ?',
                           'SSN_CREATE_DATE' => 'SSN_CREATE_DATE = ?',
                           'COMPANY_ID'      => 'COMPANY_ID = ?',
                           'ISSN_STATUS'     => 'ISSN_STATUS = ?',
                           'SSN_DESCRIPTION' => 'SSN_DESCRIPTION = ?',
                           'PACK_ID'         => 'PACK_ID = ?');

        $this->view->headers = array('SSN_ID'            => 'ISSN',
                                     'WH_ID'             => 'WH.',
                                     'LOCN_ID'           => 'Location',
                                     'PROD_ID'           => 'Product ID',
                                     'CURRENT_QTY'       => 'Curr Qty',
                                     'SSN_CREATE_DATE'   => 'Created Date',
                                     'COMPANY_ID'        => 'Company ID',
                                     'ISSN_STATUS'       => 'Status',
                                     'SSN_DESCRIPTION'   => 'SSN Description',
                                     'PACK_ID'           => 'Package Type');
//                                   'ISSN_PACKAGE_TYPE' => 'Package Type');

        if (isset($this->session->conditions[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()])) {
            $conditions = $this->session->conditions[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()];
        } else {
            $conditions = array();
        }
        foreach ($this->getRequest()->getParams() as $key => $val) {
            if (array_key_exists($key, $isAllowed)) {
                $conditions[$key] = $val;
            }
        }
        $this->session->conditions[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()] = $conditions;
        $this->view->conditions                                                                                    = $conditions;
        //-- End setup conditions
        //-- convert screen filter conditions to Minder acceptable format for preparing query
        $clause = array();
        foreach ($conditions as $key => $val) {
            if (array_key_exists($key, $isAllowed)) {
                if (null != $val) {
                    $clause[strtoupper($isAllowed[$key])] = $val;
                }
            }
        }
        //-- end conversion
        $temp = $this->minder->getIssns($clause);
        $lines = array();
        $this->view->resortAllowed = true;
         unset($originalSsn);
        foreach ($temp as $line) {
            if (array_search($line->id, $conditions['original'])) {
                if (isset($originalSsn)) {
                    if ($originalSsn == $line->items['ORIGINAL_SSN']) {
                        $lines[] = $line;
                    } else {
                        $lines[] = $line;
                        $this->view->resortAllowed = false;
                        $this->addError("Not all ISSN have same ORIGINAL_SSN");
                    }
                } else {
                    $originalSsn = $line->items['ORIGINAL_SSN'];
                    $lines[] = $line;
                }
            }
        }
        $this->view->originalSsn1 = $originalSsn;
        //$this->view->originalSsn2 = $originalSsn + 5000;
        $this->view->originalSsn2 = substr($originalSsn,0,2) . (substr($originalSsn,2) + 5000);
        $this->view->lines = $lines;
        $this->view->searchFields = array();

        $data = array();
        foreach ($lines as $line) {
            if (array_key_exists($line->id, $conditions) && 'off' != $conditions[$line->id]) {
                $data[$line->id] = $line->items;
                $lines4update[$line->id] = $line;
            }
        }
        $this->view->data = $data;

        switch (strtoupper($this->getRequest()->getPost('action'))) {
            case 'UPDATE':
                if ($this->view->resortAllowed) {
                    //-- get Qty and # Packages
                    $newStack = array('QTY' => $this->getRequest()->getPost('qty'),
                                      'PKG' => $this->getRequest()->getPost('pkg'),
                                      'QTY1' => $this->getRequest()->getPost('qty1'),
                                      'PKG1' => $this->getRequest()->getPost('pkg1'),
                                      'PACK_TYPE1' => $this->getRequest()->getPost('pack_type1'),
                                      'PACK_TYPE2' => $this->getRequest()->getPost('pack_type2'),
                                      'PRINTER' =>  $this->getRequest()->getPost('printer')
                                     );
                    array_multisort($newStack['PKG'], SORT_DESC, SORT_NUMERIC,
                                    $newStack['QTY'], SORT_DESC, SORT_NUMERIC);
                    array_multisort($newStack['PKG1'], SORT_DESC, SORT_NUMERIC,
                                    $newStack['QTY1'], SORT_DESC, SORT_NUMERIC);
                    if (false == ($list = $this->minder->reSort($lines4update, $newStack))) {
                        $this->addError($this->minder->lastError);
                    } else {
                        $this->addMessage('ReSort successfully');
                        if($this->minder->defaultControlValues['GENERATE_LABEL_TEXT'] == 'F'){
                            
                            $printerObj = $this->minder->getPrinter(null, $newStack['PRINTER']);
                            $result     = false;
                            $count      = 0;
                            
                            foreach ($list as $line) {
                               
                               $issnList = $this->minder->getIssnForPrint($line->id); 
                                
                               try{
                                    $result    =    $printerObj->printIssnLabel($issnList);
                                    if($result['RES'] < 0){
                                        $this->addError('Error while print label(s): ' . $result['ERROR_TEXT']);
                                    }             
                               } catch(Exception $ex){
                                       $this->addError($ex->getMessage());
                                       break;    
                               }
                               $count++;    
                            }
                            if($result['RES'] >= 0){
                                $this->addMessage($count . ' label(s) printed successfully');
                            }    
                        } else {
                            $template = 'issn.fmt';
                            $p = $this->minder->getPrinter($newStack['PRINTER'], $template);
                            foreach ($list as $line) {
                                if (!$p->printISSNLabel($line)) {
                                    $this->addWarning('Label for ' . $line->id . ' are not printed');
                                }
                            }    
                        }
                        
                        $this->view->lines = $list;
                    }
                }
                break;
            case 'REPORT: CSV':
                $this->getResponse()->setHeader('Content-Type', 'text/csv')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.csv"');
                $this->render('report-csv');
                return;
                break;

            case 'REPORT: XML':
                $response = $this->getResponse();
                $response->setHeader('Content-type', 'application/octet-stream');
                $response->setHeader('Content-type', 'application/force-download');
                $response->setHeader('Content-Disposition', 'attachment; filename="report.xml"');
                $this->render('report-xml');
                return;

            case 'REPORT: XLS':
                error_reporting(0);
                $xls = new Spreadsheet_Excel_Writer();
                $xls->send('report.xls');
                $this->view->xls = $xls;
                $this->render('report-xls');
                return;

            case 'REPORT: TXT':
                $this->getResponse()->setHeader('Content-Type', 'text/plain')
                                    ->setHeader('Content-Disposition', 'attachment; filename="report.txt"');
                $this->render('report-txt');
                return;
                break;
            default:
                break;
        }

        $this->view->packagingTypeList = $this->fillList('', "getPackagingTypeList");
        $this->view->printerList       = $this->minder->getPrinterList(); //$this->fillList('', "getPrinterList");

        //-- post process navigation
        $this->view->numRecords  = count($this->view->lines);
        $this->view->navigation = $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()];
        if ($this->view->numRecords <= $this->view->navigation['show_by']) {
            $this->view->navigation['pageselector'] = $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()]['pageselector']
                                                    = 0;
        }

        if (($this->view->navigation['show_by'] * ($this->view->navigation['pageselector'] + 1)) > $this->view->numRecords) {
            $this->view->navigation['pageselector'] = $this->session->navigation[$this->getRequest()->getControllerName()][$this->getRequest()->getActionName()]['pageselector']
                                                    = (int)floor($this->view->numRecords / $this->view->navigation['show_by']);

            $this->view->maxno = $this->view->numRecords - ($this->view->navigation['show_by'] * $this->view->navigation['pageselector']);
        } else {
            $this->view->maxno = $this->view->navigation['show_by'];
        }
        //-- end post process
        $this->view->pages     = array();
        for ($i = 1; $i<=ceil($this->view->numRecords/$this->view->navigation['show_by']); $i++) {
            $this->view->pages[] = $i;
        }
        $this->view->lines = array_slice($this->view->lines,
                                         $this->view->navigation['show_by'] * $this->view->navigation['pageselector'],
                                         $this->view->maxno);
    }

    public function calcAction()
    {
        $calculatedValue = 0; //-- calculated value
        $count           = 0; //-- number of elements used for calculation

        $field  = strtoupper($this->getRequest()->getParam('field'));
        $method = $this->getRequest()->getParam('method');
        $id     = $this->getRequest()->getParam('id');
        $value  = $this->getRequest()->getParam('value');
        $action = $this->getRequest()->getParam('inAction');

        $isAllowed = array('SSN_ID'          => 'SSN_ID = ?',
                           'WH_ID'           => 'WH_ID = ?',
                           'LOCN_ID'         => 'LOCN_ID = ?',
                           'PROD_ID'         => 'PROD_ID = ?',
                           'CURRENT_QTY'     => 'CURRENT_QTY = ?',
                           'SSN_CREATE_DATE' => 'SSN_CREATE_DATE = ?',
                           'COMPANY_ID'      => 'COMPANY_ID = ?',
                           'ISSN_STATUS'     => 'ISSN_STATUS = ?',
                           'SSN_DESCRIPTION' => 'SSN_DESCRIPTION = ?',
                           'PACK_ID'         => 'PACK_ID = ?');

        switch (strtolower($action)) {
            default:
                //-- setup conditions
                if (isset($this->session->conditions[$this->getRequest()->getControllerName()][$action])) {
                    $conditions = $this->session->conditions[$this->getRequest()->getControllerName()][$action];
                } else {
                    $conditions = array();
                }
                foreach ($this->getRequest()->getParams() as $key => $val) {
                    if (array_key_exists($key, $isAllowed)) {
                        $conditions[$key] = $val;
                    }
                }
                $this->session->conditions[$this->getRequest()->getControllerName()][$action] = $conditions;
                //-- End setup conditions

                //-- convert screen filter conditions to Minder acceptable format for preparing query
                $clause = array();
                foreach ($conditions as $key => $val) {
                    if (array_key_exists($key, $isAllowed)) {
                        if (null != $val) {
                            $clause[strtoupper($isAllowed[$key])] = $val;
                        }
                    }
                }
                //-- end conversion
                //-- get appropriate lines
                if(isset($conditions['clause'])) {
                    $clause = $conditions['clause'];
                } else {
                    $clause = null;
                }
                $temp  = $this->minder->getIssns($clause);
                $lines = array();
                foreach ($temp as $line) {
                    if (array_search($line->id, $conditions['original'])) {
                        $lines[] = $line;
                    }
                }
                $conditions = $this->_markSelected($lines, $id, $value, $method, $action);
                $numRecords = count($lines);
                $this->session->conditions[$this->getRequest()->getControllerName()][$action] = $conditions;
                switch ($field) {
                    case 'QTY':
                        for ($i = 0; $i < $numRecords; $i++) {
                            if (false !== array_search($lines[$i]->id, $conditions, true )) {
                                $calculatedValue += $lines[$i]->items['CURRENT_QTY'];
                                $count++;
                            }
                        }
                        break;
                    default:
                    break;
                }
            break;
        }
        $data = array();
        $data['selected_num'] = $count;
        $data['total_qty'] = $calculatedValue;
        $this->view->data = $data;
    }

    /**
     * Get list by $callbackFunction, with $callbackParams (if exists)
     * then check if not exists $valueToCheck - add it to list
     *
     * @param string $valueToCheck
     * @param string $callbackFunction
     * @param array  $callbackParams
     * @return array
     */
    private function fillList($valueToCheck, $callbackFunction, $callbackParams = null)
    {
        if ($callbackParams != null) {
            $tempArray = call_user_func_array(array($this->minder, $callbackFunction), $callbackParams);
        } else {
            $tempArray = call_user_func(array($this->minder, $callbackFunction));
        }
        if ($valueToCheck != null) {
            if (!array_key_exists($valueToCheck, $tempArray)) {
                $tempArray = array($valueToCheck => $valueToCheck) + $tempArray;
            }
        } else {
            $tempArray = minder_array_merge(array('' => ''), $tempArray);
        }
        return $tempArray;
    }

    protected function _setupHeaders($tableId)
    {
        if (!parent::_setupHeaders()) {
            $this->session->headers[$this->_controller][$this->_action][$tableId] =
            $this->view->headers = array('SSN_ID'            => 'ISSN',
                                         'WH_ID'             => 'WH.',
                                         'LOCN_ID'           => 'Location',
                                         'PROD_ID'           => 'Product ID',
                                         'CURRENT_QTY'       => 'Curr Qty',
                                         'SSN_CREATE_DATE'   => 'Created Date',
                                         'COMPANY_ID'        => 'Company ID',
                                         'ISSN_STATUS'       => 'Status',
                                         'SSN_DESCRIPTION'   => 'SSN Description',
                                         'ISSN_PACKAGE_TYPE' => 'Package Type');
        }
        return true;
    }

    protected function _setupShortcuts()
    {
        if (strtolower($this->session->controller) == 'issn') {
            $shortcuts = array(
            '<ISSN>' => $this->view->url(array('action'     => 'index',
                                               'controller' => 'issn',
                                               'module'     => 'warehouse'), '', true),
            'SSN' => $this->view->url(array('action'     => 'index',
                                            'controller' => 'ssn',
                                            'module'     => 'warehouse'), '', true),
            'Goods Receipt Notes' => $this->view->url(array('action'     => 'index',
                                                            'controller' => 'grn',
                                                            'module'     => 'warehouse'), '', true),
            'Products'  => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'products',
                                                  'module'     => 'warehouse'), '', true),
            'Location'  =>
                array(
                    'Locations List'    => $this->view->url(array('action' => 'index', 'controller' => 'location2', 'module' => 'warehouse'), '', true),
                ),
            'Receive'   => $this->view->url(array('action'     => 'purchase',
                                                  'controller' => 'receive',
                                                  'module'     => 'warehouse'), null, true) . '/?fmt=12-inch',
            'Stocktake' => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'stocktake',
                                                  'module'     => 'warehouse'), '', true)
            );
        } elseif (strtolower($this->session->controller) == 'issn2') {
            $shortcuts = array(
            '<ISSN>' => $this->view->url(array('action'     => 'index',
                                             'controller' => 'issn2',
                                             'module'     => 'warehouse'), '', true),
            'SSN' => $this->view->url(array('action'     => 'index',
                                              'controller' => 'ssn2',
                                              'module'     => 'warehouse'), '', true),
            'Goods Receipt Notes' => $this->view->url(array('action'     => 'index',
                                                            'controller' => 'grn',
                                                            'module'     => 'warehouse'), '', true),
            'Products'  => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'products',
                                                  'module'     => 'warehouse'), '', true),
            'Location'  =>
                array(
                    'Locations List'    => $this->view->url(array('action' => 'index', 'controller' => 'location2', 'module' => 'warehouse'), '', true),
                ),
            'Receive'   => $this->view->url(array('action'     => 'purchase',
                                                  'controller' => 'receive',
                                                  'module'     => 'warehouse'), null, true) . '/?fmt=12-inch',
            'Stocktake' => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'stocktake',
                                                  'module'     => 'warehouse'), '', true)
            );
        } elseif (strtolower($this->session->controller) == 'issn') {
            $shortcuts = array(
            'ISSN' => $this->view->url(array('action'     => 'index',
                                             'controller' => 'issn',
                                             'module'     => 'warehouse'), '', true),
            '<SSN>' => $this->view->url(array('action'     => 'index',
                                              'controller' => 'ssn',
                                              'module'     => 'warehouse'), '', true),
            'Goods Receipt Notes' => $this->view->url(array('action'     => 'index',
                                                            'controller' => 'grn',
                                                            'module'     => 'warehouse'), '', true),
            'Products'  => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'products',
                                                  'module'     => 'warehouse'), '', true),
            'Location'  =>
                array(
                    'Locations List'    => $this->view->url(array('action' => 'index', 'controller' => 'location2', 'module' => 'warehouse'), '', true),
                ),
            'Receive'   => $this->view->url(array('action'     => 'purchase',
                                                  'controller' => 'receive',
                                                  'module'     => 'warehouse'), null, true) . '/?fmt=12-inch',
            'Stocktake' => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'stocktake',
                                                  'module'     => 'warehouse'), '', true)
            );
        } elseif (strtolower($this->session->controller) == 'location') {
            $shortcuts = array(
            'ISSN' => $this->view->url(array('action'     => 'index',
                                             'controller' => 'issn',
                                             'module'     => 'warehouse'), '', true),
            'SSN' => $this->view->url(array('action'     => 'index',
                                            'controller' => 'ssn',
                                            'module'     => 'warehouse'), '', true),
            'Goods Receipt Notes' => $this->view->url(array('action'     => 'index',
                                                            'controller' => 'grn',
                                                            'module'     => 'warehouse'), '', true),
            'Products'  => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'products',
                                                  'module'     => 'warehouse'), '', true),
            '<Location>'  =>
                array(
                    'Locations List'    => $this->view->url(array('action' => 'index', 'controller' => 'location2', 'module' => 'warehouse'), '', true),
                ),
            'Receive'   => $this->view->url(array('action'     => 'purchase',
                                                  'controller' => 'receive',
                                                  'module'     => 'warehouse'), null, true) . '/?fmt=12-inch',
            'Replace Ssn Type'           => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'replace',
                                                  'module'     => 'warehouse'), '', true)
            /*,
            'Stocktake' => $this->view->url(array('action'     => 'index',
                                                  'controller' => 'stocktake',
                                                  'module'     => 'warehouse'), '', true)
            */
            );
        }
        if (!$this->minder->isStockAdjust) {
             unset($shortcuts['Stocktake']);
        }
        $this->view->shortcuts = $shortcuts;
        return true;
    }
}
