<?php
/**
 * Minder_Transaction
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

/**
 * Minder_Transaction
 *
 * This class provide the basic template for a Minder transaction. It has been
 * defined as abstract because there is no way to know how certain methods
 * should be implemented. You should use a subclass of this class which defines
 * these methods.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
abstract class Transaction implements Transaction_V6Interface
{
    /**
     * The code for the transaction
     *
     * @var string
     */
    public $transCode;

    /**
     * The class for the transaction
     * @var string
     */
    public $transClass;

    /**
     * The Version for the transaction
     * @var string
     */
    public $transVersion;

    public $date = null;

    public $companyId = null;

    public $orderNo = '';

    public $orderType = '';

    public $orderSubType = '';

    public $prodId = '';

    /**
     * The Family of the transaction
     * @var string
     */
    public $transFamily ;

    /**
     * Initialise the transaction
     *
     * @return void
     */
    public function __construct()
    {
        $this->transCode  = '';
        $this->transClass = '';
        $this->transFamily = '';
    }

    /**
     * Returns the transaction code for inserting into the database
     *
     * The transaction code for all transactions is stored in the transCode
     * property
     *
     * @return string
     */
    public function getTransCode()
    {
        return $this->transCode;
    }

    /**
     * Returns the transaction class for inserting into the database
     *
     * The transaction class for all transactions is stored in the transClass
     * property
     *
     * @return string
     */
    public function getTransClass()
    {
        return $this->transClass;
    }

    /**
     * Returns the transaction class for inserting into the database
     *
     * The transaction class for all transactions is stored in the transClass
     * property
     *
     * @return string
     */
    public function getTransFamily()
    {
        return $this->transFamily;
    }

    /**
     * Returns the location for inserting into the database
     *
     * This function should be defined by subclasses of Transaction
     *
     * @return string
     */
    abstract public function getLocation();

    /**
    * Common method to check field size constraints
    * 
    * @param string $paramName - name of the parameter to check constraint
    * @param integer $limit    - parameter max length
    * 
    * @throws Minder_Exception 
    */
    protected function _checkMaxLenLimit($paramName, $limit) {
        if (strlen($this->$paramName) > $limit) 
            throw new Minder_Exception(get_class($this) . '::' . $paramName . ' value length "' . $this->$paramName . '" (' . strlen($this->$paramName) . ') is greater then limit (' . $limit . ').');
    }

    /**
     * @param string $transactionResponse
     * @return Transaction_Response_Interface
     */
    public function parseResponse($transactionResponse) {
        return new Transaction_Response($transactionResponse);
    }

    /**
     * @return string
     */
    public function getWhId()
    {
        return substr($this->getLocation(), 0, 2);
    }

    /**
     * @return string
     */
    public function getLocnId()
    {
        return substr($this->getLocation(), 2, 10);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getTransCode();
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getTransClass();
    }

    /**
     * @return string
     */
    public function getDate()
    {
        //return is_null($this->date) ? date("Y-M-d H:i:s") : $this->date;
        return is_null($this->date) ? gmdate("Y-M-d H:i:s") : $this->date;
    }

    /**
     * @return string
     */
    public function getComplete()
    {
        return 'F';
    }

    /**
     * @return string
     */
    public function getErrorText()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getInstanceId()
    {
        return 'MASTER  ';
    }

    /**
     * @return string
     */
    public function getExported()
    {
        return '0';
    }

    /**
     * @return string
     */
    public function getInputSource()
    {
        return 'SSBSSKSSS';
    }

    /**
     * @return string
     */
    public function getPersonId()
    {
        return Minder::getInstance()->userId;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return Minder::getInstance()->deviceId;
    }

    /**
     * @return string
     */
    public function getProdId()
    {
        return $this->prodId;
    }

    /**
     * @return string
     */
    public function getCompanyId()
    {
        return is_null($this->companyId) ? Minder::getInstance()->defaultControlValues['COMPANY_ID'] : $this->companyId;
    }

    /**
     * @return string
     */
    public function getOrderNo()
    {
        return $this->orderNo;
    }

    /**
     * @return string
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @return string
     */
    public function getOrderSubType()
    {
        return $this->orderSubType;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return $this->getTransFamily();
    }

    public function setPickOrder(PickOrder $order) {
        $this->orderNo = $order->pickOrder;
        $this->orderType = $order->pickOrderType;
        $this->orderSubType = $order->pickOrderSubType;
        $this->companyId = $order->companyId;
    }
}
