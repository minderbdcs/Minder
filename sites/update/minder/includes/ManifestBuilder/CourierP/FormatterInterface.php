<?php

interface ManifestBuilder_CourierP_FormatterInterface {
    public function init(Zend_Date $currentDate, $fileName, ManifestBuilder_Model_Company $currentCompany);

    /**
     * @param ManifestBuilder_Model_Consignment $consignment
     * @param ManifestBuilder_Model_DeliveryAddress $deliveryAddress
     * @param bool $containsDangerousGoods
     * @return ManifestBuilder_CourierP_FormatterXml_Connote
     */
    public function addConnote(ManifestBuilder_Model_Consignment $consignment, ManifestBuilder_Model_DeliveryAddress $deliveryAddress, $containsDangerousGoods);

    /**
     * @return string
     */
    public function getContent();
}