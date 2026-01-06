<?php

class Minder_Controller_Action_Helper_BarcodeParser extends Zend_Controller_Action_Helper_Abstract 
{
    protected $minder = null;
    
    public function init() {
        parent::init();
        $this->minder = Minder::getInstance();
    }
    
    
    public function direct($rawParam, $allowedParams = array()) {
        return $this->parse($rawParam, $allowedParams);
    }
    
    public function parse($rawParam, $allowedParams = array()) {
        $descriptions = $this->buildBarcodeDescriptors($allowedParams);

        return $this->doParseBarcode($rawParam, $descriptions);
    }

    /**
     * @param $allowedParams
     * @return mixed
     */
    protected function _getParams($allowedParams)
    {
        $filter = array();
        $args = array();
        if (count($allowedParams) > 0) {
            $filter[] = 'PARAM.DATA_ID IN (' . implode(', ', array_map(create_function('$a', 'return "?";'), $allowedParams)) . ')';
            $args = array_merge($args, $allowedParams);
        }

        $where = '';
        if (count($filter)) {
            $where = ' WHERE ' . implode(' AND ', $filter);
        }

        $sql = 'SELECT * FROM PARAM' . $where;
        array_unshift($args, $sql);


        $params = call_user_func_array(array($this->minder, 'fetchAllAssoc'), $args);
        return $params;
    }

    /**
     * @param $allowedParams
     * @return array
     */
    public function buildBarcodeDescriptors($allowedParams)
    {
        $params = $this->_getParams($allowedParams);
        $descriptions = array();

        foreach ($params as $paramDescription) {
            $tmpPrefixArray = explode(';', $paramDescription['SYMBOLOGY_PREFIX']);

            if (count($tmpPrefixArray) > 1) {
                //remove last element as SYMBOLOGY_PREFIX allways ending with ';'
                unset($tmpPrefixArray[count($tmpPrefixArray) - 1]);
            }

            foreach ($tmpPrefixArray as $prefix) {
                $paramDescription['SYMBOLOGY_PREFIX'] = strtoupper(trim($prefix));
                $descriptions[] = $paramDescription;
            }
        }

        usort($descriptions, array($this, 'descriptionSorter'));
        return $descriptions;
    }

    /**
     * @param $rawParam
     * @param $descriptions
     * @return array
     */
    public function doParseBarcode($rawParam, $descriptions)
    {
        $result = array(
            'RAW_VALUE' => $rawParam,
            'PREFIX' => '',
            'PARSED_VALUE' => '',
            'VALID' => false,
            'PARAM_NAME' => ''
        );

        foreach ($descriptions as $paramDescription) {
            $prefix = '';
            $parsedValue = $rawParam;
            $paramName = $paramDescription['DATA_ID'];

            if (strlen($paramDescription['SYMBOLOGY_PREFIX']) > 0) {
                //test prefix, if needed

                if (($pos = stripos($rawParam, $paramDescription['SYMBOLOGY_PREFIX'])) === false) {
                    //prefix needed but not found, trying next param
                    continue;
                }

                //prefix found, extract it
                $prefix = $paramDescription['SYMBOLOGY_PREFIX'];
                $parsedValue = substr($parsedValue, strlen($prefix));
            }

            //now test for length
            if (strtoupper($paramDescription['FIXED_LENGTH']) == 'T') {
                if (strlen($parsedValue) != intval($paramDescription['MAX_LENGTH'])) {
                    //length does not match
                    continue;
                }
            } else {
                if (strlen($parsedValue) > intval($paramDescription['MAX_LENGTH'])) {
                    //length does not match
                    continue;
                }
            }

            //finaly test with data expression, if needed
            if (strlen($paramDescription['DATA_EXPRESSION']) > 0) {
                if (!preg_match('/(' . $paramDescription['DATA_EXPRESSION'] . ')/', $parsedValue)) {
                    //data expression does not match
                    continue;
                }
            }

            //everything is ok, param found
            $result = array(
                'RAW_VALUE' => $rawParam,
                'PREFIX' => $prefix,
                'PARSED_VALUE' => $parsedValue,
                'VALID' => true,
                'PARAM_NAME' => $paramName
            );
        }

        return $result;
    }


    private function descriptionSorter($a, $b) {
        return strlen($a['SYMBOLOGY_PREFIX']) - strlen($b['SYMBOLOGY_PREFIX']);
    }
}

class Minder_Controller_Action_Helper_BarcodeParser_Exception extends Minder_Exception {}