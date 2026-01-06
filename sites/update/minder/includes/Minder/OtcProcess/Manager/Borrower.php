<?php

class Minder_OtcProcess_Manager_Borrower {
    const OVERDUE = 'OD';

    protected $_issnMapper;
    protected $_options;

    public function castLocationToBorrower(Minder_OtcProcess_State_Location $location) {
        $borrower = new Minder_OtcProcess_State_Borrower($location->getLocnId(), $location->via);

        $borrower->existed = $location->existed;
        $borrower->displayedId = $location->getWhId() . $location->getLocnId();
        $borrower->description = ($location->existed ? $location->description : 'Unknown location');

        return $borrower;
    }

    public function getBorrower($borrowerId = null, $via = 'S') {
        $borrowerData = empty($borrowerId) ? null : $this->_getMinder()->getBorrower($borrowerId);

        $borrower = new Minder_OtcProcess_State_Borrower($borrowerId, $via, $borrowerData);

        if ($borrower->existed) {
            $issnList = $this->_getLoanedIssns($borrower);
            $borrower->loanedTotal = count($issnList);

            if ($borrower->overdue) {
                $borrower->loanedTotalDescription = $this->_getOverdueAmount($issnList);
            }
        }

        return $borrower;
    }

    protected function _getOverdueAmount(Minder_Issn_Collection $issns) {
        if ($this->_getOptions()->shouldReportOverdueTools()) {
            return $issns->getOverdueAmount(Minder2_Environment::getInstance()->getSystemControls()->LOAN_PERIOD_NO_1);
        } else {
            return self::OVERDUE;
        }
    }

    /**
     * @param $borrower
     * @return Minder_Issn_Collection
     */
    protected function _getLoanedIssns(Minder_OtcProcess_State_Borrower $borrower) {
        return $this->_getIssnMapper()->getBorrowerIssns($borrower->getLocnId());
    }

    protected function _getIssnMapper() {
        if (is_null($this->_issnMapper)) {
            $this->_issnMapper = new Minder_Issn_Mapper();
        }

        return $this->_issnMapper;
    }

    protected function _getOptions() {
        if (is_null($this->_options)) {
            $this->_options = new Minder2_Options();
        }

        return $this->_options;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}