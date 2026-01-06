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
 * File map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_File {
    public $name;
    public $fileType;
    public $content;
    public $folder; //NetSuite_RecordRef
    public $fileSize;
    public $url;
    public $description;
    public $isOnline;
    public $isInactive;
    public $createdDate;
    public $internalId;

    public function __construct(  $name, $fileType, $content, NetSuite_RecordRef $folder, $fileSize, $url, $description, $isOnline, $isInactive, $createdDate, $internalId) {
        $this->name = $name;
        $this->fileType = $fileType;
        $this->content = $content;
        $this->folder = $folder;
        $this->fileSize = $fileSize;
        $this->url = $url;
        $this->description = $description;
        $this->isOnline = $isOnline;
        $this->isInactive = $isInactive;
        $this->createdDate = $createdDate;
        $this->internalId = $internalId;
    }
}?>