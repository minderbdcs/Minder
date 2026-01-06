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
 * NoteType map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_NoteType {
    public $name;
    public $description;
    public $isInactive;
    public $internalId;
    public $externalId;

    public function __construct(  $name, $description, $isInactive, $internalId, $externalId) {
        $this->name = $name;
        $this->description = $description;
        $this->isInactive = $isInactive;
        $this->internalId = $internalId;
        $this->externalId = $externalId;
    }
}?>