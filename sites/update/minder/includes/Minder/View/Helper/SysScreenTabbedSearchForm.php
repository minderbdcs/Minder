<?php
class Minder_View_Helper_SysScreenTabbedSearchForm extends Zend_View_Helper_Form
{
    protected $tabs    = array();
    protected $fields  = array();
    protected $actions = array();
    
    
    public function sysScreenTabbedSearchForm($attribs = array(), $tabs = array(), $fields = array(), $actions = array()) {
        return $this->render($attribs, $tabs, $fields, $actions);
    }
    
    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $tabs
    * 
    * @return Minder_View_Helper_SysScreenTabbedSearchForm
    */
    public function setTabs($tabs = array()) {
        $this->tabs = array();
        usort($tabs, array($this , '_sortCallback'));
        foreach ($tabs as $tabDescription) {
            $this->tabs[strtoupper(trim($tabDescription['SS_NAME'] . '_' . $tabDescription['SST_TAB_NAME']))] = $tabDescription['SST_TITLE'];
        }
        
        return $this;
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $fields
    * 
    * @return Minder_View_Helper_SysScreenTabbedSearchForm
    */
    public function setFields($fields = array()) {
        $this->fields = array();
        
        foreach ($this->tabs as $tabId => $tabCaption)
            $this->fields[$tabId] = array();
            
        usort($fields, array($this , '_sortCallback'));
        foreach ($fields as $fieldDesc) {
            $tmpTabId = trim($fieldDesc['SSV_TAB']);
            if (empty($tmpTabId)) {
                foreach ($this->tabs as $tabId => $tabCaption) {
                    $this->fields[$tabId][$fieldDesc['RECORD_ID']] = $fieldDesc;
                }
            } else {
                $tmpTabId = strtoupper(trim($fieldDesc['SS_NAME']) . '_' . $tmpTabId);
                $this->fields[$tmpTabId][$fieldDesc['RECORD_ID']]  = $fieldDesc;
            }
        }
        
        return $this;
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $actions
    * 
    * @return Minder_View_Helper_SysScreenTabbedSearchForm
    */
    public function setActions($actions = array()) {
        if (!is_array($actions))
            $actions = array();
            
        $this->actions = $actions;
        
        return $this;
    }
    
    public function getTabs() {
        return $this->tabs;
    }
    
    public function getFields() {
        return $this->fields;
    }
    
    public function getActions() {
        return $this->actions;
    }
    
    public function renderTabsList($attribs = array()) {
        $xhtml  = '<ul>';
        
        $referencePrefix = 'tab_id_';
        if (isset($attribs['tab_reference_prefix'])) {
            $referencePrefix = $attribs['tab_reference_prefix'];
        }
        
        foreach ($this->tabs as $tabId => $tabCaption) {
            $tabReference = $this->view->escape($referencePrefix . 'SEARCH_' . $tabId);
            
            $tmpTabId = $this->view->escape('SEARCH_' . $tabId);
            
            $xhtml .= '<li tab_name="' . $tmpTabId . '" tab_id="' . $tmpTabId . '"><a href="#' . $tabReference . '" ><b><span>' . $this->view->escape($tabCaption) . '</span></b></a></li>';
        }
        
        $xhtml .= '</ul>';
        
        return $xhtml;
    }
    
    public function renderFields($tabId, $attribs) {
        if (!isset($this->tabs[$tabId]))
            return '';
        
        $tabContentClass = '';
        if (isset($attribs['class'])) {
            $tabContentClass = $attribs['class'];
        }
        
        $referencePrefix = 'tab_id_';
        if (isset($attribs['tab_reference_prefix'])) {
            $referencePrefix = $attribs['tab_reference_prefix'];
        }
        $tabReference = $this->view->escape($referencePrefix . 'SEARCH_' . $tabId);

        $sortedFields = $this->fields[$tabId];
        
        usort($sortedFields, array($this, '_sortCallback'));

        $xhtml  = '<table class="' . $tabContentClass . '" tab_id="' . $this->view->escape($tabReference) . '">';
        $xhtml .= '<tr>';
        $currentCollumnNo = 0;
        foreach ($sortedFields as $fieldDesc) {
            
            $tmpInputMethod = explode('|', $fieldDesc['SSV_INPUT_METHOD']);
            if ($tmpInputMethod[0] == 'dl')
                continue;
            
            $attribs['search_element'] = true;
            
            $searchFieldLabelName = 'SEARCH-' . (empty($fieldDesc['SSV_TABLE']) ? $fieldDesc['SSV_ALIAS'] : $fieldDesc['SSV_TABLE'] . '-' . $fieldDesc['SSV_ALIAS']);
            $labelsAttribs = array(
                'id' => $searchFieldLabelName,
                'original-value' => $fieldDesc['SSV_TITLE']
            );
            
            switch($tmpInputMethod[0]) {
                case 'RO':
                    $xhtml .= '<th colspan="2">' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $currentCollumnNo++;
                    break;
                case 'IN':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormText($attribs, $fieldDesc, $this->actions) . '</td>';
                    $currentCollumnNo++;
                    break;

                case 'DP':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormDatePicker($attribs, $fieldDesc, $this->actions) . '</td>';
                    $currentCollumnNo++;
                    break;
                    
                case 'DD':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormDropDown($attribs, $fieldDesc, $this->actions) . '</td>';
                    $currentCollumnNo++;
                    break;
                    
                case 'RB':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormRadio($attribs, $fieldDesc, $this->actions) . '</td>';
                    $currentCollumnNo++;
                    break;

                case 'DL':
                    if ($currentCollumnNo > 1) {
                        $currentCollumnNo = 0;
                        $xhtml .= '<th>&nbsp;</th><td>&nbsp;</td></tr><tr>';
                    }
                
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormDL($attribs, $fieldDesc) . '</td>';
                    $currentCollumnNo += 2;
                    break;
                    
                default:
                    throw new Minder_Exception("Unsupported SSV_INPUT_METHOD = '" . $fieldDesc['SSV_INPUT_METHOD'] . "' for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
            }

            $xhtml .= '<script type="text/javascript">
                $("#' . $tabReference . ' [name=' . $searchFieldLabelName . ']").change(function(evt){
                    var $_this = $(this);
                    var $_form = $_this.parents("form:first");
                    $_form.find("[name=' . $searchFieldLabelName . ']").val($_this.val());
                });
            </script>';


            if ($currentCollumnNo >= 3) {
                $currentCollumnNo = 0;
                
            $xhtml .= '</tr><tr>';
            }
            
        }
        
        if ($currentCollumnNo > 0) {
            while ($currentCollumnNo++ < 3)
                $xhtml .= '<td>&nbsp;</td><td>&nbsp;</td>';
        }
        
        $xhtml .= '</tr>';
        $xhtml .= '</table>';
        
        return $xhtml;
    }
    
    public function renderTabContent($tabId, $attribs) {
        if (!isset($this->tabs[$tabId]))
            return '';
        
        $tabContentMaxHeight = 'none';
        if (isset($attribs['max_height'])) {
            $tabContentMaxHeight = $attribs['max_height'];
        }
        
        $style = array(
            'margin-bottom: 0;'
        );
        if (is_numeric($tabContentMaxHeight)) {
            $style[] = 'height: ' . $tabContentMaxHeight . 'px;';
            $style[] = 'overflow: auto;';
        }
        
        $xhtml =  '<!-- tab content starts -->';
        $xhtml .= '<div style="' . implode(' ', $style) . '">';
        $xhtml .= $this->renderFields($tabId, $attribs);
        $xhtml .= '</div>';
        $xhtml .= '<!-- tab content ends -->';
        
        return $xhtml;
        
    }
    
    public function renderTab($tabId, $attribs) {
        if (!isset($this->tabs[$tabId]))
            return '';
        
        $referencePrefix = 'tab_id_';
        if (isset($attribs['tab_reference_prefix'])) {
            $referencePrefix = $attribs['tab_reference_prefix'];
            unset($attribs['tab_reference_prefix']);
        }
        $tabReference = $this->view->escape($referencePrefix . 'SEARCH_' . $tabId);
        
        $style = array(
        );
        if (isset($attribs['style'])) {
            $style = (is_array($attribs['style'])) ? $attribs['style'] : array($attribs['style']);
            unset($attribs['style']);
        }
        
        $tabContentAttribs = array();
        if (isset($attribs['tab_content'])) {
            $tabContentAttribs = $attribs['tab_content'];
            unset($attribs['tab_content']);
        }
        
        $xhtml  = '<div class="ui-tabs-panel" id="' . $tabReference . '"  style="' . implode(' ', $style) . '" ' . $this->_htmlAttribs($attribs) . '>';
        $xhtml .= $this->renderTabContent($tabId, $tabContentAttribs);
        $xhtml .= '</div>';
        
        return $xhtml;
        
    }
    
    
    /**
    * Render html code for given actions list, which can be inserted in to result page
    * 
    * @return string - html code
    */
    public function renderActions() {
        return '<script type="text/javascript">' . PHP_EOL . implode(PHP_EOL, array_map(create_function('$item', 'return $item["SSA_ACTION"];'), $this->actions)) . PHP_EOL . '</script>';
    }
    
    public function render($attribs = array(), $tabs = array(), $fields = array(), $actions = array()) {
        
        $formName     = 'search_results_form';
        $formId       = strtoupper($formName);
        $formAction   = '';
        $formMethod   = 'POST';
        $extraAttribs = array();
        
        if (isset($attribs['form'])) {
            if (isset($attribs['form']['name'])) {
                $formId = $formName = $this->view->escape($attribs['form']['name']);
                unset($attribs['form']['name']);
            }
            
            if (isset($attribs['form']['id'])) {
                $formId = $this->view->escape($attribs['form']['id']);
                unset($attribs['form']['id']);
            }
            
            if (isset($attribs['form']['action'])) {
                $formAction = $this->view->escape($attribs['form']['action']);
                unset($attribs['form']['action']);
            }
            
            if (isset($attribs['form']['method'])) {
                $formMethod = $this->view->escape($attribs['form']['method']);
                unset($attribs['form']['method']);
            }
            
            $extraAttribs = $attribs['form'];
            unset($attribs['form']);
        }
        
        $tabsContainerId = $formName . '_TABS';
        
        $tabsAttribs = array();
        if (isset($attribs['tabs'])) {
            $tabsAttribs = $attribs['tabs'];
            unset($attribs['tabs']);
        }
        
        if (!empty($tabs))
            $this->setTabs($tabs);
        
        if (!empty($fields))
            $this->setFields($fields);
        
        if (!empty($actions))
            $this->setActions($actions);
        
        $searchBtnClass = $formId . '_search_btn';
        $clearBtnClass  = $formId . '_clear_btn';
        
        $xhtml = '<form name="' . $formName . '" id="' . $formId . '" action="' . $formAction . '" method="' . $formMethod . '" ' . $this->_htmlAttribs($attribs) . '>';
        $xhtml .= '<!-- tabs container start -->';
        $xhtml .= '<div><div id="' . $tabsContainerId . '" class="ui-tabs-container">';
        $xhtml .= $this->renderTabsList($tabsAttribs);

        foreach ($this->tabs as $tabId => $tabCaption)
            $xhtml .= $this->renderTab($tabId, $tabsAttribs);
        
        
        $xhtml .= '<!-- tabs container end -->';
        $xhtml .= '
            <div>
                <ul class="toolbar">
                    <li><input class="mdr-button ' . $searchBtnClass . '" type="submit" value="Submit Search"></li>
                    <li><input class="mdr-button ' . $clearBtnClass . '" type="submit" value="Clear"></li>
                </ul>
            </div>
        ';
        $xhtml .= '</div></div>';
        $xhtml .= '
            <input type="hidden" name="SEARCH_FORM_ACTION" value="search">
            <script type="text/javascript">
                $(".' . $clearBtnClass . '").click(function(evt) {
                    evt.preventDefault();
                    $("#' . $formId . ' input[type=text]").val("");
                    $("#' . $formId . ' select").attr("selectedIndex", 0);
                });
                
                $(document).ready(function () {
                    $("#' . $tabsContainerId . ' > ul").tabs();
                });
            </script>
        ';
        $xhtml .= $this->renderActions();
        $xhtml .= '</form>';
        
        return $xhtml;
    }
}