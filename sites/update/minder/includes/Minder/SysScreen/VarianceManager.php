<?php

class Minder_SysScreen_VarianceManager {
    public function getAll() {
        $result = new Minder_SysScreen_VarianceList();

        foreach ($this->_getOptionManager()->getScreenVariances() as $option) {
            $newVariance = new Minder_SysScreen_Variance();
            $newVariance->primaryScreen = $option->CODE;
            $newVariance->variancesString = $option->DESCRIPTION2;

            $result->add($newVariance);
        }

        return $result;
    }

    /**
     * @param $screenName
     * @return Minder_SysScreen_InheritanceSettings
     */
    public function getInheritanceSettings($screenName) {
        $result = new Minder_SysScreen_InheritanceSettings();
        $option = $this->_getOptionManager()->getInheritanceOption($screenName);

        if (!empty($option)) {
            $result->settingsString = $option->DESCRIPTION2;
        }

        return $result;
    }

    protected function _getOptionManager() {
        return new Minder2_Options();
    }
}