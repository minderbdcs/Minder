<?php

interface ManifestBuilder_LoggerAwareInterface {
    /**
     * @param ManifestBuilder_Logger $logger
     * @return ManifestBuilder_LoggerAwareInterface
     */
    public function setLogger(ManifestBuilder_Logger $logger);

    /**
     * @return ManifestBuilder_Logger
     */
    public function getLogger();
}