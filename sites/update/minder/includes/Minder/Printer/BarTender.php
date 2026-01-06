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
 * Print ISSN labels using BarTender Printer
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * @throws    Exception
 */
class Minder_Printer_BarTender extends Minder_Printer_Abstract
{
   
    public function __construct($printer = null, $port = null)
    {
        parent::__construct($printer);
       
    }
    
    public function __call($name, $arguments) {
        return array('RES' => -100, 'ERROR_TEXT' => 'Method ' . get_class($this) . '::' . $name . ' is not implemented.');
    }

    /**
     * @param array $data
     * @return boolean
     */
    function printGrnOrderLabel($data)
    {
        throw new Exception(__CLASS__ . ': ' . __FUNCTION__ . ' is unimplemented.');
    }

    /**
     * print IssnLine to printer
     *
     * @param IssnLine $line
     * @return boolean
     */
    public function printIssnLabel($lines)
    {
        if (!is_array($lines)) {
            $lines = array($lines);
        }
        
        $errorList = array();
        if (false != ($workDir = $this->minder->getPrinterDir($this->printer)) && file_exists($workDir)) {
            $fileName = rtrim($workDir, '\\/') . DIRECTORY_SEPARATOR . 'ISSN.txt';
            $backupDir = $workDir . DIRECTORY_SEPARATOR . date('Y-m-d');
          
            if (!file_exists($backupDir)) {
                 if (false == mkdir($backupDir)) {
                    throw new Exception('Can\'t make directory for Label Print arhive.');
                 }
            }
            if (false != ($fp = fopen($fileName, 'w'))) {
                $result = true;
                foreach ($lines as $line) {
                    $productInfo = $this->minder->getProductInfo($line->items['PROD_ID']);
                    $out = array();
                    $out[] = (($line->items['CURRENT_QTY'] > 1  && $line->items['PROD_ID'] == null) ? '_' : '') . $line->items['SSN_ID'];
                    $out[] = $line->items['PO_ORDER'];
                    $out[] = 1;
                    $out[] = $this->printer;
                    $out[] = $line->items['SSN_DESCRIPTION'];
                    $out[] = $line->items['PROD_ID'];
                    $out[] = $line->items['CURRENT_QTY'];
                    if (false != $productInfo) {
                        $out[] = $productInfo[0]['CODE'];
                    } else {
                        $out[] = '';
                    }
                    $out[] = $line->items['OTHER1'];
                    $out[] = $line->items['OTHER2'];
                    $out[] = $line->items['OTHER3'];
                    $out[] = $line->items['SERIAL_NUMBER'];
                    $out[] = $line->items['COMPANY_ID'];
                    $out[] = $line->items['PICK_ORDER'];
                    $out[] = $line->items['CREATE_DATE'];
                    $out[] = $line->items['USER_ID'];
                    $out[] = '';
                    $out[] = '';
                    $out[] = '';
                    $out[] = '';

                    if (false != ($data = $this->prepare($out))) {
                        $backupFileName = $backupDir . DIRECTORY_SEPARATOR . $line->items['SSN_ID'] . '_ISSN.txt';
                        $this->save($backupFileName, $data);
                        if (false === $this->send($fp, $data)) {
                            $errorList[] = $line->items['SSN_ID'];
                        }
                    }
                }
                fclose($fp);
            } else {
                $errorList[] = 'Can\'t open file ' . $fileName;
            }
        } else {
            $errorList[] = 'Can\'t get dir for specified printer ' . $this->printer;
        }

        if (null != $errorList) {
            $this->errors = $errorList;
            $result = false;
        }
        return $result;
    }

    /**
     * Prepares data
     *
     * @param array $data
     * @return string|boolean
     */
    public function prepare($data)
    {
        if (false != ($fp = fopen("php://temp/", 'r+'))) {
            fputcsv($fp, $data);
            if (false != rewind($fp)) {
                $output = stream_get_contents($fp);
                fclose($fp);
                return $output;
            }
        }
        return false;
    }

    /**
    * @desc print test labels from SYS_LABEL screen
    * @param array $test  
    * 
    * @return boolean
    */
    public function printTestLabel($test) {
    
    }
    
    /**
    * @desc print LOCATION labels
    * @param $location - LOCATON object
    * 
    * @return boolean
    */
    public function printLocationLabel($location) {
        
        $locnId   = $location->items['LOCN_ID'];
        $locnName = substr($location->items['LOCN_NAME'], 1, strlen($location->items['LOCN_NAME']) - 2);
        $locnData = explode('-', $locnName);
        
        $whId       = $locnData[0];
        $aisle      = $locnData[1];
        $bay        = $locnData[2];
        $level      = $locnData[3];
        $position   = $locnData[4];
        
        $data    = array($whId, $locnId, $locnName, $aisle, $bay, $level, $position);
        
        if (false != ($workDir = $this->minder->getPrinterDir($this->printer)) && file_exists($workDir)) {
            $fileName = rtrim($workDir, '\\/') . DIRECTORY_SEPARATOR . 'ISSN.txt';
            $backupDir = $workDir . DIRECTORY_SEPARATOR . date('Y-m-d');
            if (!file_exists($backupDir)) {
                 if (false == mkdir($backupDir)) {
                    throw new Exception('Can\'t make directory for Label Print arhive.');
                 }
            }
           
            if (false != ($fp = fopen($fileName, 'w'))) {
                if (false != ($datas = $this->prepare($data))) {
                    $backupFileName = $backupDir . DIRECTORY_SEPARATOR . $locnId . '_location.txt';
                    $this->save($backupFileName, $datas);
                    if (false != $this->send($fp, $datas)) {
                        return true;
                    }
                }    
            }  
       } 
        
        return false;
        
    }
    
    public function printLogonLabel($logon)
    {
        $productTemplate = $this->_template;
        
        $user_id        = $product['USER_ID'];
        $password       = $product['PASS_WORD']; 
        
        $data    = array($user_id, $password);
        
        if (false != ($workDir = $this->minder->getPrinterDir($this->printer)) && file_exists($workDir)) {
            $backupDir = $workDir . DIRECTORY_SEPARATOR . date('Y-m-d');
            if (!file_exists($backupDir)) {
                 if (false == mkdir($backupDir)) {
                    throw new Exception('Can\'t make directory for Logon Print arhive.');
                 }
            }
           
                if (false != ($datas = $this->prepare($data))) {
                    $backupFileName = $backupDir . DIRECTORY_SEPARATOR . $user_id . '_logon.txt';
                    $this->save($backupFileName, $datas);
                    if (false != $this->send($fp, $datas)) {
                        return true;
                    }
                }    
       } 
        
        return false;    	
    }
    
    /**
    * @desc print data from PROD_PROFILE screen
    * 
    * @param array $product - data to print
    * @return boolean 
    */
    public function printProductLabel($product) {
        
        $productId       = $product['PROD_ID'];
        $shortDesc       = $product['SHORT_DESC']; 
        
        $data    = array($productId, $shortDesc);
        
        if (false != ($workDir = $this->minder->getPrinterDir($this->printer)) && file_exists($workDir)) {
            $fileName = rtrim($workDir, '\\/') . DIRECTORY_SEPARATOR . 'I.txt';
            $backupDir = $workDir . DIRECTORY_SEPARATOR . date('Y-m-d');
            if (!file_exists($backupDir)) {
                 if (false == mkdir($backupDir)) {
                    throw new Exception('Can\'t make directory for Product Print arhive.');
                 }
            }
           
            if (false != ($fp = fopen($fileName, 'w'))) {
                if (false != ($datas = $this->prepare($data))) {
                    $backupFileName = $backupDir . DIRECTORY_SEPARATOR . $productId . '_product.txt';
                    $this->save($backupFileName, $datas);
                    if (false != $this->send($fp, $datas)) {
                        return true;
                    }
                }    
            }  
       } 
        
        return false;
    }
   /**
   * @desc print GRN label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printGrnLabel($data){
    
       if (false != ($workDir = $this->minder->getPrinterDir($this->printer)) && file_exists($workDir)) {
            $fileName = rtrim($workDir, '\\/') . DIRECTORY_SEPARATOR . 'I.txt';
            $backupDir = $workDir . DIRECTORY_SEPARATOR . date('Y-m-d');
            if (!file_exists($backupDir)) {
                 if (false == mkdir($backupDir)) {
                    throw new Exception('Can\'t make directory for Product Print arhive.');
                 }
            }
           
            if (false != ($fp = fopen($fileName, 'w'))) {
                if (false != ($datas = $this->prepare($data))) {
                    $backupFileName = $backupDir . DIRECTORY_SEPARATOR . $productId . '_grn.txt';
                    $this->save($backupFileName, $datas);
                    if (false != $this->send($fp, $datas)) {
                        return true;
                    }
                }    
            }  
       } 
        
       return false;
        
   }

    /**
     * @param array $borrowerId
     * @param null $labelQty
     * @return array
     */
    public function printBorrowerLabel($borrowerId, $labelQty = null){
        $result = array('RES' => 0, 'ERROR_TEXT' => 'Print Request Sent');
        $borrowerLocation = $this->minder->getLocations(array('WH_ID = ?' => 'XB', 'LOCN_ID = ?' => $borrowerId));
        if (empty($borrowerLocation)) {
            $result['RES'] = -102;
            $result['ERROR_TEXT'] = 'Borrower not found.';
            return $result;
        }
        
        $printResult = $this->minder->printLocationLabel($this->printer, array($borrowerLocation[0], $borrowerLocation[0]));
        if (!$printResult) {
            $result['RES'] = -101;
            $result['ERROR_TEXT'] = 'Unknown error.';
        }
        return $result;
    }

    /**
     * Saves data into specified file
     *
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    public function save($fileName, $data)
    {
        if (false != (file_put_contents($fileName, $data))) {
            return true;
        }
        return false;
    }

    /**
     * Sends data
     *
     * @param resource $fp
     * @param string $data
     * @return boolean|integer
     */
    public function send($fp, $data)
    {
        return fwrite($fp, $data);
    }
    
//methods to print LABELS using SYS_LABEL

   /**
   * @desc print Office Address|Mail Label|Despatch data from PICK_ORDER
   * @param array $data  
   * 
   * @return boolean
   */
   public function printAddressLabel($data, $prnType){
       
//       $this->setTableName('PICK_ORDER');
       $result = $this->labelPrint($data, $prnType);
           
       return $result;
   }
   
   /**
   * @desc set table name
   * @param string $tableName  
   * 
   * @return void
   */
   public function setTableName($tableName = null){
   
           $this->tableName    =    $tableName;
   
   }
   
   /**
   * @desc print data through PC_LABEL procedure
   * @param array $data
   * @param string $prnType 
   */
   protected function labelPrint($data, $prnType){
           
           $data        = $this->filterData($this->tableName, $data);    
           $deviceId   = $this->printer;
           if($deviceId != null && !empty($deviceId)) {
               if(count($data)>0) {
                   $result           = false;
                $printerFormat  = $this->printer . '_FORMAT';
                $prnFiles       = $this->minder->getPrnFile($printerFormat);
                $fileName       = isset($prnFiles[$prnType]) ? $prnFiles[$prnType] : null;
                $palaceDelimiter= $this->minder->defaultControlValues['LABEL_FIELD_WRAPPER'];
                
                $toPrint    =   null;
                
                if(empty($this->tableName)){
                    foreach($data as $key => $value) {
                        $toPrint .= $palaceDelimiter . $key . $palaceDelimiter . '=' . $value . '|';       
                    }
                } else {
                    foreach($data as $key => $value) {
                        $toPrint .= $palaceDelimiter . $this->tableName . '.' . $key . $palaceDelimiter . '=' . $value . '|';       
                    }
                }
                
                $prnFileName= $fileName;
                $prnData    = $toPrint;
                $personId   = $this->minder->userId;    
                $fromDeviceId   = $this->minder->deviceId;    
                
                //$result     = $this->minder->pcLabelPrint($deviceId, $prnType, $prnFileName, $prnData, $personId);
                $result     = $this->minder->pcLabelPrint($deviceId, $prnType, $prnFileName, $prnData, $personId, $fromDeviceId);
                
                return $result;
                
               } else {
                   throw new Exception('Missing data for printing');
               }
           } else {
               throw new Exception('Error while select printer');
           }
           
   }
   
  /**
   * @desc remove all BLOB fields
   * @param string $tableName
   * @param array  $data
   * 
   * @return array 
   */
   private function filterData($tableName, $data){
        
           $mustRemove    =    array();
       
           switch(strtoupper($tableName)){
               case 'ISSN':
                   break;
               case 'SSN':
                   $mustRemove[]    =    'PDF_DESCRIPTION';
                   $mustRemove[]    =    'NOTES';
                   break;
               case 'LOCATION':
                   break;
               case 'SYS_USER':
                   break;
               case 'PROD_PROFILE':
                   $mustRemove[]    =    'PROD_IMAGE';
                   break;
           }
           
           foreach($data as $key => $value){
               if(in_array($key, $mustRemove)){
                   unset($data[$key]);
               }
           }
           return $data;
   }
    
}
