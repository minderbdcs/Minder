<?php

/**
 * @method info(string $message)
 * @method err(string $message)
 * @method warn(string $message)
 * @method debug(string $message)
 */
class ManifestBuilder_Logger extends Zend_Log {
    public function searchingForCarriers() {
        $this->info('Searching for Carriers which need manifests ...');
    }

    public function carriersFound($carriers) {
        $this->info('... ' . count($carriers) . ' carrier(s) found');
        $this->debug('found CARRIERS_ID and SERVICE_TYPES to build manifests for: ' . print_r($carriers, true));
    }

    public function carrierAccountsFound($accounts) {
        $this->info('... ' . count($accounts) . ' carrier account(s) found');
        $this->debug('found Carrier Accounts to build manifests for: ' . print_r($accounts, true));
    }

    public function buildingManifestFor($carrierId, $account, $serviceType) {
        $this->info('Building manifest for "' . $carrierId . '", account "' . $account . '", service type "' . $serviceType . '" ....');
    }

    public function buildingManifestForAccount($carrierId, $account) {
        $this->info('Building manifest for "' . $carrierId . '", account "' . $account . '"....');
    }

    public function rebuildingManifest($manifestId) {
        $this->info('Rebuilding manifest #"' . $manifestId . ' ....');
    }

    public function manifestBuilt($path) {
        $this->info('.... manifest is built. Manifest file: ' . $path);
    }

    public function allManifestsBuilt() {
        $this->info('All manifests are built.');
    }

    public function crit($message) {
        $this->log('Critical Error: ' . $message, static::CRIT);
    }

    public function noUserNameWarning($carrierId) {
        $this->warn('Cannot find FTP Username for carrier "' . $carrierId . '". Skipping manifest building.');
    }

    public function noMerchantLocationIdWarning($carrierId, $serviceType) {
        $this->warn('Cannot find Merchant Location ID for Carrier "' . $carrierId . '" and Service Type "' . $serviceType . '" combination. Skipping manifest building.');
    }

    public function trace($stackTrace) {
        $this->err('Trace:');
        foreach ($stackTrace as $traceEntry) {
            $this->err($traceEntry['file'] . ':' . $traceEntry['line']);
        }
    }
}