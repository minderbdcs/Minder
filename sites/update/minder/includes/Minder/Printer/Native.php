<?php

/**
 * Print ISSN labels using Printer Native commands.
 *
 * @category  Minder
 * @package   Minder
 * @author    Suhinin Dmitriy <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 * @throws    Exception
 */
class Minder_Printer_Native extends Minder_Printer_Abstract
{

    protected $tableName	=	null;
    public $_labelDescriptors = array();
    public $labelFields = array();

    public function __construct($printer = null){

        parent::__construct($printer);

    }

    public function printPickBlock($data, $intNoCopyToPrint = "") {
        return $this->labelPrint($data, 'PICK_BLOCK', $intNoCopyToPrint);
    }

    public function printPickLabel($data, $intNoCopyToPrint = "") {
        return $this->labelPrint($data, 'PICK_LABEL', $intNoCopyToPrint);
    }

    /**
     * @param array $data
     * @return boolean
     */
    function printGrnOrderLabel($data, $intNoCopyToPrint = "")
    {
        return $this->labelPrint($data, 'GRNORDER', $intNoCopyToPrint);
    }
   
   /**
   * @desc print ISSN label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printIssnLabel($data, $intNoCopyToPrint = ""){
        if (isset($data->items))
            $result = $this->labelPrint($data->items, 'ISSN', $intNoCopyToPrint);
        else
            $result = $this->labelPrint($data, 'ISSN', $intNoCopyToPrint);

        return $result;
   }
   /**
   * @desc print TEST label
   * @param array $data
   * @param string $prnType   
   * 
   * @return boolean
   */
   public function printTestLabel($data, $prnType, $intNoCopyToPrint = ""){
   	
   		$result = $this->labelPrint($data, $prnType, $intNoCopyToPrint);
   		
   		return $result;
   }
   /**
   * @desc print LOCATION label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printLocationLabel($data, $intNoCopyToPrint = ""){

   		$result = $this->labelPrint($data, 'LOCATION', $intNoCopyToPrint);
   		
   		return $result;
   }
   /**
   * @desc print LOGON label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printLogonLabel($data, $intNoCopyToPrint = ""){
   
   		$result = $this->labelPrint($data, 'LOGON', $intNoCopyToPrint);
   		
   		return $result;
   }
   /**
   * @desc print PRODUCT label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printProductLabel($data, $labelFormat = 'PRODUCT_LABEL', $intNoCopyToPrint = ""){
   		
       $result = $this->labelPrint($data, $labelFormat, $intNoCopyToPrint);
   		
   		return $result;
   }
   /**
   * @desc print GRN label
   * @param array $data  
   * 
   * @return boolean
   */
   public function printGrnLabel($data, $intNoCopyToPrint = ""){
       
       
       $data   = $this->minder->getGrnForPrint($data['GRN']);    
       $result = $this->labelPrint($data, 'GRN', $intNoCopyToPrint);
           
       return $result;
   }
   /**
   * @desc print Office Address|Mail Label|Despatch data from PICK_ORDER
   * @param array $data  
   * 
   * @return boolean
   */
   public function printAddressLabel($data, $prnType, $intNoCopyToPrint = ""){
       
//       $this->setTableName('PICK_ORDER');
       $result = $this->labelPrint($data, $prnType, $intNoCopyToPrint);
           
       return $result;
   }
   
    /**
   * @desc print data from PACK_ID table joined with other tables
   * 
   * @param array $data
   * @param string $prnType
   */
    public function printDespatchAddressLabel($data, $prnType, $intNoCopyToPrint = ""){
//        if (isset($data['PICK_DESPATCH.PICKD_CARRIER_ID']) && !empty($data['PICK_DESPATCH.PICKD_CARRIER_ID']))
//            $prnType = $prnType . '|' . $data['PICK_DESPATCH.PICKD_CARRIER_ID'];

        
        if (isset($data['CARRIER_SERVICE.CS_LABEL_TYPE']))
            $prnType = $data['CARRIER_SERVICE.CS_LABEL_TYPE'];
            
        $articleidsuffix = '';
        if (isset($data['PACK_ID.DESPATCH_LABEL_NO']))
            $articleidsuffix = substr($data['PACK_ID.DESPATCH_LABEL_NO'], 11, 13);
            
        if (isset($data['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR']))
            $data['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR'] = trim($data['PACK_ID.PACK_LAST_SEQUENCE_INDICATOR']);
        
        $data['articleidsuffix'] = $articleidsuffix;
        
        $result = $this->labelPrint($data, $prnType, $intNoCopyToPrint);
           
        return $result;    
    }
   
   /**
   * @desc print data from PROD_PROFILE table
   * 
   * @param array $data
   * @param string $prnType
   */
   public function printToolLabel($data, $intNoCopyToPrint = ""){
    
       $result = $this->labelPrint($data, 'ISSN', $intNoCopyToPrint);
           
       return $result;    
   }

    /**
     * @param array $borrowerId
     * @param null $intNoCopyToPrint
     * @return array
     */
    public function printBorrowerLabel($borrowerId, $intNoCopyToPrint = "") {
        $labelData = $this->minder->getBorrowerForPrint($borrowerId);

        if (!is_null($intNoCopyToPrint)) {
            $labelData['labelqty'] = $intNoCopyToPrint;
        }
      	if(empty($labelData['LOCATION.WH_ID'])) {
      		throw new Exception("WH_ID must be 'XB' for printing BORROWER");
      	} 
        else { 
          return $this->labelPrint($labelData, 'BORROWER', $intNoCopyToPrint);
      	}   
    }

    public function printCostCentreLabel($data) {
        $intNoCopyToPrint = $_POST["printRequest"]["labelQty"];
        return $this->labelPrint($data, 'COST_CENTRE', $intNoCopyToPrint);
    }
   
   /**
   * @desc set table name
   * @param string $tableName  
   * 
   * @return void
   */
   public function setTableName($tableName = null){
   
   		$this->tableName	=	$tableName;
   
   }

    /**
     * @desc print data through PC_LABEL procedure
     * @param array $data
     * @param string $prnType
     * @return array
     * @throws Exception
     */
   protected function labelPrint($data, $prnType, $intNoCopyToPrint = ""){
   		$data		= $this->filterData($this->tableName, $data);
   		$deviceId   = $this->printer;
   		if($deviceId != null && !empty($deviceId)) {
   			if(count($data)>0) {
   				  $result   		= false;
		        $printerFormat  = $this->printer . '_FORMAT';
		        $prnFiles       = $this->minder->getPrnFile($printerFormat);
		        $fileName       = isset($prnFiles[$prnType]) ? $prnFiles[$prnType] : null;
		        $palaceDelimiter= $this->minder->defaultControlValues['LABEL_FIELD_WRAPPER'];
		        
                $data                                                       = $this->_prepareData($data, $palaceDelimiter);
                $data                                                       = $this->_filterFields($data, $prnType);
                $data[$palaceDelimiter . 'MDRVersion' . $palaceDelimiter]   = $palaceDelimiter . 'MDRVersion' . $palaceDelimiter . '=' . Minder_Version::VERSION;
                $data[$palaceDelimiter . 'MDRRelease' . $palaceDelimiter]   = $palaceDelimiter . 'MDRRelease' . $palaceDelimiter . '=' . Minder_Version::RELEASE;

                $prnFileName= $fileName;
		        $prnData    = implode('|', $data) . '|';
		        $personId   = $this->minder->userId;    
		        $fromDeviceId   = $this->minder->deviceId;    
		        
		        //$result     = $this->minder->pcLabelPrint($deviceId, $prnType, $prnFileName, $prnData, $personId);
		        $result     = $this->minder->pcLabelPrint($deviceId, $prnType, $prnFileName, $prnData, $personId, $fromDeviceId, $intNoCopyToPrint);
		        
		        return $result;
		        
   			} else {
   				throw new Exception('Missing data for printing');
   			}
			
   		} else {
   			throw new Exception('Error while select printer');
   		}
   		
   }
   
   public function save($fileName, $data){}
   
  /**
   * @desc remove all BLOB fields
   * @param string $tableName
   * @param array  $data
   * 
   * @return array 
   */
   private function filterData($tableName, $data){
		
   		$mustRemove	=	array();
   	
   		switch(strtoupper($tableName)){
   			case 'ISSN':
   				break;
   			case 'SSN':
   				$mustRemove[]	=	'PDF_DESCRIPTION';
   				$mustRemove[]	=	'NOTES';
   				break;
   			case 'LOCATION':
   				break;
   			case 'SYS_USER':
   				break;
   			case 'PROD_PROFILE':
   				$mustRemove[]	=	'PROD_IMAGE';
   				break;
   		}
   		
   		foreach($data as $key => $value){
   			if(in_array($key, $mustRemove)){
   				unset($data[$key]);
   			}
   		}
   		return $data;
   }

    protected function _prepareData($data, $palaceDelimiter) {
        $result = array();
        if(empty($this->tableName)){
            foreach($data as $key => $value) {
                $result[$key] = $palaceDelimiter . $key . $palaceDelimiter . '=' . $value;
            }
        } else {
            foreach($data as $key => $value) {
                $result[$this->tableName . '.' . $key] = $palaceDelimiter . $this->tableName . '.' . $key . $palaceDelimiter . '=' . $value;
            }
        }

        return $result;
    }

    /**
     * @param $labelType
     * @return Minder_LabelPrinter_LabelDescriptor
     */
    protected function _getLabelDescriptor($labelType) {
        if (!isset($this->_labelDescriptors[$labelType]))
            $this->_labelDescriptors[$labelType] = new Minder_LabelPrinter_LabelDescriptor($this, $labelType);

        return $this->_labelDescriptors[$labelType];
    }

    protected function _getLabelFields($labelType) {
        if (!isset($this->labelFields[$labelType])) {
            $this->labelFields[$labelType] = $this->_fetchLabelFields($labelType);
        }

        return $this->labelFields[$labelType];
    }

    protected function _fetchLabelFields($labelType) {

        $result = array();
        foreach ($this->_getLabelDescriptor($labelType)->getSysLabelFields() as $sysLabelField) {
            /**
             * @var Minder_LabelPrinter_LabelField $sysLabelField
             */
            $result[] = $sysLabelField->hasTable() ? $sysLabelField->getTable() . '.' . $sysLabelField->getFieldName() : $sysLabelField->getFieldName();
        }

        return $result;
    }

    protected function _filterFields($data, $labelType) {
        $fields = array_flip($this->_getLabelFields($labelType));
        $result = empty($fields) ? $data: array_intersect_key($data, $fields);
        if (isset($data['DEFAULT'])) {
            $result['DEFAULT'] = $data['DEFAULT'];
        }
        return $result;
    }
}
