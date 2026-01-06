<?php

class Minder_SysScreen_FileContentsBuilder
{
    protected $minder;

    protected $headers = array();
    protected $rows    = array();
    protected $file    = null;
    protected $path    = null;

    public function setFile($file) {
        $this->file = $file;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    public function getFile() {
        return $this->file;
    }

    public function getPath() {
        return $this->path;
    }

    protected function _getHeaderPatterns() {
        $result = array();
        foreach ($this->_getFileMask() as $mask) {
            $maskArray = explode('|', $mask['FILEMASK']);
            $folder = rtrim($mask['FOLDER'], '/') . '/';

            while (!empty($maskArray)) {
                $fileMask = array_shift($maskArray);
                $headerString = (string)array_shift($maskArray);
                $headerArray = explode('=', $headerString);
                $hasHeaders = isset($headerArray[1]) ? $headerArray[1] : null;

                $result[] = new Minder_SysScreen_FileContentBuilder_HeaderInfo($folder . $fileMask, $hasHeaders);
            }
        }

        return $result;
    }

    public function hasHeaders($fullFileName) {
        foreach ($this->_getHeaderPatterns() as $headerInfo) {
            /**
             * @var Minder_SysScreen_FileContentBuilder_HeaderInfo $headerInfo
             */
            if (fnmatch($headerInfo->getPath(), $fullFileName, FNM_PATHNAME | FNM_CASEFOLD))
                return $headerInfo->hasHeaders();
        }

        return false;
    }

    public function __construct() {
        $this->minder = Minder::getInstance();
    }

    protected function _mapHelper($item) {
        return $item['CODE'];
    }

    protected function _getFileMask() {
        $sql = "
            SELECT
                FILETYPE.DESCRIPTION AS FILEMASK,
                FILEFOLDER.DESCRIPTION AS FOLDER
            FROM
                OPTIONS AS FILETYPE,
                OPTIONS AS FILEFOLDER
            WHERE
                FILETYPE.GROUP_CODE = 'FILETYPE'
                AND FILEFOLDER.GROUP_CODE = 'FILEFOLDER'
                AND FILETYPE.CODE LIKE FILEFOLDER.CODE || '%'
        ";
        $paths = $this->minder->fetchAllAssoc($sql);
        return $paths;
    }

    public function buildSysScreenSearchResult($ssName, $required = false, $filename) {
        $ssRealName = $this->_getSSRealName($ssName);
        $sysScreenDesc = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $fields  = array();
        $tabs    = array();
        $colors  = array();
        $actions = array();

        if (empty($sysScreenDesc)) {
            if ($required)
                throw new Minder_SysScreen_Builder_Exception('SYS_SCREEN "' . $ssName . '" is required but was not defined in SYS_SCREEN table.');
            else
                return array(
                    $fields,
                    $tabs,
                    $colors,
                    $actions,
                    $sysScreenDesc,
                    'fields' => $fields,
                    'tabs' => $tabs,
                    'colors' => $colors,
                    'actions' => $actions,
                    'sys_screen_desc' => $sysScreenDesc
                );
        }


        $fields  = $this->getSysScreenFieldsFromFile($filename);
        $tabs    = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRTab($ssRealName));
        $colors  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Color($ssRealName));
        $actions = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Action($ssRealName));

        if (count($fields) < 1)
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName has no fields.");

        if (count($tabs) < 1) {
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName has no tabs.");
        }


        return array($fields, $tabs, $colors, $actions, 'fields' => $fields, 'tabs' => $tabs, 'colors' => $colors, 'actions' => $actions);
    }

    public function getSysScreenFieldsFromFile($fullFileName) {
        $fileHandle = fopen($fullFileName, 'r');
        if ($fileHandle) {
            if ($this->hasHeaders($fullFileName)) {
                $line       = fgets($fileHandle);
                $fields     = explode(',', $line);
                $headers    = array();
                $i = 1;

                foreach($fields as $field) {
                    $name = 'Field' . ($i++);
                    array_push($headers, $this->createHeader($name, $field));
                }
                return $headers;
            }
            else {
                $line       = fgets($fileHandle);
                $fields     = explode(',', $line);
                $headers    = array();

                for($i = 0; $i < count($fields); $i++) {
                    $name = 'Field' . ($i + 1);
                    array_push($headers, $this->createHeader($name, $name));
                }
                return $headers;
            }
        }
        else {
            throw new Minder_SysScreen_FileContentsBuilder_Exception("Unable to open file: $fullFileName.");
        }
    }

    public function buildSysScreenModelFileContents($ssName, &$newModel = null, $file) {
        $ssRealName = $this->_getSSRealName($ssName);
        $sysScreen  = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SysScreen($ssRealName));
        $fields     = $this->getSysScreenFieldsFromFile($file);
        $tabs       = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_SRTab($ssRealName));
        $PKeys      = array(
            'FILE_CONTENT_1' => array(
                'SSV_NAME' => 'LINENO'
            )
        );

        if (empty($sysScreen))
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName is undefined.");

        if (empty($PKeys))
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName has no primary keys.");

        if (count($fields) < 1)
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName has no fields cause file $file is empty!");

        if (count($tabs) < 1) {
            throw new Minder_SysScreen_FileContentsBuilder_Exception("System screen $ssName has no tabs.");
        }

        if (is_null($newModel))
            $newModel = new Minder_SysScreen_Model_FileContent();

        // looking for fields
        $newModel->fields = $fields;
        $newModel->pkeys  = $PKeys;

        return $newModel;
    }

    protected function _getSSRealName($ssName) {
        return $ssName;
    }

    /**
     * @param Minder_SysScreen_PartBuilder $partBuilder
     * @return array
     */
    public  function getScreenPartDesc($partBuilder) {
        return $partBuilder->build();
    }

    public function buildScreenButtons($ssName) {
        $buttons = $this->getScreenPartDesc(new Minder_SysScreen_PartBuilder_Button($this->_getSSRealName($ssName)));
        return array($buttons, 'buttons' => $buttons);
    }

    public function createHeader($name, $title) {
        $headerObj = array();
        $headerObj['SSV_INPUT_METHOD']  = 'RO';
        $headerObj['SSV_NAME']          = strtoupper($name);
        $headerObj['SSV_ALIAS']         = strtoupper($name);
        $headerObj['SSV_TITLE']         = $title;
        $headerObj['SSV_FIELD_STATUS']  = 'OK';
        $headerObj['SSV_FIELDS_TYPE']   = 'SR';

        return $headerObj;
    }
}

class Minder_SysScreen_FileContentsBuilder_Exception extends Minder_Exception {}
