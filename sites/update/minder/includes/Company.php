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

/**
 * Order
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class Company extends Model
{
    public $companyId;
    public $address;
    public $acn;
    public $abn;
    public $url;
    public $email;
    public $fax;
    public $phone;
    public $name;
    public $defaultCarrierId = '';

    public function __construct() {
        $this->companyId = '';
        $this->address = '';
        $this->acn = '';
        $this->abn = '';
        $this->url = '';
        $this->email = '';
        $this->fax = '';
        $this->phone = '';
        $this->name = '';
        $this->defaultCarrierId = '';
    }

    /**
     * @return string
     */
    protected function _getInvoiceDir() {
        switch (DIRECTORY_SEPARATOR) {
            case '/': //UNIX
                //return '/data';
                return '/data/minder';
            case '\\': //Win
                return 'D:/asset.rf';
            default:
                throw new Exception('Unknown OS');
        }
    }

    /**
     * @param string $pickOrder
     * @param string $uniqNo
     * @return string
     */
    public function formatInvoiceFileName($pickOrder, $uniqNo) {
        return strtoupper('Invoice_' . $pickOrder . '_' . $uniqNo . '.pdf');
    }

    /**
     * @param string $year
     * @param string $month
     * @return string
     */
    protected function _formatInvoicePath($year, $month) {
        //return $this->_getInvoiceDir() . strtoupper(DIRECTORY_SEPARATOR . $this->companyId . DIRECTORY_SEPARATOR . 'TAX_INVOICE' . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month);
        return $this->_getInvoiceDir() . strtoupper(DIRECTORY_SEPARATOR .  'TAX_INVOICE' . DIRECTORY_SEPARATOR .  $this->companyId . DIRECTORY_SEPARATOR .  $year . DIRECTORY_SEPARATOR . $month);
    }

    /**
     * @throws Exception
     * @param string $pickOrder
     * @param string $uniqNo
     * @param string $year
     * @param string $month
     * @return string
     */
    public function loadInvoiceImage($pickOrder, $uniqNo, $year, $month) {
        $filePath = $this->_formatInvoicePath($year, $month) . DIRECTORY_SEPARATOR . $this->formatInvoiceFileName($pickOrder, $uniqNo);

        if (!is_readable($filePath))
            throw new Exception('Cannot read "' . $filePath . '".');

        if (false === ($pdfImage = file_get_contents($filePath)))
            throw new Exception('Cannot read "' . $filePath . '".');

        return $pdfImage;
    }

    /**
     * @throws Exception
     * @param string $pickOrder
     * @param string $year
     * @param string $month
     * @param string $pdfImage
     * @return string
     */
    public function saveInvoiceImage($pickOrder, $year, $month, $pdfImage) {
        $invoiceDir = $this->_formatInvoicePath($year, $month);

        if (!is_writable($invoiceDir)) {
            if (!file_exists($invoiceDir)) {
                if (!mkdir($invoiceDir, 0775, true)) {
                    throw new Exception('Cannot write to "' . $invoiceDir . '" check system settings.');
                }
            } else {
                throw new Exception('Cannot write to "' . $invoiceDir . '" check system settings.');
            }
        }

        $uniqNo = uniqid();
        $filePath = $invoiceDir . DIRECTORY_SEPARATOR . $this->formatInvoiceFileName($pickOrder, $uniqNo);

        if (false === file_put_contents($filePath, $pdfImage))
            throw new Exception('Cannot write to "' . $filePath . '".');

        return $uniqNo;
    }
}
