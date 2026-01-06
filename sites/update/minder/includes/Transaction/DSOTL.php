<?php
/**
 * Transaction_DSOTL
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Dmitriy Suhinin <suhinin.dmitriy@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Include the definition of the Transaction base class
 */
require_once 'Transaction.php';



class Transaction_DSOTL Extends Transaction_DSOT
{
    //length limits for transaction params
    public static $connoteNoMaxLen     = 20;
    public static $accountNoMaxLen     = 10;    
    public static $locationIdMaxLen    = 10;    
    public static $carrierIdMaxLen     = 10;    
    public static $palletQtyMaxLen     = 4;    
    public static $palletOwnerIdMaxLen = 10;    
    public static $cartonQtyMaxLen     = 4;    
    public static $satchelQtyMaxLen    = 4;    
    public static $totalWeightMaxLen   = 5;    
    public static $totalVolumeMaxLen   = 5;    
    public static $payerFlagMaxLen     = 1;    
    public static $labelTypeMaxLen     = 1;    
    public static $serviceTypeMaxLen   = 3;
    public static $packTypeMaxLen      = 1;
    public static $printerIdMaxLen     = 2;    
    public static $serviceRecordIdMaxLen = 10;

    /**
     * @var string
     */
    public $objectId;

    public $conNoteNo       =   '';
    
    public $accountNo       =   '';
    
    public $locationId      =   '';
    
    public $carrierId       =   '';
    
    public $palletQty       =   '';
    
    public $palletOwnerId   =   'NONE      ';
    
    public $cartonQty       =   '    ';
    
    public $satchelQty      =   '';
    
    public $totalWeight     =   '';
    
    public $totalVolume     =   '';
    
    public $payerFlag       =   'S';
    
    public $labelType       =   'S';
    
    public $serviceType     =   'GEN';

    public $serviceRecordId =   '';
    
    public $packType        =   'C';
    
    public $printerId       =   '';
    
    public $labelQty;
    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = 'DSOT';
        $this->transClass = 'L';
       
    }
    
    protected function _checkMaxLenLimit($paramName, $limit) {
        if (strlen($this->$paramName) > $limit) 
            throw new Minder_Exception(get_class($this) . '::' . $paramName . ' value length "' . $this->$paramName . '" (' . strlen($this->$paramName) . ') is greater then limit (' . $limit . ').');
    }
    
    /**
     * Returns the ISSN which should be updated
     * Max 10 characters
     *
     * @return string
     */
    public function getObjectId()
    {
        //add some checks
        
        $this->_checkMaxLenLimit('conNoteNo', self::$connoteNoMaxLen);
        $this->_checkMaxLenLimit('accountNo', self::$accountNoMaxLen);
        
        //as specified in 
        //http://dev.barcoding.com.au/project/minder/wiki/Transaction_DSOT
        //and Visio-WM_TRANSACTIONS_231008.pdf
        $tmpConNoteNo = $this->conNoteNo.str_repeat(' ', self::$connoteNoMaxLen - strlen($this->conNoteNo));
        if ($this->payerFlag == 'R') {
            $tmpAccountNo = $this->accountNo.str_repeat(' ', self::$accountNoMaxLen - strlen($this->accountNo));
        } else {
            $tmpAccountNo = str_repeat(' ', self::$accountNoMaxLen);
        }
        
        return $tmpConNoteNo.$tmpAccountNo;
//        return $this->objectId;
    }

    /**
     *
     * @return string
     */
    public function getReference()
    {
        $this->_checkMaxLenLimit('palletQty', self::$palletQtyMaxLen);              //4  : 0-3
        $this->_checkMaxLenLimit('palletOwnerId', self::$palletOwnerIdMaxLen);      //10 : 4-13
        $this->_checkMaxLenLimit('cartonQty', self::$cartonQtyMaxLen);              //4  : 14-17
        $this->_checkMaxLenLimit('satchelQty', self::$satchelQtyMaxLen);            //4  : 18-21
        $this->_checkMaxLenLimit('totalWeight', self::$totalWeightMaxLen);          //5  : 22-26
        $this->_checkMaxLenLimit('totalVolume', self::$totalVolumeMaxLen);          //5  : 27-31
        $this->_checkMaxLenLimit('payerFlag', self::$payerFlagMaxLen);              //1  : 32
        $this->_checkMaxLenLimit('labelType', self::$labelTypeMaxLen);              //1  : 33
        $this->_checkMaxLenLimit('serviceType', self::$serviceTypeMaxLen);          //3  : 34-36
        $this->_checkMaxLenLimit('packType', self::$packTypeMaxLen);                //1  : 37
        $this->_checkMaxLenLimit('printerId', self::$printerIdMaxLen);              //3  : 38-40
        $this->_checkMaxLenLimit('serviceRecordId', self::$serviceRecordIdMaxLen);  //10 : 41-50
        
        return str_repeat('0', self::$palletQtyMaxLen - strlen($this->palletQty)) . $this->palletQty . 
                $this->palletOwnerId . str_repeat(' ', self::$palletOwnerIdMaxLen - strlen($this->palletOwnerId)) .
                str_repeat('0', self::$cartonQtyMaxLen - strlen($this->cartonQty)) . $this->cartonQty .
                str_repeat('0', self::$satchelQtyMaxLen - strlen($this->satchelQty)) . $this->satchelQty .
                str_repeat('0', self::$totalWeightMaxLen - strlen($this->totalWeight)) . $this->totalWeight .
                str_repeat('0', self::$totalVolumeMaxLen - strlen($this->totalVolume)) . $this->totalVolume .
                $this->payerFlag . $this->labelType .
                $this->serviceType . str_repeat(' ', self::$serviceTypeMaxLen - strlen($this->serviceType)) .
                $this->packType . $this->printerId .
                $this->serviceRecordId . str_repeat(' ', self::$serviceRecordIdMaxLen - strlen($this->serviceRecordId));
    }

    /**
     * Returns the quantity for inserting into the database
     *
     * The quantity is always the value 1
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->labelQty;
    }
    /**
     * Returns the location for inserting into the database
     *
     * The location for the AUOB transaction is empty string
     *
     * @return string
     */
    public function getLocation()
    {
        $this->_checkMaxLenLimit('locationId', self::$locationIdMaxLen);
        return $this->locationId;
    }

    /**
     * Returns the sublocation for inserting into the database
     *
     * The sublocation for the AUOB transaction is empty string
     *
     * @return string
     */
    public function getSubLocation()
    {
        $this->_checkMaxLenLimit('carrierId', self::$carrierIdMaxLen);
        return $this->carrierId;
    }
}
