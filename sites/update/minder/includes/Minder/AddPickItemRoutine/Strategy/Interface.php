<?php

interface Minder_AddPickItemRoutine_Strategy_Interface {
    /**
     * @abstract
     * @param Minder_AddPickItemRoutine_State $state
     * @return Minder_AddPickItemRoutine_State
     */
    function addPickItem($state);

}