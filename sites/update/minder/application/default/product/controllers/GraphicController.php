<?php

/**
 * Minder
 *
 * PHP version 5.2.3
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */


include_once ("jpgraph/jpgraph.php");
include_once ("jpgraph/jpgraph_gantt.php");


/**
 * GraphicController
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */



class GraphicController extends Minder_Controller_Action
{
    /**
    * @desc data from PICK_ITEM table
    */
    protected $pickItems    =   array();
    
    /**
    * @desc Graph object
    */
    protected $graph        =   null;
    
    public function init() {
        
        parent::init();
        $this->_helper->viewRenderer->setNoRender(true);
        
        
    }
    /**
    * @desc draw PickItem crossing diagram 
    */
    public function linediagramAction() {
        $mode = null;
        $mode = $this->session->params['draw']['mode'];
        //$this->session->params['draw']['mode'] = '';
        switch($mode) {
            case 'pickItem':
                                $this->_configPickItemParams();
                                $this->_drawPickItemDiagram();
                                break;
            case 'ssnItem':
                                $this->_configSsnItemParams();
                                $this->_drawSsnItemDiagram();
                                break;     
                                
        } 
    }
    /**
    * @desc set main params and inital GpGraph for SSNs
    * @param void
    * @return void
    */
    protected function _configSsnItemParams() {
        $this->pickItems = $this->minder->getSsnItemDates($this->session->params['draw']['items']);    
        $this->graph = new GanttGraph();
        $this->graph->title->Set("Crossing PICK_ITEMS");

        
        // For the titles we also add a minimum width of 100 pixels for the Task name column
        $this->graph->scale->actinfo->SetColTitles(
                                                    array('Pick Label', 'SSN_ID', 'Days', 'Due Date', 'Return Date'), array(100)
                                                  );
        $this->graph->scale->actinfo->SetBackgroundColor('green:0.5@0.5');
        $this->graph->scale->actinfo->SetFont(FF_FONT1,FS_NORMAL,10);
        $this->graph->scale->actinfo->vgrid->SetStyle('solid');
        $this->graph->scale->actinfo->vgrid->SetColor('gray');
        
        // Setup some "very" nonstandard colors
        $this->graph->SetMarginColor('lightgreen@0.8');
        $this->graph->SetBox(true,'yellow:0.6',2);
        $this->graph->SetFrame(true,'darkgreen',4);
        $this->graph->scale->divider->SetColor('yellow:0.6');
        $this->graph->scale->dividerh->SetColor('yellow:0.6');
        
        // Explicitely set the date range 
        // (Autoscaling will of course also work)
        $startDate = date('Y-m-d'); $endDate = date('Y-m-d');
        foreach($this->pickItems as $pickItem) {
            foreach($pickItem as $item) {
               if(!empty($item['PICK_LINE_DUE_DATE']) && !is_null($item['PICK_LINE_DUE_DATE']) &&
                  !empty($item['RETURN_DATE']) && !is_null($item['RETURN_DATE'])) {
                  
                       $pickDueDate     = explode(' ', $item['PICK_LINE_DUE_DATE']);
                       $pickReturnDate  = explode(' ', $item['RETURN_DATE']);
                       if($pickDueDate[0] < $startDate) {
                           $startDate = $pickDueDate[0];
                       }
                       if($pickReturnDate[0] > $endDate) {
                            $endDate    =   $pickReturnDate[0];
                       }
                  }
            }   
        }    

        $this->graph->SetDateRange($startDate, $endDate);

        // Display month and year scale with the gridlines
        $this->graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        $this->graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

        // Make the week scale font smaller than the default
        $this->graph->scale->week->SetFont(FF_FONT0);

        // Use the short name of the month together with a 2 digit year
        // on the month scale
        $this->graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        $this->graph->scale->month->grid->SetColor('gray');
        $this->graph->scale->month->grid->Show(true);
        $this->graph->scale->year->grid->SetColor('gray');
        $this->graph->scale->year->grid->Show(true);
    
    }
    /**
    * @desc data must be in $this->pickItems array
    */
    protected function _drawSsnItemDiagram() {
        $i = 0;
        // draw selected PICK_ITEMS
        foreach($this->pickItems as $key => $pickItem) {
            
            $bar = new GanttBar($i, "$key", "1970-01-01" , "1970-01-01");
            $bar->title->SetFont(FF_FONT1,FS_BOLD,8);
            $bar->SetPattern(BAND_RDIAG,"yellow");
            $bar->SetFillColor("red");
            $this->graph->Add($bar);
            $i++;
            // draw crossing PICK_ITEMS 
            foreach($pickItem as $value) {
                $pickDueDate    = explode(' ', $value['PICK_LINE_DUE_DATE']);
                $pickReturnDate = explode(' ', $value['RETURN_DATE']);
                $pickOrder      = $value['PICK_ORDER'];   
                $days           = $this->_dateDiff($value['PICK_LINE_DUE_DATE'], $value['RETURN_DATE']) + 1;
                $ssnId          = $value['SSN_ID'];
                $legend         = array('   + ' .$value['PICK_LABEL_NO'], "$ssnId", "$days", "$pickDueDate[0]", "$pickReturnDate[0]");
            
                $bar = new GanttBar($i, $legend,  
                                    $pickDueDate[0], $pickReturnDate[0], $pickOrder, 10
                                   );
                $this->graph->Add($bar);
                $i++;
            }
        }
        
        // Output the chart
        $this->graph->Stroke();
    
    } 
    /**
    * @desc set main params and inital GpGraph for pickItems
    * @param void
    * @return void
    */
    protected function _configPickItemParams() {
        
        $this->pickItems = $this->minder->getPickItemDates($this->session->params['draw']['items']);    
        $this->graph = new GanttGraph();

        $this->graph->title->Set("Loan Order Overlaps");

        
        // For the titles we also add a minimum width of 100 pixels for the Task name column
        $this->graph->scale->actinfo->SetColTitles(
                                                    array('Pick Label', 'SSN_ID', 'Days', 'Due Date', 'Return Date'), array(100)
                                                  );
        $this->graph->scale->actinfo->SetBackgroundColor('green:0.5@0.5');
        $this->graph->scale->actinfo->SetFont(FF_FONT1,FS_NORMAL,10);
        $this->graph->scale->actinfo->vgrid->SetStyle('solid');
        $this->graph->scale->actinfo->vgrid->SetColor('gray');
        
        // Setup some "very" nonstandard colors
        $this->graph->SetMarginColor('lightgreen@0.8');
        $this->graph->SetBox(true,'yellow:0.6',2);
        $this->graph->SetFrame(true,'darkgreen',4);
        $this->graph->scale->divider->SetColor('yellow:0.6');
        $this->graph->scale->dividerh->SetColor('yellow:0.6');
        
        // Explicitely set the date range 
        // (Autoscaling will of course also work)
        $startDate = date('Y-m-d'); $endDate = date('Y-m-d');
        foreach($this->pickItems as $pickItem) {
            if(is_array($pickItem[1]) && count($pickItem[1]) > 0) {
                $pickDueDate     = explode(' ', $pickItem[0]['PICK_LINE_DUE_DATE']);
                $pickReturnDate  = explode(' ', $pickItem[0]['RETURN_DATE']);
                
                if($pickDueDate[0] < $startDate) {
                    $startDate = $pickDueDate[0];
                }
                if($pickReturnDate[0] > $endDate) {
                    $endDate    =   $pickReturnDate[0];
                }
            }
        }
        
        foreach($this->pickItems as $pickItem) {
            foreach($pickItem[1] as $item) {
               if(!empty($item['PICK_LINE_DUE_DATE']) && !is_null($item['PICK_LINE_DUE_DATE']) &&
                  !empty($item['RETURN_DATE']) && !is_null($item['RETURN_DATE'])) {
                       $pickDueDate     = explode(' ', $item['PICK_LINE_DUE_DATE']);
                       $pickReturnDate  = explode(' ', $item['RETURN_DATE']);
                       if($pickDueDate[0] < $startDate) {
                           $startDate = $pickDueDate[0];
                       }
                       if($pickReturnDate[0] > $endDate) {
                            $endDate    =   $pickReturnDate[0];
                       }
                  }   
           }    
        }
        // added week
        $endDate   = date('Y-m-d', (strtotime($endDate) + 500000));
        $this->graph->SetDateRange($startDate, $endDate);
        // Display month and year scale with the gridlines
        $this->graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        $this->graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);

        // Make the week scale font smaller than the default
        $this->graph->scale->week->SetFont(FF_FONT0);

        // Use the short name of the month together with a 2 digit year
        // on the month scale
        $this->graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        $this->graph->scale->month->grid->SetColor('gray');
        $this->graph->scale->month->grid->Show(true);
        $this->graph->scale->year->grid->SetColor('gray');
        $this->graph->scale->year->grid->Show(true);
        
    }
    /**
    * @desc data must be in $this->pickItems array
    */
    protected function _drawPickItemDiagram() {
        
        $i = 0;
        // draw selected PICK_ITEMS
        foreach($this->pickItems as $key => $pickItem) {
            
            // if empty array of crossing elements
            if(count($pickItem[1]) == 0) {
                continue;
            }
            $pickMainDueDate    = explode(' ', $pickItem[0]['PICK_LINE_DUE_DATE']);
            $pickMainReturnDate = explode(' ', $pickItem[0]['RETURN_DATE']);
            $pickOrder      = $pickItem[0]['PICK_ORDER'];   
            $days           = $this->_dateDiff($pickItem[0]['PICK_LINE_DUE_DATE'], $pickItem[0]['RETURN_DATE']) + 1;
            $ssnId          = $pickItem[0]['SSN_ID'];  
            $legend         = array($key, "$ssnId", "$days", "$pickMainDueDate[0]", "$pickMainReturnDate[0]");
         
            $bar = new GanttBar($i, $legend, 
                                $pickMainDueDate[0],  $pickMainReturnDate[0], $pickOrder, 10
                               );
            $bar->title->SetFont(FF_FONT1,FS_BOLD,8);
            $bar->SetPattern(BAND_RDIAG,"yellow");
            $bar->SetFillColor("red");
            $this->graph->Add($bar);
            
            $i++;
            // draw crossing PICK_ITEMS 
            foreach($pickItem[1] as $item => $value) {
                $pickDueDate    = explode(' ', $value['PICK_LINE_DUE_DATE']);
                $pickReturnDate = explode(' ', $value['RETURN_DATE']);
                $pickOrder      = $value['PICK_ORDER'];   
                $days           = $this->_dateDiff($value['PICK_LINE_DUE_DATE'], $value['RETURN_DATE']) + 1;
                if(!empty($value['PROD_ID']) && !is_null($value['PROD_ID'] && $value['PROD_ID'] !='')) {
                    $ssnOrProdId    =   $value['PROD_ID'];
                } else {
                    $ssnOrProdId    =   $value['SSN_ID'];
                }
                
                if($pickDueDate[0] < $pickMainDueDate[0]) {
                    $pickDueDate[0] = $pickMainDueDate[0];
                }
                
                if($pickReturnDate[0] > $pickMainReturnDate[0]) {
                    $pickReturnDate[0] = $pickMainReturnDate[0];
                }  
                $legend         = array('   + ' .$value['PICK_LABEL_NO'], "$ssnOrProdId", "$days", "$pickDueDate[0]", "$pickReturnDate[0]");
            
                $bar = new GanttBar($i, $legend,  
                                    $pickDueDate[0], $pickReturnDate[0], $pickOrder, 10
                                   );
                $this->graph->Add($bar);
                $i++;
            }
        }
        
        // Output the chart
        $this->graph->Stroke();
    
    }
    /**
    * @desc calculate diff between SatrtDate and EndDate
    * @param string $startDate
    * @param string $endDate
    * @return string   
    */
    protected function _dateDiff($startDate, $endDate) {
        $dueDate        = strtotime($startDate); 
        $returnDate     = strtotime($endDate); 
        $diffDate       = getdate($returnDate - $dueDate); 
        
        return $diffDate['yday']; 
    }
}