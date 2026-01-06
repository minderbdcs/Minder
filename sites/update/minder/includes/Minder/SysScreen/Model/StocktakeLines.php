<?php

class Minder_SysScreen_Model_StocktakeLines extends Minder_SysScreen_Model {
    public function makeOriginalSsnCondition(array $ssnIds) {
        if (empty($ssnIds)) {
            return array('1=2' => array());
        } else {
            return array('ISSN.ORIGINAL_SSN IN (' . substr(str_repeat('?, ', count($ssnIds)), 0, -2) . ')' => $ssnIds);
        }
    }

    protected function _getRecordIds($rowOffset, $itemCountPerPage) {
        $result = $this->selectArbitraryExpression($rowOffset, $itemCountPerPage, 'DISTINCT RECORD_ID');

        return array_map(create_function('$item', 'return $item["RECORD_ID"];'), $result);
    }

    /**
     * @param string $action
     * @param Minder_JSResponse $result
     * @return Minder_JSResponse
     */
    public function updateStocktakeAction($action, $result = null) {
        $recordIds = $this->_getRecordIds(0, count($this));

        foreach ($this->_getMinder()->updatePendingStocktake($recordIds, $action) as $id => $text) {
            if (is_array($text)) {
                $text = current($text);
                $result->errors[] = $id . ' - ' . $text;
            } else {
                $result->messages[] = 'Update pending for ' . $id . ' - ' . $text;
            }
        }

        return $result;
    }

    /**
     * @param Minder_JSResponse $result
     * @return Minder_JSResponse
     */
    public function applyVariance($result = null) {
        $recordIds = $this->_getRecordIds(0, count($this));

        if (empty($recordIds))
            return $result;

        foreach ($this->_getMinder()->applyVariance($recordIds) as $key => $message) {
            if (false !== strpos($message, 'Processed success')) {
                $result->messages[] = 'Apply variance ' . $key . ' - ' . $message;
            } else {
                $result->errors[] = 'Apply variance ' . $key . ' - ' . $message;
            }
        }

        return $result;
    }

    /**
     * @param Minder_JSResponse $result
     * @return \Minder_JSResponse
     */
    public function deleteStocktakeCount($result) {
        $recordIds = $this->_getRecordIds(0, count($this));

        if (empty($recordIds))
            return $result;

        foreach ($this->_getMinder()->deleteStocktakeCount($recordIds) as $key => $message) {
            if (false !== strpos($message, 'Processed success')) {
                $result->messages[] = 'Delete count ' . $key . ' - ' . $message;
            } else {
                $result->errors[] = 'Delete count ' . $key . ' - ' . $message;
            }
        }


        return $result;
    }
}