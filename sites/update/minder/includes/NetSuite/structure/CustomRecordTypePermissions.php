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
 * CustomRecordTypePermissions map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_CustomRecordTypePermissions {
    public $permittedRole; //NetSuite_RecordRef
    public $permittedLevel;
    public $restriction;
    public $defaultForm; //NetSuite_RecordRef
    public $restrictForm;
    public $searchForm; //NetSuite_RecordRef
    public $listView; //NetSuite_RecordRef
    public $listViewRestricted;
    public $dashboardView; //NetSuite_RecordRef
    public $restrictDashboardView;
    public $sublistView; //NetSuite_RecordRef
    public $restrictSublistView;

    public function __construct(  NetSuite_RecordRef $permittedRole, $permittedLevel, $restriction, NetSuite_RecordRef $defaultForm, $restrictForm, NetSuite_RecordRef $searchForm, NetSuite_RecordRef $listView, $listViewRestricted, NetSuite_RecordRef $dashboardView, $restrictDashboardView, NetSuite_RecordRef $sublistView, $restrictSublistView) {
        $this->permittedRole = $permittedRole;
        $this->permittedLevel = $permittedLevel;
        $this->restriction = $restriction;
        $this->defaultForm = $defaultForm;
        $this->restrictForm = $restrictForm;
        $this->searchForm = $searchForm;
        $this->listView = $listView;
        $this->listViewRestricted = $listViewRestricted;
        $this->dashboardView = $dashboardView;
        $this->restrictDashboardView = $restrictDashboardView;
        $this->sublistView = $sublistView;
        $this->restrictSublistView = $restrictSublistView;
    }
}?>