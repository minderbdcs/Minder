<?php

interface ManifestBuilder_AustPost_FormatterInterface {
    /**
     * @param ManifestBuilder_AustPost_Model_ManifestHeader $manifestHeader
     */
    public function addHeader(ManifestBuilder_AustPost_Model_ManifestHeader $manifestHeader);

    /**
     * @param ManifestBuilder_AustPost_Model_PcmsManifest $pcmsManifest
     * @return ManifestBuilder_AustPost_FormatterXml_PcmsManifest
     */
    public function addPcmsManifest(ManifestBuilder_AustPost_Model_PcmsManifest $pcmsManifest);

    /**
     * @return string
     */
    public function getContent();

    public function reset();
}