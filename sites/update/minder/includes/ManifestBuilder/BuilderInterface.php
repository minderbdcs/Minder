<?php

interface ManifestBuilder_BuilderInterface {

    /**
     * @return ManifestBuilder_BuilderInterface
     */
    public function init();

    public function build($carrierId, ManifestBuilder_Options $options);

    public function upload(array $manifestList);

    /**
     * @param ManifestBuilder_Date $date
     * @return ManifestBuilder_BuilderInterface
     */
    public function setCurrentDate(ManifestBuilder_Date $date);
}