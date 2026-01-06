<?php

class Minder_ManifestRoutines {

    protected $_createdPdfId = '';
    protected $_session      = null;

    public static function getSupportedCarriersList() {
        return Minder::getInstance()->findList('SELECT CODE, CODE FROM OPTIONS WHERE GROUP_CODE = ? ORDER BY 1', 'MANIFESTSH');
    }

    /**
     * @throws Minder_ManifestRoutines_Exception
     * @param string $manifestId
     * @return string
     */
    protected function _findCarrierId($manifestId) {
        $result = Minder::getInstance()->findValue('SELECT PICKD_CARRIER_ID FROM PICK_DESPATCH WHERE PICKD_MANIFEST_ID = ?', $manifestId);

        if (empty($result))
            throw new Minder_ManifestRoutines_Exception('No CARRIER_ID found for Manifest #' . $manifestId);

        return $result;
    }

    /**
     * @throws Minder_ManifestRoutines_Exception
     * @param string $carrierId
     * @return string
     */
    protected function _getScriptToRun($carrierId) {
        $result = Minder::getInstance()->findValue('SELECT DESCRIPTION FROM OPTIONS WHERE GROUP_CODE = ? AND CODE = ?', 'MANIFESTSH', $carrierId);

        if (empty($result))
            throw new Minder_ManifestRoutines_Exception('No Manifest Script specified for CARRIER #' . $carrierId . ' in options table. Check system setup.');

        return $result;
    }

    /**
     * @return Zend_Session_Namespace
     */
    protected function _getSession() {
        if (is_null($this->_session))
            $this->_session = new Zend_Session_Namespace('manifests');

        return $this->_session;
    }

    /**
     * @param string $pathToPdf
     * @return string - PDF image ID
     */
    protected function _storeGenreratedPdfInSession($pathToPdf) {
        $storedManifests = $this->_getStoredManifests();

        $result = uniqid('manifest', true);

        $storedManifests[$result] = $pathToPdf;
        $this->_setStoredManifests($storedManifests);

        return $result;
    }

    /**
     * @param  $storedManifests
     * @return void
     */
    protected function _setStoredManifests($storedManifests) {
        $session         = $this->_getSession();
        $session->storedManifests = $storedManifests;
    }

    /**
     * @return array
     */
    protected function _getStoredManifests() {
        $session         = $this->_getSession();
        return $session->storedManifests;
    }

    /**
     * @param string $imageId
     * @return null|string
     */
    public function getStoredPdfImage($imageId) {
        $storedManifests = $this->_getStoredManifests();

        if (!isset($storedManifests[$imageId])) return null;

        if (!is_readable($storedManifests[$imageId])) return null;

        return file_get_contents($storedManifests[$imageId]);
    }

    /**
     * @param string $imageId
     * @return null | string
     */
    public function getStoredPdfImageName($imageId) {
        $storedManifests = $this->_getStoredManifests();

        if (!isset($storedManifests[$imageId])) return null;

        $path_parts = pathinfo($storedManifests[$imageId]);

        return $path_parts['filename'];
    }

    /**
     * @param  $carrierId
     * @param null $manifestId
     * @return void
     */
    protected function _executeScript($carrierId, $manifestId = null) {
        $scriptToRun = $this->_getScriptToRun($carrierId);
        $scriptToRun .= ' ' . implode(' ', array(
            Minder::getInstance()->defaultControlValues['SYSTEM_TYPE'],
            $carrierId,
            'true',
            (is_null($manifestId) ? '' : $manifestId)
        ));

        $output = array();
        $lastLine = '';

        exec($scriptToRun, $output, $lastLine);

        $pathToGeneratedPdf = array_pop($output);

        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $output, $lastLine, $pathToGeneratedPdf));

        if (is_readable($pathToGeneratedPdf)) return $this->_storeGenreratedPdfInSession($pathToGeneratedPdf);

        return null;
    }

    /**
     * @param string $manifestId
     * @return null | string
     */
    public function reRunManifestBuild($manifestId) {
        return $this->_executeScript($this->_findCarrierId($manifestId), $manifestId);
    }

    /**
     * @param string $carrierId
     * @return null | string
     */
    public function runManifestBuild($carrierId) {
        return $this->_executeScript($carrierId);
    }
}