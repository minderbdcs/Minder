<?php

class Minder_OtcProcess_Manager_Consumable {
    public function getConsumable($id, $via) {
        $prodProfile = $this->_getMinder()->getProd($id);

        $result = new Minder_OtcProcess_State_Consumable($id, $via);

        if (!empty($prodProfile)) {
            $result->existed = true;
            $result->description = '[' . $id . '] ' . (empty($prodProfile['SHORT_DESC']) ? '' : $prodProfile['SHORT_DESC']);
            $result->images = $this->_getImages($prodProfile, $this->_getMinder()->defaultControlValues['COMPANY_ID']);
            $result->defaultIssueQty = empty($prodProfile['ISSUE']) ? 1 : $prodProfile['ISSUE'];
            $result->defaultIssueUom = empty($prodProfile['ISSUE_UOM']) ? 'EA' : $prodProfile['ISSUE_UOM'];

            $result->companyId       = (empty($prodProfile['COMPANY_ID']) || $prodProfile['COMPANY_ID'] == 'ALL')
                ? $this->_getMinder()->defaultControlValues['COMPANY_ID']
                : $prodProfile['COMPANY_ID'];
        }

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    protected function _getImagePath() {
        return '/minder/images/LowRes/';
    }

    private function _getImages($product, $defaultCompanyId)
    {
        $company = empty($product['COMPANY_ID']) ? $defaultCompanyId : $product['COMPANY_ID'];

        $basePath = $this->_getImagePath() . $company . '/PROD_ID/';

        return new Minder_OtcProcess_ItemImages(
            $basePath . $product['PROD_ID'] . '_0.png',
            $basePath . $product['PROD_ID'] . '_1.png',
            $basePath . $product['PROD_ID'] . '_2.png'
        );
    }
}