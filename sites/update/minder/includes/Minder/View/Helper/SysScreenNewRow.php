<?php
  
class Minder_View_Helper_SysScreenNewRow extends Zend_View_Helper_FormElement
{
    protected $tabs     = array();
    protected $fields   = array();
    protected $actions  = array();
    protected $headers  = array();
    protected $defaults = array();
    
    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }
    
    public function getActions() {
        return $this->actions;
    }

    public function getTabs() {
        return $this->tabs;
    }

    public function getFields() {
        return $this->fields;
    }

    public function getHeaders() {
        return $this->headers;
    }
    
    public function getDefaults() {
        return $this->defaults;
    }

    public function setDefaults($defaults = array()) {
        if (!is_array($defaults))
            $defaults = array();
        
        $this->defaults = $defaults;
        
        return $this;
    }
    
    public function setActions($actions = array()) {
        if (!is_array($actions))
            $actions = array();
            
        $this->actions = $actions;
        
        return $this;
    }
    
    public function setTabs($tabs = array()) {
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
    
    public function renderRowCell($attribs = array(), $tabId = '', $columnId = '', $headers = array(), $fields = array(), $actions = array(), $defaults = array()) {
        if (isset($attribs['tab_id'])) {
            $tabId = $attribs['tab_id'];
            unset($attribs['tab_id']);
        }
        
        if (isset($attribs['column_id'])) {
            $columnId = $attribs['column_id'];
            unset($attribs['column_id']);
        }
        
        if (isset($attribs['headers'])) {
            $headers = $attribs['headers'];
            unset($attribs['headers']);
        }
        
        if (isset($attribs['fields'])) {
            $fields = $attribs['fields'];
            unset($attribs['fields']);
        }
        
        if (isset($attribs['actions'])) {
            $actions = $attribs['actions'];
            unset($attribs['actions']);
        }
        
        if (isset($attribs['defaults'])) {
            $defaults = $attribs['defaults'];
            unset($attribs['defaults']);
        }
        
        if (empty($fields)) $fields = $this->getFields();
        if (empty($actions)) $actions = $this->getActions();
        if (empty($defaults)) $defaults = $this->getDefaults();
        if (empty($headers)) {
            $this->setFields($fields);
            $headers = $this->getHeaders();
        }
        
        if (!isset($headers[$tabId]) || !isset($headers[$tabId][$columnId]) || !isset($fields[$columnId]))
            return '';
        
        $rowId = 'NEW';
        if (isset($attribs['row_id']))
            $rowId = $attribs['row_id'];
            
        $cellClass = 'row_cell';
        if (isset($attribs['cell_class'])) 
            $cellClass = $attribs['cell_class'];
        $cellClass .= ' TAB-ID-' . $tabId . ' ROW-ID-' . $rowId . ' COLUMN-ID-' . $columnId;
            
        $tmpFieldDesc = $this->fields[$columnId];
        $fieldAlias   = $tmpFieldDesc['SSV_ALIAS'];
        $tmpFieldDesc['DEFAULT_VALUE'] = (isset($defaults[$fieldAlias])) ? $defaults[$fieldAlias] : '';
                    
        $style = array();
        if (!empty($tmpFieldDesc['SSV_FIELD_WIDTH'])) {
            $style[] = 'width: ' . $tmpFieldDesc['SSV_FIELD_WIDTH'] . 'px;';
        }
        $styleStr = implode($style);
        
        $xhtml  = '<td class="' . $this->view->escape($cellClass) . '" row_id="' . $this->view->escape($rowId) . '" column_id="' . $this->view->escape($columnId) . '" style="' . $styleStr . '">';

        $tmpAttrs              = array();
        $tmpClasses            = array();
        $tmpAttrs['class']     = 'ROW-ID-'.$rowId . ' COLUMN-ID-' . $columnId . ' ' . $fieldAlias;
        $tmpAttrs['style']     = $styleStr;
        $tmpAttrs['row-id']    = $rowId;
        $tmpAttrs['column-id'] = $columnId;
        $tmpAttrs['is_new']    = true;

        if (!empty($tmpFieldDesc['NUMBER_FORMAT']))
            $tmpAttrs['data-number-format'] = $tmpFieldDesc['NUMBER_FORMAT'];
        
        $xhtml .= $this->view->SysScreenFormElement($tmpAttrs, $tmpFieldDesc, $actions);
        
        $xhtml .= '</td>';
        
        return $xhtml;
    }
    
    public function renderTabRow($attribs = array(), $tabId = '', $headers = array(), $fields = array(), $actions = array(), $defaults = array()) {
        if (isset($attribs['tab_id'])) {
            $tabId = $attribs['tab_id'];
            unset($attribs['tab_id']);
        }
        
        if (isset($attribs['headers'])) {
            $headers = $attribs['headers'];
            unset($attribs['headers']);
        }
        
        if (isset($attribs['fields'])) {
            $fields = $attribs['fields'];
            unset($attribs['fields']);
        }
        
        if (isset($attribs['actions'])) {
            $actions = $attribs['actions'];
            unset($attribs['actions']);
        }
        
        if (isset($attribs['defaults'])) {
            $defaults = $attribs['defaults'];
            unset($attribs['defaults']);
        }
        
        if (empty($fields)) $fields = $this->getFields();
        if (empty($actions)) $actions = $this->getActions();
        if (empty($defaults)) $defaults = $this->getDefaults();
        if (empty($headers)) {
            $this->setFields($fields);
            $headers = $this->getHeaders();
        }
        
        if (!isset($headers[$tabId]))
            return '';
            
        //should be the same row-id for all tabs        
        $rowId = 'NEW-ROW-';
        if (isset($attribs['row_id']))
            $rowId = $attribs['row_id'];
        else 
            $attribs['row_id'] = $rowId;
            
        $rowClass = 'row';
        if (isset($attribs['row_class'])) 
            $rowClass = $attribs['row_class'];
        $rowClass .= ' TAB-ID-' . $tabId . ' ROW-ID-' . $rowId;
            
        $xhtml  = '<tr is_new="true" tab_id="' . $this->view->escape($tabId) . '" class="' . $this->view->escape($rowClass) .  '" row_id="' . $this->view->escape($rowId) . '">';
        $xhtml .= '<th>&nbsp;</th>';

        foreach ($headers[$tabId] as $columnId => $columnName) {
            $xhtml .= $this->renderRowCell($attribs, $tabId, $columnId, $headers, $fields, $actions, $defaults);
        }
        $xhtml .= '</tr>';
        
        return $xhtml;
    }
    
    public function renderTabs($attribs = array(), $tabs = array(), $headers = array(), $fields = array(), $actions = array(), $defaults = array()) {
        if (isset($attribs['tabs'])) {
            $tabs = $attribs['tabs'];
            unset($attribs['tabs']);
        }
        
        if (isset($attribs['headers'])) {
            $headers = $attribs['headers'];
            unset($attribs['headers']);
        }
        
        if (isset($attribs['fields'])) {
            $fields = $attribs['fields'];
            unset($attribs['fields']);
        }
        
        if (isset($attribs['actions'])) {
            $actions = $attribs['actions'];
            unset($attribs['actions']);
        }
        
        if (isset($attribs['defaults'])) {
            $defaults = $attribs['defaults'];
            unset($attribs['defaults']);
        }

        //should be the same row-id for all tabs, 
        //so use uniqid here rather then in renderTabRow()
        $rowId = 'NEW-' . uniqid();
        if (!isset($attribs['row_id']))
            $attribs['row_id'] = $rowId;
            
        if (empty($tabs)) $tabs = $this->getTabs();
        if (empty($fields)) $fields = $this->getFields();
        if (empty($actions)) $actions = $this->getActions();
        if (empty($defaults)) $defaults = $this->getDefaults();
        if (empty($headers)) {
            $this->setTabs($tabs);
            $this->setFields($fields);
            $headers = $this->getHeaders();
        }
        
        $xhtml = '<table>';
        foreach ($tabs as $tabId => $tabName) {
            $xhtml .= $this ->renderTabRow($attribs, $tabId, $headers, $fields, $actions, $defaults);
        }
        $xhtml .= '</table>';
        
        return $xhtml;
    }
    
    /**
    * Render html code for given actions list, which can be inserted in to result page
    * 
    * @param array $actions - actions list from SYS_SCREEN_ACTION table
    * @return string - html code
    */
    public function renderActions($actions = array()) {
        if (!is_array($this->actions))
            $this->actions = array();
            
        if (!is_array($actions)) 
            $actions = array();
            
        if (empty($actions))
            $actions = $this->getActions();
            
        return '<script type="text/javascript">' . PHP_EOL . implode(PHP_EOL, array_map(create_function('$item', 'return $item["SSA_ACTION"];'), $actions)) . PHP_EOL . '</script>';
    }

    public function render($attribs = array(), $tabs = array(), $headers = array(), $fields = array(), $actions = array(), $defaults = array()) {
        if (isset($attribs['tabs'])) {
            $tabs = $attribs['tabs'];
            unset($attribs['tabs']);
        }
        
        if (isset($attribs['headers'])) {
            $headers = $attribs['headers'];
            unset($attribs['headers']);
        }
        
        if (isset($attribs['fields'])) {
            $fields = $attribs['fields'];
            unset($attribs['fields']);
        }
        
        if (isset($attribs['actions'])) {
            $actions = $attribs['actions'];
            unset($attribs['actions']);
        }
        
        if (isset($attribs['defaults'])) {
            $defaults = $attribs['defaults'];
            unset($attribs['defaults']);
        }
        
        if (!empty($tabs)) $this->setTabs($tabs);
        if (!empty($fields)) $this->setFields($fields);
        if (!empty($actions)) $this->setActions($actions);
        if (!empty($headers)) $this->setHeaders($headers);
        if (!empty($defaults)) $this->setDefaults($defaults);
        
        $tabs = $this->getTabs();
        $fields = $this->getFields();
        $actions = $this->getActions();
        $headers = $this->getHeaders();
        $defaults = $this->getDefaults();
        
        $xhtml  = '';
        $xhtml .= $this->renderTabs($attribs, $tabs, $headers, $fields, $actions, $defaults);
        $xhtml .= $this->renderActions($actions);
        
        return $xhtml;
    }
    
    public function sysScreenNewRow($attribs = array(), $tabs = array(), $headers = array(), $fields = array(), $actions = array(), $defaults = array()) {
        return $this->render($attribs, $tabs, $headers, $fields, $actions, $defaults);
    }
}