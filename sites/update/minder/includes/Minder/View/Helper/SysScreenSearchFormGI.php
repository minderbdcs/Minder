<?php

/**
* View Helper to create Global Input search on page
*/
class Minder_View_Helper_SysScreenSearchFormGI extends Zend_View_Helper_FormElement
{
    public function sysScreenSearchFormGI($params) {
        return $this->render($params);
    }
    
    public function render($params) {
        $log = Minder_Registry::getLogger();
        try {
            
            $giFields = array();
            if (isset($params['GI_FIELDS']))
                $giFields = $params['GI_FIELDS'];
            
            if (count($giFields) < 1)
                return ''; //nothing to render

            list($screenHint, $sysScreenName)   = $this->_getHintAndScreenName($giFields);
            $paramMap                           = $this->_createParamMap($giFields);
            $paramDescriptors                   = $this->_getParamDescriptors($paramMap);
            $globalInputId                      = $this->_getGlobalInputId($params);
            $initLine                           = $this->_getInitLine($params);
            $successHandlerName                 = $sysScreenName .'OnParseSuccess';
            $errorHandlerName                   = $sysScreenName .'OnParseError';
            $customParseEventHandlers           = isset($params['custom-parse-event-handlers']) ? $params['custom-parse-event-handlers'] : false;
            $registerHandlers                   = isset($params['register-search-handlers']) ? $params['register-search-handlers'] : false;

            $bindHandlers = $this->_getBindHandlers($customParseEventHandlers, $globalInputId, $errorHandlerName, $successHandlerName);

            $log->info(__METHOD__ . ': screen supports GI search fields: ' . var_export($giFields, true));
            $log->info(__METHOD__ . ': screen requires DATA_IDENTIFIERS: ' . var_export($paramMap, true));
            $log->info(__METHOD__ . ': found DATA_IDENTIFIERS: ' . var_export($paramDescriptors, true));

            if ($registerHandlers) {
                $registry   = isset($paramMap['search-map-registry']) ? $paramMap['search-map-registry'] : 'mdrGISearchMap';
                $xhtml      = $this->_renderScript($registry, $paramMap, $paramDescriptors, $globalInputId);
            } else {
                $xhtml      = $this->_renderLegacyHandlers($errorHandlerName, $successHandlerName, $paramMap, $paramDescriptors, $globalInputId, $bindHandlers, $initLine);
            }


            if (!empty($screenHint)) {
                $xhtml .= "
                    <h3>" . $screenHint . "</h3>
                ";
            }
        } catch (Exception $e) {
            $xhtml = "
                <script type=\"text/javascript\">
                    \$(document).ready(function(){ 
                        showErrors(['" . $e->getMessage() . "']);
                    });
                </script>
            ";
        }

        return $xhtml;
    }

    /**
     * @param $fieldDesc
     * @return string
     */
    private function _formatFieldName($fieldDesc)
    {
        return empty($fieldDesc['SSV_TABLE']) ? $fieldDesc['SSV_ALIAS'] : $fieldDesc['SSV_TABLE'] . '-' . $fieldDesc['SSV_ALIAS'];
    }

    /**
     * @param $giFields
     * @return array
     */
    private function _createParamMap($giFields)
    {
        $paramMap = array();
        foreach ($giFields as $fieldDesc) {
            $name = $this->_formatFieldName($fieldDesc);

            foreach (explode('|', $fieldDesc['SSV_EXPRESSION']) as $dataId) {
                $dataId = trim($dataId);
                $param = array(
                    'dataId' => '',
                    'dataType' => '',
                    'fieldName' => 'SEARCH-' . $name,
                    'sysScreenName' => $fieldDesc['SS_NAME'],
                );

                if ($dataId[0] === '#') {
                    $param['dataType'] = trim($dataId, '#');
                }  else {
                    $param['dataId'] = $dataId;
                }

                $paramMap[] = $param;
            }
        }
        return $paramMap;
    }

    /**
     * @param $giFields
     * @return array
     */
    private function _getHintAndScreenName($giFields)
    {
        $sysScreenName = '';
        $screenHint = '';

        foreach ($giFields as $fieldDesc) {
            $screenHint .= $fieldDesc['SSV_HINT'];
            $sysScreenName = $fieldDesc['SS_NAME'];
        }
        return array($screenHint, $sysScreenName);
    }

    /**
     * @param $paramMap
     * @return array
     */
    private function _getParamDescriptors($paramMap)
    {
        $paramDescriptors = array();
        foreach ($paramMap as $paramMapEntry) {
            if (empty($paramMapEntry['dataId'])) {
                $paramDescriptors = array_merge($paramDescriptors, $this->_symbologyPrefixDescriptor()->getTypeDescription($paramMapEntry['dataType']));
            } else {
                $paramDescriptors[] = $this->_symbologyPrefixDescriptor()->getDescription($paramMapEntry['dataId']);
            }

        }
        return $paramDescriptors;
    }

    /**
     * @return Minder_View_Helper_SymbologyPrefixDescriptor
     */
    private function _symbologyPrefixDescriptor() {
        return $this->view->getHelper('SymbologyPrefixDescriptor');
    }

    /**
     * @param $params
     * @return string
     */
    private function _getGlobalInputId($params)
    {
        $globalInputId = 'barcode';
        if (isset($params['GLOBAL_INPUT_ID'])) {
            $globalInputId = $params['GLOBAL_INPUT_ID'];
        }

        return $globalInputId;
    }

    /**
     * @param $params
     * @return string
     */
    private function _getInitLine($params)
    {
        $initLine = '';
        if (isset($params['INIT'])) {
            $initLine .= 'var initMethod = ' . $params['INIT'] . '; ';
            $initLine .= 'initMethod(); ';
        }
        return $initLine;
    }

    /**
     * @param $customParseEventHandlers
     * @param $globalInputId
     * @param $errorHandlerName
     * @param $successHandlerName
     * @return string
     */
    private function _getBindHandlers($customParseEventHandlers, $globalInputId, $errorHandlerName, $successHandlerName)
    {
        $bindHandlers = '';
        if (!$customParseEventHandlers) {
            $bindHandlers = "
                        $('#" . $globalInputId . "').bind(
                            'parse-error.search-form',
                            " . $errorHandlerName . "
                        ).bind(
                            'parse-success.search-form',
                            " . $successHandlerName . "
                        );
                ";
            return $bindHandlers;
        }
        return $bindHandlers;
    }

    /**
     * @param $errorHandlerName
     * @param $successHandlerName
     * @param $paramMap
     * @param $paramDescriptors
     * @param $globalInputId
     * @param $bindHandlers
     * @param $initLine
     * @return string
     */
    private function _renderLegacyHandlers($errorHandlerName, $successHandlerName, $paramMap, $paramDescriptors, $globalInputId, $bindHandlers, $initLine)
    {
        $parts = array();

        $parts[] = $this->_suppressDefaultHandler();
        $parts[] = $this->_errorHandlerCode($errorHandlerName);
        $parts[] = $this->_successHandlerCode($successHandlerName, $paramMap);

        $domReadyParts = array();
        $domReadyParts[] = $this->_registerDataIdentifiers($paramDescriptors, $globalInputId);
        $domReadyParts[] = $bindHandlers;
        $domReadyParts[] = $initLine;

        $parts[] = $this->_documentReady($domReadyParts);


        return "
                <script type=\"text/javascript\">
                    " . implode("\n", $parts) . "
                </script>
            ";
    }

    /**
     * @return string
     */
    private function _suppressDefaultHandler()
    {
        return "
                    function globalSearch(event) {/*suppress default globalSearch*/}
        ";
    }

    /**
     * @param $errorHandlerName
     * @return string
     */
    private function _errorHandlerCode($errorHandlerName)
    {
        return "
                    function " . $errorHandlerName . "(evt){
                        showErrors([evt.parseResult.errorMsg], 10000);
                        $('#barcode').val('').focus();
                    }
        ";
    }

    /**
     * @param $successHandlerName
     * @param $paramMap
     * @return string
     */
    private function _successHandlerCode($successHandlerName, $paramMap)
    {
        return "
                    function " . $successHandlerName . "(evt){
                        var paramMap = " . json_encode($paramMap) . ";
                        var paramDescription = evt.parseResult.paramDesc;
                        var searchField = paramMap.filter(function(entry){return entry.dataId === paramDescription.param_name;}).shift();

                        if (!searchField)
                            return;

                        var searchEvent = $.Event('do-search');
                        searchEvent.searchParams = {};
                        searchEvent.searchParams[searchField.fieldName] = paramDescription.param_filtered_value;
                        $(this).trigger(searchEvent);
                    }
        ";
    }

    /**
     * @param array $domReadyParts
     * @return string
     */
    private function _documentReady($domReadyParts = array())
    {
        return "
                    $(document).ready(function(){
                        " . implode("\n", $domReadyParts) . "
                    });
        ";
    }

    private function _renderScript($registry, $paramMap, $paramDescriptors, $globalInputId) {
        $parts = array();
        $parts[] = $this->_suppressDefaultHandler();
        $parts[] = $this->_registerSearchMap($registry, $paramMap);
        $parts[] = $this->_documentReady(array($this->_registerDataIdentifiers($paramDescriptors, $globalInputId)));

        return "
                <script type=\"text/javascript\">
                    " . implode("\n", $parts) . "
                </script>
            ";
    }


    private function _registerSearchMap($registry, $paramMap) {
        return "
                window['" . $registry . "'] = window['" . $registry . "'] || [];
                Array.prototype.push.apply(window['" . $registry . "'], " . json_encode($paramMap) . ");
            ";
    }

    /**
     * @param $paramDescriptors
     * @param $globalInputId
     * @return string
     */
    private function _registerDataIdentifiers($paramDescriptors, $globalInputId)
    {
        return "
                        var descriptors = " . json_encode($paramDescriptors) . ";
                        $('#" . $globalInputId . "').minderBarcodeInput({'barcodeDescriptors' : descriptors});
        ";
    }
}