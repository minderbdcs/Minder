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
 * Preferences map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_Preferences {
    public $warningAsError;
    public $useConditionalDefaultsOnAdd;
    public $useConditionalDefaultsOnUpdate;
    public $disableMandatoryCustomFieldValidation;
    public $disableSystemNotesForCustomFields;
    public $ignoreReadOnlyFields;

    public function __construct(  $warningAsError, $useConditionalDefaultsOnAdd, $useConditionalDefaultsOnUpdate, $disableMandatoryCustomFieldValidation, $disableSystemNotesForCustomFields, $ignoreReadOnlyFields) {
        $this->warningAsError = $warningAsError;
        $this->useConditionalDefaultsOnAdd = $useConditionalDefaultsOnAdd;
        $this->useConditionalDefaultsOnUpdate = $useConditionalDefaultsOnUpdate;
        $this->disableMandatoryCustomFieldValidation = $disableMandatoryCustomFieldValidation;
        $this->disableSystemNotesForCustomFields = $disableSystemNotesForCustomFields;
        $this->ignoreReadOnlyFields = $ignoreReadOnlyFields;
    }
}?>