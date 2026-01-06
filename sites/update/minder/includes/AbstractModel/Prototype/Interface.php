<?php

interface AbstractModel_Prototype_Interface {
    /**
     * @abstract
     * @param mixed $source
     * @return AbstractModel
     */
    public function getNewObject($source);

    /**
     * @abstract
     * @return AbstractModel
     */
    public function getNullObject();
}