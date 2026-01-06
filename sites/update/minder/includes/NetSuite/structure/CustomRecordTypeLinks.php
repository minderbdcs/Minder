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
 * CustomRecordTypeLinks map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecordTypeLinks {
    public $linkCenter; //NetSuite_RecordRef
    public $linkSection; //NetSuite_RecordRef
    public $linkLabel;

    public function __construct(  NetSuite_RecordRef $linkCenter, NetSuite_RecordRef $linkSection, $linkLabel) {
        $this->linkCenter = $linkCenter;
        $this->linkSection = $linkSection;
        $this->linkLabel = $linkLabel;
    }
}?>