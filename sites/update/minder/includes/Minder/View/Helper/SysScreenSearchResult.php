<?php
  
class Minder_View_Helper_SysScreenSearchResult extends Zend_View_Helper_FormElement
{
    protected $tabs    = array();
    protected $fields  = array();
    protected $headers = array();
    protected $actions = array();

    /**
     * @var Minder_SysScreen_DataSource_Interface
     */
    protected $_dataSource = null;

    /**
     * @var Minder_SysScreen_DataSource_Parameter_Interface
     */
    protected $_parameterProvider = null;

    /**
     * @param Minder_SysScreen_DataSource_Parameter_Interface $parameterProvider
     * @return Minder_View_Helper_SysScreenSearchResult
     */
    protected function _setParameterProvider(Minder_SysScreen_DataSource_Parameter_Interface $parameterProvider) {
        $this->_parameterProvider = $parameterProvider;
        return $this;
    }

    /**
     * @return Minder_SysScreen_DataSource_Parameter_Interface
     */
    protected function _getParameterProvider() {
        if (is_null($this->_parameterProvider))
            $this->_setParameterProvider(new Minder_SysScreen_DataSource_SystemParameterProvider());

        return $this->_parameterProvider;
    }

    /**
     * @param Minder_SysScreen_DataSource_Interface $dataSource
     * @return Minder_View_Helper_SysScreenSearchResult
     */
    protected function _setDataSource(Minder_SysScreen_DataSource_Interface $dataSource) {
        $this->_dataSource = $dataSource;
        return $this;
    }

    /**
     * @return Minder_SysScreen_DataSource_Interface
     */
    protected function _getDataSource() {
        if (is_null($this->_dataSource))
            $this->_setDataSource(new Minder_SysScreen_DataSource_Sql());

        return $this->_dataSource;
    }

    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }
    
    public function getTabs() {
        return $this->tabs;
    }
    
    public function getFields() {
        return $this->fields;
    }

    protected function _getField($columnId) {
        foreach ($this->fields as $field) {
            if ($field['RECORD_ID'] === $columnId) {
                return $field;
            }
        }

        return array();
    }
    
    public function getHeaders() {
        return $this->headers;
    }
    
    public function getActions() {
        return $this->actions;
    }
    
    public function setTabs($tabs= array()) {
        $this->tabs = array();
        usort($tabs, array($this , '_sortCallback'));
        foreach ($tabs as $tabDescription) {
            $this->tabs[strtoupper(trim($tabDescription['SS_NAME'] . '_' . $tabDescription['SST_TAB_NAME']))] = $tabDescription['SST_TITLE'];
        }
        
        return $this;
    }
    
    public function setFields($fields= array()) {
        $this->fields = $fields;
        
        $tmpHeaders   = array();
        foreach ($this->tabs as $tabId => $tabCaption)
            $tmpHeaders[$tabId] = array();
            
        usort($fields, array($this , '_sortCallback'));
        foreach ($fields as $fieldDesc) {
            $tmpTabId = trim($fieldDesc['SSV_TAB']);
            if (empty($tmpTabId)) {
                foreach ($this->tabs as $tabId => $tabCaption) {
                    $tmpHeaders[$tabId][$fieldDesc['RECORD_ID']] = $fieldDesc['SSV_TITLE'];
                }
            } else {
                $tmpTabId = strtoupper(trim($fieldDesc['SS_NAME']) . '_' . $tmpTabId);
                $tmpHeaders[$tmpTabId][$fieldDesc['RECORD_ID']]  = $fieldDesc['SSV_TITLE'];
            }
        }
        $this->setHeaders($tmpHeaders);
        
        return $this;
    }
    
    public function setHeaders($headers= array()) {
        $this->headers = $headers;

        return $this;
    }
    
    public function setActions($actions = array()) {
        if (!is_array($actions))
            $actions = array();
            
        $this->actions = $actions;
        
        return $this;
    }
    
    public function renderTabsList($info = array()) {
        $xhtml  = '<ul>';
        
        $referencePrefix = 'tab_id_';
        if (isset($info['tab_reference_prefix'])) {
            $referencePrefix = $info['tab_reference_prefix'];
        }
        
        foreach ($this->tabs as $tabId => $tabCaption) {
            $tabReference = $referencePrefix.$tabId;
            $xhtml .= "<li tab_name=\"$tabId\" tab_id=\"$tabId\"><a href=\"#$tabReference\" ><b><span>$tabCaption</span></b></a></li>";
        }
        
        $xhtml .= '</ul>';
        
        return $xhtml;
    }
    
    public function renderTabContent($tabId, $attribs= array(), $dataset= array(), $selectedRows = array()) {
        if (!isset($this->tabs[$tabId]))
            return '';
        
        $tabContentMaxHeight = 'none';
        if (isset($attribs['tab_content_max_height'])) {
            $tabContentMaxHeight = $attribs['tab_content_max_height'];
        }
        
        $tabContentClass = 'withborder tablesorter';
        if (isset($attribs['tab_content_class'])) {
            $tabContentClass = $attribs['tab_content_class'];
        }
        
        $referencePrefix = 'tab_id_';
        if (isset($attribs['tab_reference_prefix'])) {
            $referencePrefix = $attribs['tab_reference_prefix'];
        }
        $tabReference = $referencePrefix.$tabId;

        $style = array(
            'margin-bottom: 0;'
        );
        
        if (isset($attribs['tab_content_style'])) {
            $style = is_array($attribs['tab_content_style']) ? $attribs['tab_content_style'] : array($attribs['tab_content_style']);
            unset($attribs['tab_content_style']);
        }
        
        if (is_numeric($tabContentMaxHeight)) {
            $style[] = 'height: ' . $tabContentMaxHeight . 'px;';
            $style[] = 'overflow: auto;';
        }
        
        $xhtml =  '<!-- tab content starts -->';
        $xhtml .= '<div style="' . implode(' ', $style) . '">';
        $xhtml .= '<table class="' . $tabContentClass . '" tab_id="' . $this->view->escape($tabReference) . '" style="' . implode('', $style) . '">';
        $xhtml .= '<thead>';
        $xhtml .= $this->renderHeaders($tabId, $attribs);
        $xhtml .= $this->renderRows($tabId, $attribs, $dataset, $selectedRows);
        $xhtml .= '</thead>';
        $xhtml .= '</table>';
        $xhtml .= '</div>';
        $xhtml .= '<!-- tab content ends -->';
        
        return $xhtml;
    }
    
    public function renderTab($tabId, $attribs= array(), $renderContent = false, $dataset= array(), $selectedRows = array()) {
        if (!isset($this->tabs[$tabId]))
            return '';
        
        $referencePrefix = 'tab_id_';
        if (isset($attribs['tab_reference_prefix'])) {
            $referencePrefix = $attribs['tab_reference_prefix'];
        }
        $tabReference = $referencePrefix.$tabId;
        
        $style = array(
        );
        if (isset($attribs['style'])) {
            $style = (is_array($attribs['style'])) ? $attribs['style'] : array($attribs['style']);
        }
        
        $xhtml = '<div id="' . $this->view->escape($tabReference) . '"  class="ui-tabs-panel-content" style="' . implode(' ', $style) . '">';
        
        if ($renderContent !== false) {
            $xhtml .= $this->renderTabContent($tabId, $attribs, $dataset, $selectedRows) . '</div>';
        }
        
        return $xhtml;
    }
    
    public function renderHeaders($tabId, $attribs = array()) {
        if (!isset($this->headers[$tabId])) 
            return '';
            
        $selectAllClass = 'select_all_rows';
        if (isset($attribs['select_all_class']))
            $selectAllClass = $this->view->escape($attribs['select_all_class']);
            
        $selectionNamespace = 'default';
        if (isset($attribs['selection_namespace'])) {
            $selectionNamespace = $this->view->escape($attribs['selection_namespace']);
        }
        $selectAllClass .= ' ' . $selectionNamespace;
        
        $selectAllChecked = '';
        if (isset($attribs['select_all_checked']) && $attribs['select_all_checked']) {
            $selectAllChecked = ' checked = "checked" ';
        }
        
        $switchSelectionClass = "switch_selection";
        if (isset($attribs['switch_selection_class']))
            $switchSelectionClass = $this->view->escape($attribs['switch_selection_class']);
        $switchSelectionClass .= ' ' . $selectionNamespace;
        
        
        $switchSelectionMode    = "all";
        if (isset($attribs['switch_selection_mode'])) {
            $switchSelectionMode = $this->view->escape($attribs['switch_selection_mode']);
        }
        
        $selectAllDisabled      = '';
        $switchSelectionChecked = "";
        if ($switchSelectionMode == 'one') {
            $switchSelectionChecked = 'checked';
            $selectAllDisabled      = 'disabled';
        }

        $selectableRows = true;
        if (isset($attribs['selectable-rows'])) {
            $selectableRows = $attribs['selectable-rows'];
            unset($attribs['selectable-rows']);
        }

        $xhtml  = "<!-- headers starts -->";
        $xhtml .= "<tr>";

        if ($selectableRows) {
            $xhtml .= "<th style=\"white-space: nowrap;\">";
            $xhtml .= "<input type=\"checkbox\" $selectAllChecked $selectAllDisabled class=\"$selectAllClass\" row_id=\"select_all\" selection_namespace=\"$selectionNamespace\" />";
            $xhtml .= "&nbsp;&nbsp;&nbsp;";
            $xhtml .= "<input type=\"radio\" selection_mode = \"$switchSelectionMode\" $switchSelectionChecked class=\"$switchSelectionClass\" selection_namespace=\"$selectionNamespace\" />";
            $xhtml .= "</th>";
        } else {
            $xhtml .= "<th style=\"white-space: nowrap;\">";
            $xhtml .= "&nbsp;";
            $xhtml .= "</th>";
        }

        foreach ($this->headers[$tabId] as $collumnId => $collumnHeader) {
            $xhtml .= '<th column-id="' . $this->view->escape($collumnId) . '">' . $this->view->escape($collumnHeader) . '</th>';
        }
        
        $xhtml .= '</tr>';
        $xhtml .= "<!-- headers ends -->";
        
        return $xhtml;
    }
    
    protected function getRowDefaults($tabId, $rowData) {
        $defaults = array();
        $minder = Minder::getInstance();

        $dataSource        = $this->_getDataSource();
        $parameterProvider = $this->_getParameterProvider();

        foreach ($this->headers[$tabId] as $columnId => $columnHeader) {
            $tmpFieldDesc = $this->_getField($columnId);
            if (empty($tmpFieldDesc['SSV_DROPDOWN_DEFAULT']))
                continue;
                
            $fieldAlias   = $tmpFieldDesc['SSV_ALIAS'];
            
            $dataSource->sql = $tmpFieldDesc['SSV_DROPDOWN_DEFAULT'];
            $defaults[$fieldAlias] = '';

            try {
                $defaults[$fieldAlias] = $dataSource->fetchOne($parameterProvider);
            } catch (Exception $e) {

            }
        }
        return $defaults;
    }
    
    public function renderSingleRow($tabId, $info, $rowId, $rowData, $selectedRows = array()) {
        if (!isset($this->headers[$tabId])) 
            return '';
        
        $selectableRow = true;
        if (isset($info['selectable-row'])) {
            $selectableRow = $info['selectable-row'];
            unset($info['selectable-row']);
        }
        
        $selectRowClass = 'row_selector';
        if (isset($info['data_set_select_row_class'])) {
            $selectRowClass = $info['data_set_select_row_class'];
        }
        
        $rowAdditionalClass = '';
        if (isset($info['data_set_row_class'])) {
            $rowAdditionalClass = ' ' . $info['data_set_row_class'];
        }
        
        if (isset($rowData['BAD_ROW'])) {
            $rowAdditionalClass .= ' bad-line';
        }

        $selectionNamespace = 'default';
        if (isset($info['selection_namespace'])) {
            $selectionNamespace = $this->view->escape($info['selection_namespace']);
        }
        $selectRowClass .= ' ' . $selectionNamespace;
                
        $checked = ((isset($selectedRows[$rowId])) ? 'checked' : '');
                
        $xhtml   = '<tr class="ROW-ID-' . $rowId . ' ' . $rowAdditionalClass . '" row_id="' . $rowId . '">';
        $disabled = $selectableRow ? '' : 'disabled';
        $xhtml  .= '<th><input type="checkbox" class="' . $this->view->escape($selectRowClass) . '" row_id="' . $this->view->escape($rowId) . '" ' . $disabled . ' ' . $checked . ' selection_namespace="' . $selectionNamespace . '" /></th>';

        $defaults = $this->getRowDefaults($tabId, $rowData);
        
        foreach ($this->headers[$tabId] as $columnId => $columnHeader) {
            $tmpFieldDesc = $this->_getField($columnId);
            $fieldAlias   = $tmpFieldDesc['SSV_ALIAS'];
            
            $style = array();
            if (isset($info['data_set_row_style'])) {
                $style = $info['data_set_row_style'];
            }
        
            if (!empty($tmpFieldDesc['SSV_FIELD_WIDTH'])) {
                $style[] = 'width: ' . $tmpFieldDesc['SSV_FIELD_WIDTH'] . 'px;';
            }
                    
            if (!empty($tmpFieldDesc['COLOR_FIELD_ALIAS']) && isset($rowData[$tmpFieldDesc['COLOR_FIELD_ALIAS']])) {
                $style[] = 'background-color: ' . trim($rowData[$tmpFieldDesc['COLOR_FIELD_ALIAS']]) . ';';
            }
            $styleStr = implode($style);
                    
            $tmpAttrs              = array();
            $tmpClasses            = array('ROW-ID-' . $rowId, $fieldAlias, 'COLUMN-ID-' . $columnId);
            $tmpAttrs['class']     = implode(' ', $tmpClasses);
            $tmpAttrs['style']     = $styleStr;
            $tmpAttrs['id']        = $rowId . '-' . $fieldAlias;
            $tmpAttrs['row-id']    = $rowId;
            $tmpAttrs['column-id'] = $columnId;
            $tmpAttrs['row-data']  = $rowData;

            if (!empty($tmpFieldDesc['NUMBER_FORMAT']))
                $tmpAttrs['data-number-format'] = $tmpFieldDesc['NUMBER_FORMAT'];
            
            $tmpFieldDesc['ENTERED_VALUE'] = $rowData[$fieldAlias];
            if (isset($defaults[$fieldAlias]))
                $tmpFieldDesc['DEFAULT_VALUE'] = $defaults[$fieldAlias];
                    
            $xhtml .= '<td column-id="' . $this->view->escape($tmpAttrs['column-id']) . '" style="' . $styleStr . '">' . $this->view->sysScreenFormElement($tmpAttrs, $tmpFieldDesc, $this->actions) . '</td>';
        }
                
        $xhtml .= '</tr>';
        
        return $xhtml;
    }
    
    public function renderEmptyRow($tabId, $style) {
        $xhtml  = '<!-- no data set rows, so add one for tablesorter -->';
        $xhtml .= '<tr row_id=""><th />';
        foreach ($this->headers[$tabId] as $columnId => $collumnHeader) {
            $fieldDesc = $this->_getField($columnId);
            if (!empty($fieldDesc['SSV_FIELD_WIDTH'])) {
                $style['width'] = $fieldDesc['SSV_FIELD_WIDTH'] . 'px';
            }
            
            $styleStr = implode(array_map(create_function('$key, $val','return $key . ": " . $val . ";";'), array_keys($style), $style));
            
            $xhtml .= '<td column-id="' . $this->view->escape($columnId) . '" style="' . $styleStr . '">&nbsp;</td>';
        }
        $xhtml .= '</tr>';
        
        return $xhtml;
    }
    
    public function renderRows($tabId, $info= array(), $dataset= array(), $selectedRows = array()) {
        if (!isset($this->headers[$tabId])) 
            return '';
        
        $datasetClass = 'data_set';
        if (isset($info['data_set_class']))
            $datasetClass = $info['data_set_class'];
        
        $xhtml  = '<!-- data set starts --> ';
        $xhtml .= '<tbody class="' . $this->view->escape($datasetClass) . '">';
        
        $style = array();
        if (isset($info['data_set_row_style'])) {
            $style = $info['data_set_row_style'];
        }
        
        if (count($dataset) >0) {
            foreach ($dataset as $rowId => $rowData) {
                $xhtml .= $this->renderSingleRow($tabId, $info, $rowId, $rowData, $selectedRows);
            }
        } else {
            $xhtml .= $this->renderEmptyRow($tabId, $style);
        }
        
        $xhtml .= "</tbody>";
        $xhtml .= '<!-- data set ends --> ';
        
        return $xhtml;
    }
    
    /**
    * Render html code for given actions list, which can be inserted in to result page
    * 
    * @param array $actions - actions list from SYS_SCREEN_ACTION table
    * @return string - html code
    */
    public function renderActions(array $actions = array()) {
        if (!is_array($this->actions))
            $this->actions = array();
            
        if (!is_array($actions)) 
            $actions = array();
            
        $actions = array_merge($this->actions, $actions);
        Zend_Registry::get('logger')->debug(array('file' => __FILE__, 'line' => __LINE__, $actions));
        return '<script type="text/javascript">' . PHP_EOL . implode(PHP_EOL, array_map(create_function('$item', 'return $item["SSA_ACTION"];'), $actions)) . PHP_EOL . '</script>';
    }

    protected function _buttonSorter($buttonA, $buttonB) {
        return intval($buttonA['SSB_SEQUENCE']) - intval($buttonB['SSB_SEQUENCE']);
    }

    public function renderButtons(array $buttons = array()) {
        if (!is_array($buttons))
            $buttons = array();

        if (empty($buttons))
            return '';

        usort($buttons, array($this, '_buttonSorter'));

        $xhtml = '';

        foreach ($buttons as $buttonDescription) {
            $buttonIdClass = 'SCREEN_BUTTON_' . $buttonDescription['RECORD_ID'];
            $buttonName    = $this->view->escape($buttonDescription['SSB_BUTTON_NAME']);
            $buttonCaption = $this->view->escape($buttonDescription['SSB_TITLE']);
            $xhtml .= '
                <li><button class="mdr-tool-button ' . $buttonIdClass . '" name="' . $buttonName . '" onclick="return false;">' . $buttonCaption . '</button></li>
            ';
        }

        $xhtml = '<ul class="toolbar">' . PHP_EOL . $xhtml . PHP_EOL . '</ul>';

        return $xhtml;
    }

    public function renderButtonActions(array $buttons = array()) {
        if (!is_array($buttons))
            $buttons = array();

        if (empty($buttons))
            return '';

        $xhtml = '';

        foreach ($buttons as $buttonDescription) {
            $buttonIdClass = 'SCREEN_BUTTON_' . $buttonDescription['RECORD_ID'];
            $buttonAction  = $buttonDescription['SSB_ACTION'];
            $xhtml .= '
                $(".' . $buttonIdClass . '").click(function(evt){' . PHP_EOL . $buttonAction . PHP_EOL . '});
            ';
        }

        return $xhtml;
    }
    
    public function render($attribs= array(), $dataset = array(), $selectedRows = array()) {
        if (isset($attribs['data_set'])) {
            $dataset = $attribs['data_set'];
            unset($attribs['data_set']);
        }
        
        $name = 'search_results_form';
        if (isset($attribs['name'])) {
            $name = $attribs['name'];
            unset($attribs['name']);
        }
        
        $id   = strtoupper($name);
        if (isset($attribs['id'])) {
            $id = $attribs['id'];
            unset($attribs['id']);
        }
        
        if (isset($attribs['tabs'])) {
            $this->setTabs($attribs['tabs']);
            unset($attribs['tabs']);
        }
        
        if (isset($attribs['fields'])) {
            $this->setFields($attribs['fields']);
            unset($attribs['fields']);
        }
        
        if (isset($attribs['headers'])) {
            $this->setHeaders($attribs['headers']);
            unset($attribs['headers']);
        }
        
        $content  = $this->renderTabsList($attribs);
        
        $content .= $header;
        
        $tabContentClass = '';
        if (isset($attribs['tab_content_class'])){
            $tabContentClass = $attribs['tab_content_class'];
            unset($attribs['tab_content_class']);
        }
        
        foreach ($this->tabs as $tabId => $tabCaption) {
            $tabContent  = '<!-- tab content starts -->';
            $tabContent .= '<table class="' . $this->view->escape($tabContentClass) . '" style="margin-bottom: 0;" tab_id="tab_' . $this->view->escape($tabId) .'">';
            $tabContent .= '<thead>';
            $tabContent .= $this->renderHeaders($tabId, $attribs);
            $tabContent .= $this->renderRows($tabId, $attribs, $dataset, $selectedRows);
            $tabContent .= '</thead>';
            $tabContent .= '</table>';
            $tabContent .= '<!-- tab content ends -->';
            
            $content    .= $this->renderTab($tabId, $attribs, $tabContent);
        }
        
        $content .= $footer;
        
        if (isset($attribs['data_set_class']))
            unset($attribs['data_set_class']);
        
        if (isset($attribs['data_set_select_row_class']))
            unset($attribs['data_set_select_row_class']);
        
        if (isset($attribs['data_set_row_style']))
            unset($attribs['data_set_row_style']);
        
        if (isset($attribs['select_all_class']))
            unset($attribs['select_all_class']);
        
        if (isset($attribs['tab_reference_prefix']))
            unset($attribs['tab_reference_prefix']);
            
        $tabsConteinerId = $id.'_TABS_CONTAINER';
        if (isset($attribs['tabs_container_id'])) {
            $tabsConteinerId = $attribs['tabs_container_id'];
            unset($attribs['tabs_container_id']);
        }
        
        $tabsConteinerClass = '';
        if (isset($attribs['tabs_container_class'])) {
            $tabsConteinerClass = $attribs['tabs_container_class'];
            unset($attribs['tabs_container_class']);
        }
        
        $xhtml  = '<!-- ' . $this->view->escape($name) . ' result form starts -->';
        $xhtml .= '<form name="' . $this->view->escape($name) . '" id="' . $this->view->escape($id) . '" ' . $this->_htmlAttribs($attribs) . '>';
        $xhtml .= '<!-- tabs container starts -->';
        $xhtml .= '<div id="' . $this->view->escape($tabsConteinerId) . '" class="' . $this->view->escape($tabsConteinerClass) . '" style="">';

        $xhtml .= $content;
        $xhtml .= '</div>';
        $xhtml .= '<!-- tabs container ends -->';
        $xhtml .= '</form>';
        $xhtml .= '<!-- ' . $this->view->escape($name) . ' SYS_SCREEN_ACTIONs starts -->';
        $xhtml .= $this->renderActions();
        $xhtml .= '<!-- ' . $this->view->escape($name) . ' SYS_SCREEN_ACTIONs ends -->';
        $xhtml .= '<!-- ' . $this->view->escape($name) . ' result form ends -->';
        
        return $xhtml;
    }
}