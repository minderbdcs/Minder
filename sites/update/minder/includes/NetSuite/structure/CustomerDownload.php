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
 * CustomerDownload map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomerDownload {
    public $file; //NetSuite_RecordRef
    public $licenseCode;
    public $remainingDownloads;
    public $expiration;

    public function __construct(  NetSuite_RecordRef $file, $licenseCode, $remainingDownloads, $expiration) {
        $this->file = $file;
        $this->licenseCode = $licenseCode;
        $this->remainingDownloads = $remainingDownloads;
        $this->expiration = $expiration;
    }
}?>