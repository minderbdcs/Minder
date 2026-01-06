<?php

/**
 * @property string url
 * @property int port
 * @property string userName
 * @property string password
 */
class Minder_ReportManager_Client {
    protected $_url  = 'http://localhost/cgi-bin/repwebserver.dll';
    protected $_port = 80;

    protected $_userName = 'Admin';
    protected $_password = '';

    protected $_connectionTimeout = 30;

    public function construct($url = null, $port = null) {
        if (!is_null($url)) $this->__set('url', $url);
        if (!is_null($port)) $this->__set('port', $port);
    }

    /**
     * @param array $getParams
     * @return string
     */
    protected function formatGetParamsString($getParams) {
        $tmpArray = array();
        foreach ($getParams as $paramName => $paramValue) {
            $tmpArray[] = urlencode($paramName) . '=' . urlencode($paramValue);
        }

        return implode('&', $tmpArray);
    }

    /**
     * @throws Minder_ReportManager_Client_Exception
     * @param string $url
     * @param string $port
     * @param array $params
     * @return Minder_ReportManager_Response
     */
    protected function _call($url, $params) {
        if (false === ($curlResource = curl_init($url)))
            throw new Minder_ReportManager_Client_Exception('Init connection error.');

        $connectionOptions = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url . '?' . $this->formatGetParamsString($params),
//            CURLOPT_PORT           => $this->_port,
            CURLOPT_CONNECTTIMEOUT => $this->_connectionTimeout
        );

        try {
            if (!curl_setopt_array($curlResource, $connectionOptions))
                throw new Minder_ReportManager_Client_Exception('Error setting connection options : ' . curl_error($curlResource));

            if (false === ($responseContent = curl_exec($curlResource))) {
                throw new Minder_ReportManager_Client_Exception('Error executing request: ' . curl_error($curlResource));
            }

            $statusCode = curl_getinfo($curlResource, CURLINFO_HTTP_CODE);
            if ($statusCode == 404) {
                throw new Minder_ReportManager_Client_Exception('Error executing request: 404. Requested url ' . $url . ' not found.');
            }

            $contentType = Minder_Http_Header_ContentType::fromString('Content-Type: '. curl_getinfo($curlResource, CURLINFO_CONTENT_TYPE));
            $response = new Minder_ReportManager_Response($responseContent, $contentType);

        } catch (Minder_ReportManager_Client_Exception $e) {
            curl_close($curlResource);
            throw $e;
        }

        curl_close($curlResource);

        return $response;
    }

    public function login() {
        $loginUrl = $this->_url . '/index';

        $loginParams = array(
            'username' => $this->_userName,
            'password' => $this->_password
        );

        try {
            $loginResponse = $this->_call($loginUrl, $loginParams)->getContent();
        } catch (Minder_ReportManager_Client_Exception $e) {
            throw new Minder_ReportManager_Client_Exception('Login Error: ' . $e->getMessage());
        }
        if (strstr($loginResponse, 'Incorrect user name or password') !== false)
            throw new Minder_ReportManager_Client_Exception('Login Error: Incorrect user name or password');
    }

    public function executeReport($reportParams) {
        $reportUrl = $this->_url . '/execute.pdf';
        $callParams = $reportParams;
        $callParams['username'] = $this->_userName;
        $callParams['password'] = $this->_password;
        array_change_key_case($callParams, CASE_LOWER);

        try {
            $reportResponse = $this->_call($reportUrl, $callParams);
        } catch (Minder_ReportManager_Client_Exception $e) {
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: ' . $e->getMessage());
        }

        if ($reportResponse->getContentType()->isHtml()) {
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: ' . $reportResponse->getBodyMessage());
        }

        $reportContent = $reportResponse->getContent();

        if (empty($reportContent))
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: Empty response.');

        if (strstr($reportContent, 'Incorrect user name or password') !== false)
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: Incorrect user name or password.');

        if (strstr($reportContent, 'Data alias does not exists') !== false)
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: Data alias "' . $reportParams['aliasname'] . '" does not exists.');

        if (strstr($reportContent, 'Cannot open file') !== false)
            throw new Minder_ReportManager_Client_Exception('Execute Report Error: Report "' . $reportParams['reportname'] . '" does not exists.');

        return $reportContent;
    }

    public function __get($name) {
        $name = '_' . $name;
        return $this->$name;
    }

    public function __set($name, $value) {
        $name = '_' . $name;
        switch ($name) {
            case '_url'               :
                $this->$name = rtrim($value, '/');
                break;
            case '_userName'          :
            case '_password'          :
                $this->$name = $value;
                break;
            case '_port'              :
            case '_connectionTimeout' :
                $this->$name = $value;
                break;
            default:
                $this->$name = $value;
        }
    }
}