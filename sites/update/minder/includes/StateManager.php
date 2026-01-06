<?php

class StateManager {

    public function getStates() {
        $result = array();
        foreach ($this->_getOptionsManager()->getStateOptions() as $option) {
            $state = new State();
            $state->CODE = $option->DESCRIPTION;
            $state->NAME = $option->COMMENTS;
            $result[] = $state;
        }
        return $result;
    }

    protected function _getOptionsManager() {
        return new Minder2_Options();
    }
}