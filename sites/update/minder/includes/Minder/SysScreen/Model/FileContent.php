<?php

class Minder_SysScreen_Model_FileContent extends Minder_SysScreen_Model {
    protected $_items = null;

    function __wakeup()
    {
        $this->_items = null;
    }

    public function setConditions($conditions = array())
    {
        $this->_items = null;
        return parent::setConditions($conditions);
    }

    public function addConditions($conditions = array())
    {
        $this->_items = null;

        if (isset($conditions['search-line'])) {
            $this->conditions['search-line'] = $conditions['search-line'];
        } else {
            parent::addConditions($conditions);
        }
        return $this;
    }

    public function removeConditions($conditions = array())
    {
        $this->_items = null;
        return parent::removeConditions($conditions);
    }


    protected function _getFileList() {
        $conditions = $this->getConditions();

        return isset($conditions['FULLNAME']) && is_array($conditions['FULLNAME']) ? $conditions['FULLNAME'] : array();
    }

    protected function _buildPKeyValue($item) {
        $values = array();
        foreach ($this->pkeys as $pkey) {
            $values[] = isset($item[$pkey['SSV_NAME']]) ? $item[$pkey['SSV_NAME']] : '';
        }

        return implode(self::$pKeySeparator, $values);
    }

    protected function _isValidLine($line) {

        if (!empty($this->conditions['search-line'])) {
            $tmpStr = implode('', $line);
            if (false === stristr($tmpStr, $this->conditions['search-line']))
                return false;
        }

        foreach ($this->conditions as $condition => $values) {
            if (in_array($condition, array('search-line', 'FULLNAME')) ) {
                continue;
            }
            $fieldName = trim($condition, '!');

            if (!isset($line[$fieldName])) {
                continue;
            }

            if ($fieldName == $condition) {
                if (!in_array($line[$fieldName], $values)) {
                    return false;
                }
            } else {
                if (in_array($line[$fieldName], $values)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function _fetchItems() {
        $result = array();
        $lineNo = 1;

        $csvReader = new Minder_Reader_Csv('');
        foreach ($this->_getFileList() as $filePath) {
            $csvReader->setFilePath($filePath);

            try {
                $tmpLine = array();
                foreach ($csvReader as $fileLine) {

                    foreach ($fileLine as $fieldNo => $value) {
                        $tmpLine['FIELD' . ($fieldNo + 1)] = $value;
                    }
                    $tmpLine['FULLNAME'] = $filePath;
                    $tmpLine['LINENO']   = $lineNo++;

                    if ($this->_isValidLine($tmpLine)) {
                        $tmpLine[$this->pkeysExpressionAlias] = $this->_buildPKeyValue($tmpLine);
                        $result[] = $tmpLine;
                    }
                }
            } catch (Exception $e) {
                //do nothing
            }
        }

        return $result;
    }

    public function count()
    {
        if (is_null($this->_items)) {
            $this->_items = $this->_fetchItems();
        }

        return count($this->_items);
    }

    protected function _getItems($rowOffset, $itemCountPerPage) {
        if (is_null($this->_items))
            $this->_items = $this->_fetchItems();

        return array_slice($this->_items, $rowOffset, $itemCountPerPage);
    }

    public function getItems($rowOffset, $itemCountPerPage, $getPKeysOnly = false)
    {
        $result = array();

        if ($getPKeysOnly) {
            foreach ($this->_getItems($rowOffset, $itemCountPerPage) as $fileInfo) {
                $result[$fileInfo[$this->pkeysExpressionAlias]] = array($this->pkeysExpressionAlias => $fileInfo[$this->pkeysExpressionAlias]);
            }
        } else {
            foreach ($this->_getItems($rowOffset, $itemCountPerPage) as $fileInfo) {
                $result[$fileInfo[$this->pkeysExpressionAlias]] = $fileInfo;
            }
        }

        return $result;
    }

    public function makeConditionsFromId($ids = '', $exlude = false)
    {
        if (!is_array($ids))
            $ids = array($ids);

        $result = array();

        foreach (array_map(array($this, '_mapPrimaryIdValue'), $ids) as $idsMap) {
            foreach ($idsMap as $fieldName => $fielsValue) {
                $condition = $exlude ? '!' . $fieldName : $fieldName;

                if (!isset($result[$condition]))
                    $result[$condition] = array();

                $result[$condition][] = $fielsValue;
            }
        }

        return $result;
    }

    public function getReportItems($rowOffset, $itemCountPerPage) {
        $result = array();

        foreach ($this->_getItems($rowOffset, $itemCountPerPage) as $item) {
            unset($item['FULLNAME']);
            unset($item['LINENO']);
            unset($item[$this->pkeysExpressionAlias]);
            $result[] = $item;
        }

        return $result;
    }

}