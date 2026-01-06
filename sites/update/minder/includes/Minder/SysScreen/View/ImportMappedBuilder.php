<?php

class Minder_SysScreen_View_ImportMappedBuilder extends Minder_SysScreen_View_Builder {

    protected $_importRules;

    protected $_importType = '';

    function __construct($importType)
    {
        $this->_setImportType($importType);
    }

    protected function _fetchImportRules() {
        $query = "SELECT * FROM IMPORT_RULES WHERE IMPORT_RULES_TYPE = ?";
        return Minder::getInstance()->fetchAllAssoc($query, $this->_getImportType());
    }

    protected function _getImportRules() {
        if (is_null($this->_importRules)) {
            $this->_importRules = $this->_fetchImportRules();
        }

        return $this->_importRules;
    }

    protected function _getPaths() {
        return array_unique(Minder_ArrayUtils::mapField($this->_getImportRules(), 'IMPORT_RULES_PATH'));
    }

    protected function _getStaticConditions() {
        $result = array();
        foreach ($this->_getImportRules() as $rule) {
            $parts = array(
                new Minder_SysScreen_View_FileSystem_EqualTo('DIRNAME', $rule['IMPORT_RULES_PATH']),
            );

            if (!empty($rule['IMPORT_RULES_FILENAME_PREFIX'])) {
                $parts[] = new Minder_SysScreen_View_FileSystem_StartsWith('BASENAME', $rule['IMPORT_RULES_FILENAME_PREFIX']);
            }

            if (!empty($rule['IMPORT_RULES_FILENAME_EXTENSN'])) {
                $parts[] = new Minder_SysScreen_View_FileSystem_EqualTo('EXTENSION', ltrim($rule['IMPORT_RULES_FILENAME_EXTENSN'], '.'));
            }

            $result[] = new Minder_SysScreen_View_FileSystem_And($parts);
        }

        return array(new Minder_SysScreen_View_FileSystem_Or($result));
    }

    protected function _buildFSView($screenName) {
        $primaryKeys        = $this->_getPrimaryKeys($screenName);
        $fields             = $this->_getFields($screenName);
        $staticConditions   = $this->_getStaticConditions($screenName);
        $searchFields       = $this->_getSearchFields($screenName);
        $order              = $this->_getOrder($screenName);

        if (empty($primaryKeys)) {
            throw new Minder_SysScreen_View_BuilderException("System screen $screenName has no primary keys.");
        }

        if (empty($fields)) {
            throw new Minder_SysScreen_View_BuilderException("System screen $screenName has no fields.");
        }

        $result = new Minder_SysScreen_View_FileSystem($this->_getPaths());

        $result->setPrimaryKeys($primaryKeys);
        $result->setFields($fields);
        $result->setStaticConditions($staticConditions);
        $result->setSearchFields($searchFields);
        $result->setOrder($order);

        return $result;
    }



    protected function _doBuild($screenName) {
        $source = $this->_buildFSView($screenName);
        $source->init();

        return $source;
    }

    public function buildSysScreenModel($screenName, $modelPrototype = null) {
        switch ($screenName) {
            case 'MAPPED_FILES':
                return $this->_doBuild($screenName, $modelPrototype);
                break;
            default:
                return parent::buildSysScreenModel($screenName, $modelPrototype);
        }
    }

    /**
     * @return string
     */
    protected function _getImportType()
    {
        return $this->_importType;
    }

    /**
     * @param string $importType
     * @return $this
     */
    protected function _setImportType($importType)
    {
        $this->_importType = $importType;
        return $this;
    }

}