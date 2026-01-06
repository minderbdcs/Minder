<?php

interface Minder_Form_Element_GridElementInterface {
    /**
     * Return amount of grid columns occupied by element
     * @abstract
     * @return int
     */
    function getWidth();

    /**
     * Set amount of grid columns occupied by element
     * @abstract
     * @param int $val
     */
    function setWidth($val);
}