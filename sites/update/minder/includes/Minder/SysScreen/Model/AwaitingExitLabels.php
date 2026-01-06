<?php
/**
 * Created by JetBrains PhpStorm.
 * User: m1x0n
 * Date: 16.11.11
 * Time: 9:13
 * To change this template use File | Settings | File Templates.
 */
 
class Minder_SysScreen_Model_AwaitingExitLabels extends Minder_SysScreen_Model {
    protected function _buildDespatchLimit($despatchId) {
        $tmpFilters = array();
        $tmpFilters += $this->_buildExpressionLimit('PACK_ID.DESPATCH_ID', $despatchId);
        return array('(' . implode(' AND ', array_keys($tmpFilters)) . ')' => array_reduce(array_values($tmpFilters), array($this, '_reduceHelper'), null));
    }

    public function setDespatchLimit($despatches) {

        $tmpConditions = array();
        $tmpArgs       = array();
        foreach ($despatches as $tmpRow) {
            $tmpLimit        = $this->_buildDespatchLimit($tmpRow['DESPATCH_ID']);
            $tmpConditions[] = key($tmpLimit);
            $tmpArgs         = array_merge($tmpArgs, current($tmpLimit));
        }

        $this->setConditions(array('(' . implode(' OR ', $tmpConditions) . ')' => $tmpArgs));
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $this->conditions));
    }

/**
     * @param Minder_Printer_Abstract $printer
     * @return Minder_JSResponse
     */
    public function printLabel($printer) {
        $printResult = new Minder_JSResponse();
        $tempResult  = new Minder_JSResponse();

        $rowsAmount = count($this);
        if ($rowsAmount < 1) {
            $tempResult->warnings[] = 'Now rows to print.';
            return $tempResult;
        }

        $printedLabelsAmount = 0;
        foreach ($this->selectArbitraryExpression(0, $rowsAmount, 'DISTINCT PACK_ID.DESPATCH_ID, PACK_ID.PACK_ID') as $pack) {

            if (false === ($labelData = $this->getPackLabelData($pack['DESPATCH_ID']))) {
                throw new Minder_Exception('Pack #' . $pack['DESPATCH_ID'] . ' does not exists.');
            }

            if (count($labelData) < 1) {
                throw new Minder_Exception('Pack #' . $pack['DESPATCH_ID'] . ' does not exists.');
            }

            $printTools = new Minder_PackIdPrintTools();
            $tempResult = $printTools->reprintLabel($pack['PACK_ID'], $printer);

            $printResult->messages[$printedLabelsAmount] = $tempResult->messages;

            $printedLabelsAmount++;
        }

        return $printResult;
    }

    public function getPackLabelData($despatchId)
    {
        $tmpFilters  = $this->_buildExpressionLimit('DESPATCH_ID', $despatchId);

        $sql  = 'SELECT * FROM PACK_ID WHERE (' . implode(' AND ', array_keys($tmpFilters)) . ')';
        $args = array_reduce(array_values($tmpFilters), array($this, '_reduceHelper'), null);

        array_unshift($args, $sql);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssocExt'), $args);
    }

}
