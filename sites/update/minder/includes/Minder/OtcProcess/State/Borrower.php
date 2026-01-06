<?php

class Minder_OtcProcess_State_Borrower extends Minder_OtcProcess_State_AbstractLocation {

    function __construct($borrowerId = null, $via = 'S', $borrower = null)
    {
        $this->location = 'XB' . $borrowerId;
        $this->via = $via;
        $this->displayedId = $borrowerId;
        $this->isSet = !empty($borrowerId);

        $this->description = empty($borrowerId) ? '' : 'Invalid employee';

        if (!empty($borrower)) {
            $this->existed = true;
            $this->description = $borrower['LOCN_NAME'];
            $this->overdue = ($borrower['LOCN_STAT']=='OD');
            $this->closedLocation = ($borrower['LOCN_STAT']=='CL');
        }
    }
}