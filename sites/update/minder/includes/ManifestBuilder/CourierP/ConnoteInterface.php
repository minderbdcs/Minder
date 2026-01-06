<?php

interface ManifestBuilder_CourierP_ConnoteInterface {
    public function addItem(ManifestBuilder_Model_Item $item, ManifestBuilder_Model_Consignment $consignment);
}