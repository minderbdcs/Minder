<?php
  
class Minder_View_Helper_SysScreenSearchForm extends Zend_View_Helper_FormElement
{
    protected $actions = array();

    public function setActions($actions = array()) {
        if (!is_array($actions))
            $actions = array();
            
        $this->actions = $actions;
        
        return $this;
    }
    
    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }

    public function sysScreenSearchForm($attribs= array(), $searchFields = array(), $actions = array()) {
        return $this->render($attribs, $searchFields, $actions);
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

    public function renderButtons($formId, $extraButtons = array()) {
        $searchBtnId = $this->view->escape($formId) . '_search_btn';
        $clearBtnId  = $this->view->escape($formId) . '_clear_btn';

        $xhtml = '
            <li><input class="mdr-button ' . $searchBtnId . '" type="submit" value="Submit Search" id="' . $searchBtnId . '"></li>
            <li><input class="mdr-button ' . $clearBtnId . '" type="submit" value="Clear" id="' . $clearBtnId . '"></li>
        ';

        foreach ($extraButtons as $buttonId => $buttonName) {
            $xhtml .= '
                <li><input class="mdr-button ' . $buttonId . '" type="button" value="' . $buttonName . '" onclick="return false;"></li>
            ';
        }

        $xhtml = '
                    <input type="hidden" name="SEARCH_FORM_ACTION" value="search">
                    <ul class="toolbar">' . $xhtml . '</ul>

                    <script type="text/javascript">
                        $("#' . $clearBtnId . '").click(function(evt) {
                            evt.preventDefault();
                            $("#' . $this->view->escape($formId) . ' input[type=text]").val("");
                            $("#' . $this->view->escape($formId) . ' select").attr("selectedIndex", 0);
                        });
                    </script>
        ';

        return $xhtml;
    }
    
    protected function _render($attribs = array(), $searchFields = array(), $actions = array()) {
        if (isset($attribs['search_fields'])) {
            $searchFields = $attribs['search_fields'];
            unset($attribs['search_fields']);
        }

        if (isset($attribs['actions'])) {
            $actions = $attribs['actions'];
            unset($attribs['actions']);
        }

        $sortedFields = $searchFields;
        
        usort($sortedFields, array($this, '_sortCallback'));
        
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
        
        $method = 'POST';
        if (isset($attribs['method'])) {
            $method = $attribs['method'];
            unset($attribs['method']);
        }

        $extraButtons = array();
        if (isset($attribs['extra_buttons'])) {
            $extraButtons = $attribs['extra_buttons'];
            unset($attribs['extra_buttons']);
        }
        
        $xhtml  = '<!-- search form ' . $this->view->escape($name) . ' starts -->';
        $xhtml .= '<form method="' . $this->view->escape($method) . '" name="' . $this->view->escape($name) . '" id="' . $this->view->escape($id) . '" ' . $this->_htmlAttribs($attribs) . ' autocomplete="off">';
        $xhtml .= '<table>';
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
                case 'IN':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormText($attribs, $fieldDesc, $actions) . '</td>';
                    $currentCollumnNo++;
                    break;

                case 'DP':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormDatePicker($attribs, $fieldDesc, $actions) . '</td>';
                    $currentCollumnNo++;
                    break;
                    
                case 'DD':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormDropDown($attribs, $fieldDesc, $actions) . '</td>';
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

                case 'RB':
                    $xhtml .= '<th>' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $xhtml .= '<td>' . $this->view->sysScreenFormRadio($attribs, $fieldDesc, $actions) . '</td>';
                    $currentCollumnNo++;
                    break;
                    
                case 'RO':
                    $xhtml .= '<th colspan="2">' . $this->view->formLabel($searchFieldLabelName, $fieldDesc['SSV_TITLE'], $labelsAttribs) . '</th>';
                    $currentCollumnNo++;
                    break;
                case 'NONE':
                    break;
                    
                default:
                    throw new Minder_Exception("Unsupported SSV_INPUT_METHOD = '" . $fieldDesc['SSV_INPUT_METHOD'] . "' for Sys Screen Var #" . $fieldDesc['RECORD_ID'] . ".");
            }
            
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
        $xhtml .= '
            <tr>
                <td colspan="6">
                    ' . $this->renderButtons($id, $extraButtons) . '
                </td>
            </tr>
        ';
        
        
        $xhtml .= '</table>';
        $xhtml .= '</form>';
        $xhtml .= '<!-- ' . $this->view->escape($name) . ' SYS_SCREEN_ACTIONs starts -->';
        $xhtml .= $this->renderActions($actions);
        $xhtml .= '<!-- ' . $this->view->escape($name) . ' SYS_SCREEN_ACTIONs ends -->';
        $xhtml .= '<!-- search form ' . $this->view->escape($name) . ' ends -->';
        return $xhtml;
    }

    public function render($attribs = array(), $searchFields = array(), $actions = array()) {
        try {
            return $this->_render($attribs, $searchFields, $actions);
        } catch (Exception $e) {
            return "
            <script type='text/javascript'>
                showErrors([" . json_encode($e->getMessage()) . "]);
            </script>
            ";
        }

    }
}