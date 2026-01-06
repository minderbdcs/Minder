<?php


class Minder_SysScreen_Model_FileSystem extends Minder_SysScreen_Model
{
    protected $_items = null;
    protected $_aliasMap = array();

    public function setConditions($conditions = array())
    {
        $this->_items = null;
        return parent::setConditions($conditions);
    }

    public function addConditions($conditions = array())
    {
        $this->_items = null;
        return parent::addConditions($conditions);
    }

    public function removeConditions($conditions = array())
    {
        $this->_items = null;
        return parent::removeConditions($conditions);
    }

    public function getFileTypes()
    {
        // where to store values
        $fileTypesList = array('0' => 'All',
            '1' => 'CSV',
            '2' => 'XLS',
            '3' => 'XML');

        return $fileTypesList;
    }

    public function getFileFolders() {
        $sql = 'SELECT * FROM OPTIONS WHERE GROUP_CODE = ?';
        $fileFolders = $this->_getMinder()->fetchAllAssoc($sql, 'FILEFOLDER');

        if (empty($fileFolders))
            throw new Minder_Exception('No FILEFORLDER Options is defined. Check system setup.');

        return $fileFolders;
    }

    public function getFoldersInfo() {
        $folders = array();
        foreach($this->getFileFolders() as $item) {
            array_push($folders, array('path'  => $item['DESCRIPTION'],
                'value' => $item['DESCRIPTION'],
                'descr' => $item['DESCRIPTION2'])
            );
        }

        return $folders;
    }

    protected function _getFilePathes()
    {
        $result = array();
        $folders = array();

        foreach ($this->getFileFolders() as $record) {

            if ($record['CODE'] == $this->conditions['path']) {
                $result[] = $record['DESCRIPTION'];
            } else {
                $folders[] = $record['DESCRIPTION'];
            }
        }

        return empty($result) ? $folders : $result;
    }

    protected function _fetchFieldAlias($fieldName) {
        if (isset($this->_aliasMap[$fieldName]))
            return $this->_aliasMap[$fieldName];

        foreach ($this->fields as $field) {
            if ($field['SSV_NAME'] == $fieldName) {
                $this->_aliasMap[$fieldName] = $this->__getFieldAlias($field);
                return $this->_aliasMap[$fieldName];
            }
        }

        $this->_aliasMap[$fieldName] = $fieldName;
        return $fieldName;
    }

    protected function _buildPKeyValue($pathInfo) {
        $values = array();
        foreach ($this->pkeys as $pkey) {
            $values[] = isset($pathInfo[$pkey['SSV_NAME']]) ? $pathInfo[$pkey['SSV_NAME']] : '';
        }

        return implode(self::$pKeySeparator, $values);
    }

    protected function _getPathInfo($path) {
        $pathInfo = pathinfo($path);

        $pathInfo = array_change_key_case($pathInfo, CASE_UPPER);
        $pathInfo['TYPE'] = $pathInfo['EXTENSION'];
        $pathInfo['IS_DIR'] = (is_dir($path) ? 'T' : 'F');
        $pathInfo['FULLNAME'] = $path;
        $pathInfo['FILE_SIZE'] = filesize($path);
        $pathInfo['DATE_CREATED'] = date('Y-m-d H:i:s', filectime($path));
        $pathInfo['FILENAME'] = $pathInfo['BASENAME'];

        $result = array();
        foreach ($pathInfo as $fieldName => $fieldValue) {
            $result[$this->_fetchFieldAlias($fieldName)] = $fieldValue;
        }

        $result[$this->pkeysExpressionAlias] = $this->_buildPKeyValue($pathInfo);

        return $result;
    }

    protected function _isValidResult($fileInfo) {
        if ($fileInfo['IS_DIR'] == 'T')
            return false;

        foreach ($this->conditions as $condition => $values) {
            if (in_array($condition, array('path', 'type')) ) {
                continue;
            }
            $fieldName = trim($condition, '!');

            if (!isset($fileInfo[$fieldName])) {
                continue;
            }

            if ($fieldName == $condition) {
                if (!in_array($fileInfo[$fieldName], $values)) {
                    return false;
                }
            } else {
                if (in_array($fileInfo[$fieldName], $values)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function _fetchItems()
    {
        $pathes = $this->_getFilePathes();

        if (empty($pathes))
            return array();

        $result = array();
        foreach ($pathes as $path) {

            if (empty($path)) {
                continue;
            }

            $realPath = rtrim($path, ' /');

            if (false === ($files = glob($realPath . '/*'))) {
                continue;
            }

            foreach ($files as $file) {
                $fileInfo = $this->_getPathInfo($file);

                if ($this->_isValidResult($fileInfo)) {
                    if(isset($this->conditions['type'])) {
                        $pattern = ($this->conditions['type'] == 'All') ? 'All' : ltrim(strtolower($this->conditions['type']), '.');

                        if ($pattern == 'All') {
                            $result[] = $fileInfo;
                        }
                        else {
                            if ($fileInfo['TYPE'] == $pattern) {
                                $result[] = $fileInfo;
                            }
                        }
                    }

                    else {
                        $result[] = $fileInfo;
                    }
                }
            }
        }

        return $result;
    }

    protected function _getItems($rowOffset, $itemCountPerPage) {
        if (is_null($this->_items))
            $this->_items = $this->_fetchItems();

        return array_slice($this->_items, $rowOffset, $itemCountPerPage);
    }

    public function count()
    {
        if (is_null($this->_items))
            $this->_items = $this->_fetchItems();

        return count($this->_items);
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

    function __wakeup()
    {
        $this->_items = null;
        $this->_aliasMap = array();
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

    protected function _getItemFullPath($item) {
        return $item['FULLNAME'];
    }

    public function getFullFileNames($offset, $count)
    {
        $itemsList = $this->getItems($offset, $count);

        if (empty($itemsList))
            return array();

        return array_map(array($this, '_getItemFullPath'), $itemsList);
    }

    protected function makeConditionsFromSearchField($fieldDescription)
    {
        $conditionString = '';
        $conditionArgs = '';

        if (!empty($fieldDescription['SEARCH_VALUE'])) {
            switch (strtoupper($fieldDescription['SSV_NAME'])) {
                case 'FOLDER':
                    $conditionString = 'path';
                    $conditionArgs = $fieldDescription['SEARCH_VALUE'];
                    break;
                case 'FILETYPE':
                    $conditionString = 'type';
                    $conditionArgs = $fieldDescription['SEARCH_VALUE'];
                    break;
            }
        }

        return array($conditionString, $conditionArgs, 'CONDITION_STRING' => $conditionString, 'CONDITION_ARGS' => $conditionArgs);
    }


}