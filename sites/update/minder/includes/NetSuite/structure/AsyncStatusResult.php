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
 * AsyncStatusResult map NetSuite datatype
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */

class NetSuite_AsyncStatusResult {
    public $jobId;
    public $status;
    public $percentCompleted;
    public $estRemainingDuration;

    public function __construct(  $jobId, $status, $percentCompleted, $estRemainingDuration) {
        $this->jobId = $jobId;
        $this->status = $status;
        $this->percentCompleted = $percentCompleted;
        $this->estRemainingDuration = $estRemainingDuration;
    }
}?>