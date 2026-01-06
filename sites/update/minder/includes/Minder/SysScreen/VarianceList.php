<?php

class Minder_SysScreen_VarianceList extends Minder_Collection {
    /**
     * @param $screenName
     * @return Minder_SysScreen_Variance
     */
    public function getScreenVariance($screenName) {
        foreach ($this->_getVariances() as $variance) {
            if (in_array($screenName, $variance->variances)) {
                return $variance;
            }
        }

        $emptyVariance                      = new Minder_SysScreen_Variance();
        $emptyVariance->primaryScreen       = $screenName;
        $emptyVariance->variancesString     = $screenName;
        return $emptyVariance;
    }

    /**
     * @return Minder_SysScreen_Variance[]
     */
    protected function _getVariances() {
        return $this->_getData();
    }

    protected function _newItem($itemData = array())
    {
        return new Minder_SysScreen_Variance();
    }


}