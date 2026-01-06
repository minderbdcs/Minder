<?php

class Minder_Issn_Collection extends Minder_Collection {

    /**
     * @param Minder_Issn_Issn|Minder_Issn_Issn[] $data
     */
    public function add($data)
    {
        parent::add($data);
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_Issn_Issn($itemData);
    }

    public function getOverdueAmount($loanPeriod) {
        $result = 0;

        foreach ($this->getIterator() as $issn) {
            /**
             * @var Minder_Issn_Issn $issn
             */

            if ($issn->isOverdue($loanPeriod)) {
                $result++;
            }
        }

        return $result;
    }
}