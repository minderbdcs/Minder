<?php

class ManifestBuilder_TnT_ConsignmentManager {
    /**
     * @var ManifestBuilder_Table_PickDespatch
     */
    protected $_pickDespatchManager;

    /**
     * @var ManifestBuilder_Table_PickOrder
     */
    protected $_pickOrderManager;

    /**
     * @var ManifestBuilder_Table_ProdProfile
     */
    protected $_prodProfileManager;

    /**
     * @var ManifestBuilder_TnT_ItemManager
     */
    protected $_itemManager;

    /**
     * @var ManifestBuilder_Table_CarrierDepot
     */
    protected $_carrierDepotManager;

    public function getConsignments(ManifestBuilder_TnT_CarrierAccount $carrierAccount, ManifestBuilder_TnT_Manifest $manifest) {
        $result = array();

        foreach ($this->_getDespatches($carrierAccount, $manifest->getManifestId()) as $despatch) {
            $newConsignment = new ManifestBuilder_TnT_Consignment();
            $newConsignment->setPickDespatch($despatch);
            $newConsignment->setPickOrder($this->_getFirstDespatchedOrder($despatch));
            $newConsignment->setContainsDangerousGoods($this->_containsDangerousGoods($despatch));
            $newConsignment->setItems($this->_getConsignmentItems($despatch));
            $newConsignment->setManifest($manifest);
            $newConsignment->setCarrierService($carrierAccount->findCarrierService($despatch));
            $newConsignment->setCarrierDepot($this->_getCarrierDepot($despatch));

            $result[] = $newConsignment;
        }

        return $result;
    }

    /**
     * @param ManifestBuilder_TnT_CarrierAccount $carrierAccount
     * @param $manifestId
     * @return ManifestBuilder_Model_PickDespatch[]|Zend_Db_Table_Rowset_Abstract
     */
    protected function _getDespatches(ManifestBuilder_TnT_CarrierAccount $carrierAccount, $manifestId) {
        return $this->_getPickDespatchManager()->getDespatches($carrierAccount, $manifestId);
    }

    protected function _getPickDespatchManager() {
        if (empty($this->_pickDespatchManager)) {
            $this->_pickDespatchManager = new ManifestBuilder_Table_PickDespatch();
        }

        return $this->_pickDespatchManager;
    }

    protected function _getFirstDespatchedOrder(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getPickOrderManager()->getFirstDespatchedOrder($pickDespatch);
    }

    protected function _getPickOrderManager() {
        if (empty($this->_pickOrderManager)) {
            $this->_pickOrderManager = new ManifestBuilder_Table_PickOrder();
        }

        return $this->_pickOrderManager;
    }

    protected function _containsDangerousGoods(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getProdProfileManager()->getDespatchedProductsWithDangerousGoodsAmount($pickDespatch) > 0;
    }

    /**
     * @return ManifestBuilder_Table_ProdProfile
     */
    protected function _getProdProfileManager() {
        if (empty($this->_prodProfileManager)) {
            $this->_prodProfileManager = new ManifestBuilder_Table_ProdProfile();
        }

        return $this->_prodProfileManager;
    }

    protected function _getConsignmentItems(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getItemManager()->getItems($pickDespatch);
    }

    protected function _getItemManager() {
        if (empty($this->_itemManager)) {
            $this->_itemManager = new ManifestBuilder_TnT_ItemManager();
        }

        return $this->_itemManager;
    }

    protected function _getCarrierDepot(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getCarrierDepotManager()->getDespatchCarrierDepot($pickDespatch);
    }

    protected function _getCarrierDepotManager() {
        if (empty($this->_carrierDepotManager)) {
            $this->_carrierDepotManager = new ManifestBuilder_Table_CarrierDepot();
        }

        return $this->_carrierDepotManager;
    }


/************ Added by Akhil ************/
    protected function _getConsignmentArticles(ManifestBuilder_Model_PickDespatch $pickDespatch) {
        return $this->_getItemManager()->getItems($pickDespatch);
    }

    protected function _getArticlesManager() {
        if (empty($this->_articlesManager)) {
            $this->_articlesManager = new ManifestBuilder_TnT_ArticlesManager();
        }

        return $this->_articlesManager;
    }


}
