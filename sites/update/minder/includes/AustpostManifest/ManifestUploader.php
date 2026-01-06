<?php

class AustpostManifest_ManifestUploader {
    
    protected function getSftpParams($carrierId) {
        $db = AustpostManifest::getInstance()->getDb();
        $ftpParamsSelect = new Zend_Db_Select($db);
        $ftpParamsSelect->from('CARRIER', array('FTP_IP_ADDRESS', 'FTP_USER', 'FTP_PASSWORD'))
                            ->where('CARRIER_ID = ?', $carrierId);
        
        $paramRow = $db->fetchRow($ftpParamsSelect);
        
        if (empty($paramRow))
            throw new AustpostManifest_ManifestUploader_NoFtpParams_Exception('Cannot get sFTP params for carrier "' . $carrierId . '". ');
        
        return array($paramRow['FTP_IP_ADDRESS'], '22', $paramRow['FTP_USER'], $paramRow['FTP_PASSWORD']);
    }
    
    public function upload($manifests) {
        $amInstance = AustpostManifest::getInstance();
        foreach ($manifests as $carrierId => $carrierManifests) {
            try {
                $amInstance->info('Starting manifest apload for "' . $carrierId . '" ...');
                
                list($host, $port, $login, $pass)= $this->getSftpParams($carrierId);
            
                $amInstance->info('connecting to "' . $login . '@' . $host . ':' . $port . '" ...');

                $sftpClient = new Net_SFTP($host, $port);
                if ($sftpClient->login($login, $pass)) {
                    $amInstance->info('connected successfully ...');
                
                    foreach ($carrierManifests as $serviceType => $filePath) {
                        $manifestFileName = basename($filePath);
                        $amInstance->info('uploading manifest "' . $manifestFileName . '" ...');
                    
                        if (false === $sftpClient->put($manifestFileName, file_get_contents($filePath))) {
                            $amInstance->error('Error during file uploading: ' . $sftpClient->getLastSFTPError());
                        } else {
                            $amInstance->info('manifest ' . $manifestFileName . ' successfully uploaded.');
                        }
                    }
                
                    $sftpClient->disconnect();
                } else {
                    throw new Minder_Exception('Cannot login to sftp server');
                }

            } catch (Exception $e) {
                $amInstance->error('Error uploading manifests for Carrier "' . $carrierId . '" ' . $e->getMessage());
            }
        }
    }
}

class AustpostManifest_ManifestUploader_NoFtpParams_Exception extends AustpostManifest_Exception {}