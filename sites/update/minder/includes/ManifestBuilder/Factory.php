<?php

class ManifestBuilder_Factory {
    public function doBuild($format, Zend_Db_Adapter_Abstract $dbAdapter) {

        switch (strtoupper($format)) {
            case 'AUSTPOST':
                $result = $this->buildAustpost($dbAdapter);
                break;
            case 'COURIERP':
            case 'CPLEASE':
                $result = $this->buildCourierP($dbAdapter);
                break;
            case 'TNT':
                $result = $this->buildTnT($dbAdapter);
                break;
            default:
                throw new Exception('Unsupported ManifestFormat "' . $format . '".');
        }

        return $result->init();
    }

    protected function buildAustpost(Zend_Db_Adapter_Abstract $dbAdapter) {
        $result = new ManifestBuilder_AustPost();

        $carrierServiceProvider = new ManifestBuilder_AustPost_Provider_CarrierService();
        $carrierServiceProvider->setDbAdapter($dbAdapter);

        $consignmentProvider = new ManifestBuilder_AustPost_Provider_Consignment();
        $consignmentProvider->setDbAdapter($dbAdapter);

        $articleProvider = new ManifestBuilder_AustPost_Provider_Article();
        $articleProvider->setDbAdapter($dbAdapter);

        $itemProvider = new ManifestBuilder_AustPost_Provider_Item();
        $itemProvider->setDbAdapter($dbAdapter);

        $carrierProvider = new ManifestBuilder_Provider_Carrier();
        $carrierProvider->setDbAdapter($dbAdapter);

        $carrierServiceTable = new ManifestBuilder_Table_CarrierService();
        $carrierServiceTable->setDefaultAdapter($dbAdapter);

        $merchantLocationProvider = new ManifestBuilder_AustPost_Provider_MerchantLocation($dbAdapter, $carrierProvider, $carrierServiceTable);

        return $result
            ->setCarrierServiceProvider($carrierServiceProvider)
            ->setConsignmentProvider($consignmentProvider)
            ->setArticleProvider($articleProvider)
            ->setItemProvider($itemProvider)
            ->setMerchantLocationProvider($merchantLocationProvider);
    }

    protected function buildCourierP(Zend_Db_Adapter_Abstract $dbAdapter) {
        $result = new ManifestBuilder_CourierP();

        $carrierProvider = new ManifestBuilder_Provider_Carrier();
        $carrierProvider->setDbAdapter($dbAdapter);
        $carrierServiceProvider = new ManifestBuilder_AustPost_Provider_CarrierService();
        $carrierServiceProvider->setDbAdapter($dbAdapter);
        $carrierServiceTable = new ManifestBuilder_Table_CarrierService();
        $carrierServiceTable->setDefaultAdapter($dbAdapter);
        $carrierAccountManager = new ManifestBuilder_CourierP_CarrierAccountManager($carrierProvider, $carrierServiceTable, $dbAdapter);

        return $result
            ->setCarrierProvider($carrierProvider)
            ->setCarrierServiceProvider($carrierServiceProvider)
            ->setCarrierAccountManager($carrierAccountManager);
    }

    protected function buildTnT(Zend_Db_Adapter_Abstract $dbAdapter) {
        $result = new ManifestBuilder_TnT();

        $carrierProvider = new ManifestBuilder_Provider_Carrier();
        $carrierProvider->setDbAdapter($dbAdapter);
        $carrierServiceProvider = new ManifestBuilder_AustPost_Provider_CarrierService();
        $carrierServiceProvider->setDbAdapter($dbAdapter);
        $carrierServiceTable = new ManifestBuilder_Table_CarrierService();
        $carrierServiceTable->setDefaultAdapter($dbAdapter);
        $carrierAccountManager = new ManifestBuilder_TnT_CarrierAccountManager($carrierProvider, $carrierServiceTable, $dbAdapter);

        return $result
            ->setCarrierProvider($carrierProvider)
            ->setCarrierServiceProvider($carrierServiceProvider)
            ->setCarrierAccountManager($carrierAccountManager);
    }
}
