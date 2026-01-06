<?php

class Minder2_SysScreen_Builder_Default implements Minder2_SysScreen_Builder_Interface {
    /**
     * @var string
     */
    protected $_sysScreenName = null;

    /**
     * @var Minder2_Model_SysScreen
     */
    protected $_sysScreen = null;

    /**
     * @var Minder_SysScreen_Builder
     */
    protected $_legacyScreenBuilder = null;

    /**
     * @param string $val
     * @return Minder2_SysScreen_Builder_Default
     */
    protected function _setSysScreenName($val) {
        $this->_sysScreenName = $val;
        $this->_sysScreen     = null;
        return $this;
    }

    /**
     * @throws Minder_Exception
     * @return string
     */
    protected function _getSysScreenName() {
        if (is_null($this->_sysScreenName))
            throw new Minder_Exception('sysScreenName is empty.');

        return $this->_sysScreenName;
    }

    /**
     * @return Minder_SysScreen_Builder
     */
    protected function _getLegacyScreenBuilder() {
        if (is_null($this->_legacyScreenBuilder))
            $this->_legacyScreenBuilder = new Minder_SysScreen_Builder();

        return $this->_legacyScreenBuilder;
    }

    /**
     * @return Minder2_Model_SysScreen
     */
    protected function _getSysScreen() {
        if (is_null($this->_sysScreen))
            $this->_sysScreen = $this->_buildSysScreenObject();

        return $this->_sysScreen;
    }

    /**
     * @static
     * @param $ssName
     * @return string
     */
    protected function _formatClassName($ssName) {
        $nameArray = explode('_', strtolower($ssName));

        foreach ($nameArray as &$namePart)
            $namePart = ucfirst($namePart);

        return implode('', $nameArray);
    }

    /**
     * @return array
     */
    protected function _getSysScreenDescription() {
        return $this->_getLegacyScreenBuilder()->getSysScreenDescription($this->_getSysScreenName());
    }

    /**
     * @return Minder2_Model_SysScreen
     */
    protected function _buildSysScreenObject()
    {
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Minder2_Model_SysScreen_', 'Minder2/Model/SysScreen/');
        $class = $loader->load($this->_formatClassName($this->_getSysScreenName()), false);

        if (false === $class)
            return new Minder2_Model_SysScreen($this->_getSysScreenDescription());

        return new $class($this->_getSysScreenDescription());
    }

    /**
     * @param $ssName
     * @param $description
     * @return Minder2_Model_SysScreen
     */
    protected function _getModelObject($ssName, $description) {
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Minder2_Model_SysScreen_', 'Minder2/Model/SysScreen/');
        $class = $loader->load($this->_formatClassName($ssName), false);

        if (false === $class)
            return new Minder2_Model_SysScreen($description);

        return new $class($description);
    }

    /**
     * @return bool
     */
    protected function _sysScreenExists() {
        return $this->_getLegacyScreenBuilder()->isSysScreenDefined($this->_getSysScreenName());
    }

    protected function _buildSearchResultTabPage($tabDescription, $fields, $searchResultModelVariableName) {
        $tmpScreen = $this->_getSysScreen();
        $tabPageId = $tabDescription['RECORD_ID'];

        $layoutElements = array();
        $layoutElements[] = $this->_buildTabNavigationPanel($tabPageId, $searchResultModelVariableName);
        $layoutElements[] = $this->_buildSelectedPanel($tabPageId, $searchResultModelVariableName);
        $layoutElements[] = $this->_buildDataGridElement($fields, $tabDescription, $tabPageId, $searchResultModelVariableName);
        $layoutElements[] = $this->_buildTabSummary($tabPageId, $searchResultModelVariableName);
        $layoutElements[] = $this->_buildTabButtonPanel($tabPageId, $searchResultModelVariableName);

        $pageLayoutVariableName = 'pageLayout' . $tabPageId;
        $tmpScreen->addDecorator($pageLayoutVariableName, array('decorator' => 'VerticalLayout', 'variableName' => $pageLayoutVariableName, 'modelVariable' => $searchResultModelVariableName, 'name' => $pageLayoutVariableName, 'elements' => $layoutElements));

        $pageVariableId = 'tabPage_' . $tabPageId;
        $tmpScreen->addDecorator($pageVariableId, array('decorator' => 'TabPage', 'variableName' => $pageVariableId, 'modelVariable' => $searchResultModelVariableName, 'name' => $tabPageId, 'settings' => $tabDescription, 'elements' => array(array('name' => $pageLayoutVariableName, 'variableName' => $pageLayoutVariableName))));

        return $pageVariableId;
    }

    protected function _getSelectedPanelElements($tabPageId, $searchResultModelVariableName) {
        $tmpScreen = $this->_getSysScreen();
        $selectedPanelElements = array();
        $selectedRowsVariable = 'selectedRows_' . $tabPageId;
        $tmpScreen->addDecorator($selectedRowsVariable, array('decorator' => 'ViewElement', 'variableName' => $selectedRowsVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_SELECTED_ROWS', 'javaScriptClass' => 'MinderView_RowSelectionInformer', 'templateFile' => 'jquery/field-with-caption.jqtmpl', 'settings' => array('_CAPTION' => 'Selected rows: ')));
        $selectedPanelElements[] = array('name' => '_SELECTED_ROWS', 'variableName' => $selectedRowsVariable);
        return $selectedPanelElements;
    }

    protected function _buildSelectedPanel($tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $selectedPanelElements = $this->_getSelectedPanelElements($tabPageId, $searchResultModelVariableName);

        $selectedPanelVariableName = 'selectedPanel' . $tabPageId;
        $tmpScreen->addDecorator($selectedPanelVariableName, array('decorator' => 'Summary', 'variableName' => $selectedPanelVariableName, 'modelVariable' => $searchResultModelVariableName, 'name' => $selectedPanelVariableName, 'elements' => $selectedPanelElements));
        return array('name' => $selectedPanelVariableName, 'variableName' => $selectedPanelVariableName);
    }

    protected function _buildTabButtonPanel($tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $buttonPanelVariable = 'buttonPanel_' . $tabPageId;
        list($buttons) = $this->_getLegacyScreenBuilder()->buildScreenButtons($this->_getSysScreenName());

        $tmpScreen->addDecorator($buttonPanelVariable, array('decorator' => 'ButtonPannel', 'buttons' => $buttons, 'modelVariable' => $searchResultModelVariableName, 'variableName' => $buttonPanelVariable, 'name' => 'BUTTON_PANEL-' . $tabPageId));
        return array('name' => 'BUTTON_PANEL-' . $tabPageId, 'variableName' => $buttonPanelVariable);
    }

    protected function _buildTabSummary($tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $summaryElements = $this->_getSummaryElements($tabPageId, $searchResultModelVariableName);

        $summaryVariableName = 'summary' . $tabPageId;
        $tmpScreen->addDecorator($summaryVariableName, array('decorator' => 'Summary', 'variableName' => $summaryVariableName, 'modelVariable' => $searchResultModelVariableName, 'name' => $summaryVariableName, 'elements' => $summaryElements));
        return array('name' => $summaryVariableName, 'variableName' => $summaryVariableName);
    }

    protected function _getSummaryElements($tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $summaryElements = array();
        $totalRowsVariable = 'totalRows_' . $tabPageId;
        $tmpScreen->addDecorator($totalRowsVariable, array('decorator' => 'ViewElement', 'variableName' => $totalRowsVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => $totalRowsVariable, 'javaScriptClass' => 'Minder_View_PaginatorInformer', 'templateFile' => 'jquery/paginator-informer.jqtmpl'));
        $summaryElements[] = array('name' => $totalRowsVariable, 'variableName' => $totalRowsVariable);
        return $summaryElements;
    }

    protected function _buildDataGridElement($fields, $tabDescription, $tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $dataGridFields = array();

        $inputMethodParser = new Minder_Page_FormBuilder_InputMethodParcer();
        foreach ($fields as $fieldDescription) {
            $fieldDescription['SSV_INPUT_METHOD_EXT'] = $inputMethodParser->parse($fieldDescription['SSV_INPUT_METHOD']);
            if (empty($fieldDescription['SSV_TAB']))
                $dataGridFields[] = $fieldDescription;
            elseif ($fieldDescription['SSV_TAB'] == $tabDescription['SST_TAB_NAME'])
                $dataGridFields[] = $fieldDescription;
        }

        $dataGridVariable = 'dataGrid_' . $tabPageId;
        $tmpScreen->addDecorator($dataGridVariable, array('decorator' => 'DataGrid', 'fields' => $dataGridFields, 'modelVariable' => $searchResultModelVariableName, 'variableName' => $dataGridVariable, 'name' => 'DATA_GRID-' . $tabPageId));
        return array('name' => 'DATA_GRID-' . $tabPageId, 'variableName' => $dataGridVariable);
    }

    protected function _buildTabNavigationPanel($tabPageId, $searchResultModelVariableName)
    {
        $tmpScreen = $this->_getSysScreen();
        $navigationPanelElements = array();

        $titleVariable = 'title_' . $tabPageId;
        $tmpScreen->addDecorator(
            $titleVariable,
            array(
                 'decorator' => 'ViewElement',
                 'variableName' => $titleVariable,
                 'modelVariable' => $searchResultModelVariableName,
                 'name' => '_TITLE',
                 'javaScriptClass' => 'Minder_View_Caption',
                 'templateFile' => 'jquery/search-result-title.jqtmpl',
                 'settings' => array('title' => $tmpScreen->SS_NAME)
            )
        );
        $navigationPanelElements[] = array('name' => '_TITLE', 'variableName' => $titleVariable);

        $selectCompleteTitleVariable = 'selectCompleteTitle_' . $tabPageId;
        $tmpScreen->addDecorator(
            $selectCompleteTitleVariable,
            array(
                'decorator' => 'ViewElement',
                'variableName' => $selectCompleteTitleVariable,
                'modelVariable' => $searchResultModelVariableName,
                'name' => $selectCompleteTitleVariable,
                'javaScriptClass' => 'Minder_View_StaticText',
                'templateFile' => 'jquery/static-text.jqtmpl',
                'settings' => array(
                    'text' => 'Select complete',
                    'title' => $tmpScreen->SS_NAME
                )
            )
        );
        $navigationPanelElements[] = array('name' => $selectCompleteTitleVariable, 'variableName' => $selectCompleteTitleVariable);

        $selectCompleteVariable = 'selectComplete_' . $tabPageId;
        $tmpScreen->addDecorator(
            $selectCompleteVariable,
            array(
                'decorator' => 'ViewElement',
                'variableName' => $selectCompleteVariable,
                'modelVariable' => $searchResultModelVariableName,
                'name' => 'selectComplete',
                'javaScriptClass' => 'Minder_View_SelectAll',
                'templateFile' => 'jquery/check-box.jqtmpl',
                'settings' => array(
                    'title' => $tmpScreen->SS_NAME
                )
            )
        );
        $navigationPanelElements[] = array('name' => 'selectComplete', 'variableName' => $selectCompleteVariable);

        $showByTitleVariable = 'showByTitle_' . $tabPageId;
        $tmpScreen->addDecorator(
            $showByTitleVariable,
            array(
                'decorator' => 'ViewElement',
                'variableName' => $showByTitleVariable,
                'modelVariable' => $searchResultModelVariableName,
                'name' => $showByTitleVariable,
                'javaScriptClass' => 'Minder_View_StaticText',
                'templateFile' => 'jquery/static-text.jqtmpl',
                'settings' => array(
                    'text' => 'View By:',
                    'title' => $tmpScreen->SS_NAME
                )
            )
        );
        $navigationPanelElements[] = array('name' => $showByTitleVariable, 'variableName' => $showByTitleVariable);

        $showByDataVariable = 'showByDataSet_' . $tabPageId;
        $showByArray = array(
            array('label' => 5, 'value' => 5),
            array('label' => 10, 'value' => 10),
            array('label' => 15, 'value' => 15),
            array('label' => 20, 'value' => 20),
            array('label' => 30, 'value' => 30),
            array('label' => 40, 'value' => 40),
            array('label' => 50, 'value' => 50),
            array('label' => 100, 'value' => 100)
        );
        $tmpScreen->addDecorator($showByDataVariable, array('decorator' => 'DataSet', 'variableName' => $showByDataVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_SHOW_BY', 'data' => $showByArray));

        $showByVariable = 'showBy_' . $tabPageId;
        $tmpScreen->addDecorator($showByVariable, array('decorator' => 'MultyElement', 'variableName' => $showByVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_SHOW_BY', 'javaScriptClass' => 'Minder_View_Multy', 'optionsDatasetVariable' => $showByDataVariable));
        $navigationPanelElements[] = array('name' => '_SHOW_BY', 'variableName' => $showByVariable);

        $pageNoTitleVariable = 'pageNoTitle_' . $tabPageId;
        $tmpScreen->addDecorator(
            $pageNoTitleVariable,
            array(
                'decorator' => 'ViewElement',
                'variableName' => $pageNoTitleVariable,
                'modelVariable' => $searchResultModelVariableName,
                'name' => $pageNoTitleVariable,
                'javaScriptClass' => 'Minder_View_StaticText',
                'templateFile' => 'jquery/static-text.jqtmpl',
                'settings' => array(
                    'text' => 'Page:',
                    'title' => $tmpScreen->SS_NAME
                )
            )
        );
        $navigationPanelElements[] = array('name' => $pageNoTitleVariable, 'variableName' => $pageNoTitleVariable);

        $pageNoVariable = 'pageNo_' . $tabPageId;
        $tmpScreen->addDecorator($pageNoVariable, array('decorator' => 'MultyElement', 'variableName' => $pageNoVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => '_PAGE_NO', 'javaScriptClass' => 'Minder_View_PageSelector'));
        $navigationPanelElements[] = array('name' => '_PAGE_NO', 'variableName' => $pageNoVariable);

        $navigationPanelVariable = 'navPanel_' . $tabPageId;
        $tmpScreen->addDecorator($navigationPanelVariable, array('decorator' => 'Panel', 'variableName' => $navigationPanelVariable, 'modelVariable' => $searchResultModelVariableName, 'name' => $navigationPanelVariable, 'elements' => $navigationPanelElements));

        return array('name' => $navigationPanelVariable, 'variableName' => $navigationPanelVariable);
    }

    protected function _buildSearchResults() {
        $tmpScreen = $this->_getSysScreen();
        $tmpScreen->addPrefixPath('Minder2_SysScreen_Decorator_JavaScript_', 'Minder2/SysScreen/Decorator/JavaScript/', Minder2_Model_SysScreen::DECORATOR);

        $searchResultModelVariableName = 'searchResultModel' . $tmpScreen->getName();
        $tmpScreen->addDecorator('screenModel', array('decorator' => 'ScreenModel', 'name' => 'screenModel', 'modelVariable' => $searchResultModelVariableName, 'javaScriptModel' => 'Minder_Model_SysScreen'));

        if ($tmpScreen->hasSearchFields())
            $tmpScreen->addDecorator('attachSearchResultModel', array('decorator' => 'AttachSearchResultModel', 'name' => 'attachSearchResultModel', 'variableName' => $this->_formatSearchModelVariableName(), 'searchResultModelVariableName' => $searchResultModelVariableName));

        list($fields, $tabs) = $this->_getLegacyScreenBuilder()->buildSysScreenSearchResult($this->_getSysScreenName());

        $pages = array();

        foreach ($tabs as $tabDescription) {
            $pages[] = $this->_buildSearchResultTabPage($tabDescription, $fields, $searchResultModelVariableName);
        }

        $tabVariable = 'tab_' . $this->_getSysScreenName();
        $tmpScreen->addDecorator('tab', array('decorator' => 'Tab', 'variableName' => $tabVariable, 'modelVariable' => $searchResultModelVariableName, 'pages' => $pages));

        $containerId = $this->_getSysScreenName() . '-SEARCH_RESULTS';
        $tmpScreen->addDecorator('defaultContainer', array('decorator' => 'defaultContainer', 'modelVariable' => $searchResultModelVariableName, 'containerId' => $containerId));
        $tmpScreen->addDecorator('render', array('decorator' => 'render', 'variableName' => $tabVariable, 'placement' => '$("#' . $containerId . ' span")'));
        $tmpScreen->addDecorator('setStatistics', array('decorator' => 'setStatistics', 'name' => 'setStatistics', 'modelVariable' => $searchResultModelVariableName));
        $tmpScreen->addDecorator('setData', array('decorator' => 'setData', 'name' => 'setData', 'modelVariable' => $searchResultModelVariableName));
        $tmpScreen->SS_TITLE_DISPLAY = true;

        return $this;
    }

    protected function _buildDataSet() {
        $this->_getSysScreen()->setDataSet(new Minder2_DataSet_SysScreenModel($this->_getLegacyScreenBuilder()->buildSysScreenModel($this->_getSysScreenName())));
    }

    protected function _buildEmptyTab() {
        return array(
            'RECORD_ID' => '__SERVICE_TAB',
            'SST_TAB_NAME' => 'GENERAL',
            'SST_SEQUENCE' => 0,
            'SST_TITLE' => 'General'
        );
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function _replaceFieldsTabWithEmpty($fields) {
        foreach ($fields as &$fieldDescription)
            $fieldDescription['SSV_TAB'] = '';

        return $fields;
    }

    /**
     * @return Minder2_SysScreen_Builder_Default
     */
    protected function _buildSearchForm() {
        list($searchFields, $actions, $tabs) = $this->_getLegacyScreenBuilder()->buildSysScreenSearchFields($this->_getSysScreenName());

        foreach ($searchFields as $fieldNo => $fieldDescription)
            if ($fieldDescription['SSV_INPUT_METHOD'] == 'NONE')
                unset($searchFields[$fieldNo]);

        if (empty($searchFields))
            return $this;

        if (empty($tabs)) {
            $tabs = array($this->_buildEmptyTab());
            $searchFields = $this->_replaceFieldsTabWithEmpty($searchFields);
        }

        $tmpScreen = $this->_getSysScreen();
        $tmpScreen->addPrefixPath('Minder2_SysScreen_Decorator_JavaScript_', 'Minder2/SysScreen/Decorator/JavaScript/', Minder2_Model_SysScreen::DECORATOR);
        $tmpScreen->initSearchFields($searchFields);
        $searchModelVariableName = $this->_formatSearchModelVariableName();

        $tmpScreen->addDecorator('searchModel', array('decorator' => 'SearchModel', 'name' => 'searchModel', 'javaScriptModel' => 'Minder_Model_Search', 'modelVariable' => $searchModelVariableName));
        $layoutElements = array();
        $layoutElements[] = $this->_buildSearchFormTitle();

        $pages = array();

        foreach ($tabs as $tabDescription) {
            $tabPageId = $tabDescription['RECORD_ID'];

            $pageElements = array();
            $pageSearchFields = array();

            foreach ($searchFields as $fieldDescription) {
                if (!empty($fieldDescription['SSV_TAB']) && $fieldDescription['SSV_TAB'] != $tabDescription['SST_TAB_NAME'])
                    continue;

                $pageSearchFields[] = $fieldDescription;
            }

            $buttons = array(
                array(
                    'RECORD_ID' => '_SEARCH_BUTTON',
                    'SSB_ACTION' => '$(this).data().minderElement.getModel().makeSearch();',
                    'SSB_TITLE'  => 'Submit Search',
                    'SSB_SEQUENCE' => 0
                ),
                array(
                    'RECORD_ID' => '_CLEAR_BUTTON',
                    'SSB_ACTION' => '$(this).data().minderElement.getModel().clearSearch();',
                    'SSB_TITLE'  => 'Clear',
                    'SSB_SEQUENCE' => 1
                )
            );

            $pageVariableId = 'tabPage_' . $tabPageId;
            $tmpScreen->addDecorator($pageVariableId, array('decorator' => 'SearchTabPage', 'variableName' => $pageVariableId, 'name' => $tabPageId, 'settings' => $tabDescription, 'elements' => $pageElements, 'modelVariable' => $searchModelVariableName, 'searchFields' => $pageSearchFields, 'buttons' => $buttons));

            $pages[] = $pageVariableId;
        }
        $tabVariable = 'searchTab_' . $this->_getSysScreenName();
        $tmpScreen->addDecorator('searchTab', array('decorator' => 'Tab', 'name' => 'searchTab', 'variableName' => $tabVariable, 'pages' => $pages, 'modelVariable' => $searchModelVariableName));

        $layoutElements[] = array('name' => 'searchTab', 'variableName' => $tabVariable);

        $pageLayoutVariableName = 'layout_' . $tmpScreen->RECORD_ID . '_SE';
        $tmpScreen->addDecorator($pageLayoutVariableName, array('decorator' => 'VerticalLayout', 'variableName' => $pageLayoutVariableName, 'modelVariable' => $searchModelVariableName, 'name' => $pageLayoutVariableName, 'elements' => $layoutElements));

        $containerId = $this->_getSysScreenName() . '-SEARCH_FORM';
        $tmpScreen->addDecorator('searchFormContainer', array('decorator' => 'defaultContainer', 'name' => 'searchFormContainer', 'containerId' => $containerId));
        $tmpScreen->addDecorator('renderSearchForm', array('decorator' => 'render', 'name' => 'renderSearchForm', 'variableName' => $pageLayoutVariableName, 'placement' => '$("#' . $containerId . ' span")'));

        return $this;
    }

    protected function _getSearchFormTitle() {
        $tmpScreen = $this->_getSysScreen();
        return 'SEARCH ' . $tmpScreen->SS_NAME;
    }

    protected function _buildSearchFormTitle()
    {
        $tmpScreen = $this->_getSysScreen();
        $searchModelVariableName = $this->_formatSearchModelVariableName();
        $titleVariable = 'title_' . $tmpScreen->RECORD_ID . '_SE';
        $tmpScreen->addDecorator(
            $titleVariable,
            array(
                 'decorator' => 'ViewElement',
                 'variableName' => $titleVariable,
                 'modelVariable' => $searchModelVariableName,
                 'name' => $titleVariable,
                 'javaScriptClass' => 'Minder_View_StaticText',
                 'templateFile' => 'jquery/form-title.jqtmpl',
                 'settings' => array(
                     'text' => $this->_getSearchFormTitle(),
                     'title' => $tmpScreen->SS_NAME
                 )
            )
        );
        return array('name' => $titleVariable, 'variableName' => $titleVariable);
    }

    protected function _formatSearchModelVariableName()
    {
        return 'searchModel_' . $this->_getSysScreenName();
    }

    function build($ssName)
    {
        $this->_setSysScreenName($ssName);

        if (!$this->_sysScreenExists())
            return null;

        $this->_getSysScreen()->restoreState();
        $this->_getSysScreen()->serviceUrl = '/minder/dashboard/screen/'; //todo: ?????? check and remove

        $this->_buildSearchForm()->_buildSearchResults()->_buildDataSet();
        $sysScreen = $this->_getSysScreen();
        $sysScreen->_TITLE = $sysScreen->SS_NAME;

        return $sysScreen;
    }

}