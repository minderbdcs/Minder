<?php

class Minder_OtcProcess_CheckPeriod {
    public $applicable          = false;
    public $expireDate          = null;
    public $expired             = false;
    public $refuseIfExpired     = false;

    /**
     * @param bool $applicable
     * @param null|string $lastCheckDate
     * @param null|int $interval
     * @param null|string $intervalType
     * @param bool $refuseIfExpired
     */
    function __construct($applicable = false, $lastCheckDate = null, $interval = null, $intervalType = null, $refuseIfExpired = false)
    {
        $this->applicable       = $applicable;
        $this->refuseIfExpired  = $refuseIfExpired;

        if ($this->applicable) {
            $expireDate = $this->_calculateExpireDate($lastCheckDate, $interval, $intervalType);
            $this->expireDate = is_null($expireDate) ? null : date('Y-m-d', $expireDate);
            $this->expired = !is_null($expireDate) && ($expireDate < time());
        }
    }

    private function _calculateExpireDate($lastCheckDate, $interval, $intervalType)
    {
        $intervalMap = array('D' => 'day', 'M' => 'month', 'Q' => 'month', 'W' => 'week', 'Y' => 'year');
        $interval = ($intervalType == 'Q') ? $interval * 3 : $interval;

        $result = strtotime($lastCheckDate . ' + ' . $interval . ' ' . $intervalMap[$intervalType]);
        return $result === false ? null : $result;

    }

}