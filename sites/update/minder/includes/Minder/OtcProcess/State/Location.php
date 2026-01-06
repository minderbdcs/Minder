<?php

class Minder_OtcProcess_State_Location extends Minder_OtcProcess_State_AbstractLocation {
    function __construct($locationId = null, $via = 'S', $location = null)
    {
        $this->location = $locationId;
        $this->via = $via;
        $this->isSet = !empty($locationId);

        $this->description = is_null($locationId) ? '' : 'Invalid location';

        if (!empty($location)) {
            $this->_setLocation($location);
        }
    }

    protected function _setLocation($location) {
        $this->existed = true;
        $this->description = $location['LOCN_NAME'];
        $this->closedLocation = ($location['LOCN_STAT']=='CL');
    }
}