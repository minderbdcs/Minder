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
 *
 * @method printLocationLabel($data)
 * @method printIssnLabel($data)
 * @method printDespatchAddressLabel($data, $prnType)
 * @method printToolLabel($data)
 * @method printPickLabel(array $data)
 * @method printGrnLabel(array $data)
 * @method printLogonLabel(array $data)
 * @method printProductLabel($data, $labelFormat = 'PRODUCT_LABEL')
 * @method printCostCentreLabel($data)
 * @method printPickBlock($data)
 */
abstract class Minder_Printer_Abstract
{
    public  $data;

    protected $_errno;
    protected $_errstr;

    protected $printer;
    protected $minder;

    public function __construct($printer = null)
    {
        $this->minder = Minder::getInstance();

        if ($printer == null) {
            $this->printer = $this->minder->limitPrinter;
        } else {
            $this->printer = $printer;
        }
    }

    /**
     * Store data into file
     *
     * @param string $fileName
     * @param string $data
     * @return boolean
     */
    abstract function save($fileName, $data);

    /**
     * @abstract
     * @param array $data
     * @return boolean
     */
    abstract function printGrnOrderLabel($data);
    
    /**
    * @desc get error number
    */
    public function getErrorNo() {
        return $this->_errno;
    }
    /**
    * @desc get error string
    */
    public function getErrorStr() {
        return $this->_errstr;
    }

    public function printPdfImage($pdfImage) {
        $workDir = $this->minder->getPrinterDir($this->printer);
        if (!is_writable($workDir)) {
            throw new Minder_Exception('Cannot print Pdf Image on "' . $this->printer . '" device. Check write permissions.');
        }

        //$filePath = tempnam($workDir, 'invoice_pdf_image.');
        $filePath = tempnam($workDir, 'invoice.') . ".pdf";
        if (false === file_put_contents($filePath, $pdfImage)) {
            throw new Minder_Exception('Cannot write Pdf Image into "' . $this->printer . '" devices working directory.');
        }
        if (false === chmod($filePath, 0640)) {
            throw new Minder_Exception('Cannot chmod Pdf in "' . $this->printer . '" devices working directory.');
        }

        $sql = "
            INSERT INTO PRINT_REQUESTS (DEVICE_ID, PRN_TYPE, REQUEST_STATUS, PRN_DATA, BASE_FILE_NAME, PERSON_ID, FROM_DEVICE_ID)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        if (false === $this->minder->execSQL($sql, array($this->printer, 'INVOICE_PDF', 'NQ', '', basename($filePath), $this->minder->userId, $this->minder->deviceId))) {
            throw new Minder_Exception('Cannot create PRINT_REQUESTS record: ' . $this->minder->lastError);
        }
    }

    public function getPrinter() {
        return $this->printer;
    }

    abstract public function printBorrowerLabel($borrowerId, $labelQty = null);
}

