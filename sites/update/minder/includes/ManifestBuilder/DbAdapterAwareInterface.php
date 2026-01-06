<?php

interface ManifestBuilder_DbAdapterAwareInterface {
    /**
     * @param Zend_Db_Adapter_Abstract $dbAdapter
     * @return ManifestBuilder_DbAdapterAwareInterface
     */
    public function setDbAdapter(Zend_Db_Adapter_Abstract $dbAdapter);

    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDbAdapter();
}