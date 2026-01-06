<?php

class Minder_Page_FormBuilder_EditForm_Default implements Minder_Page_FormBuilder_Interface {
    protected $_ssName         = null;

    /**
     * @var Minder_Db_Table_SysScreenRow
     */
    protected $_sysScreenEntry = null;

    protected $_editResultTabs = null;

    /**
     * @var array
     */
    protected $_editResultFields = null;

    protected $_editResultButtons = null;

    protected $_editResultActions = null;

    protected function _setSsName($value) {
        $this->_ssName = strval($value);
        return $this;
    }

    protected function _getSsName() {
        return $this->_ssName;
    }

    /**
     * @return Minder_Db_Table_SysScreenRow | null
     */
    protected function _getSysScreenEntry() {
        if (is_null($this->_sysScreenEntry))
            $this->_sysScreenEntry = $this->_findSysScreenEntry();

        return $this->_sysScreenEntry;
    }

    /**
     * @return Minder_Page_Mapper_SysScreen
     */
    protected function _getSysScreenMapper() {
        return new Minder_Page_Mapper_SysScreen();
    }

    /**
     * @return Minder2_Environment
     */
    protected function _getEnvironment() {
        return Minder2_Environment::getInstance();
    }

    /**
     * @return null|Minder_Db_Table_SysScreenRow
     */
    protected function _findSysScreenEntry() {
        return $this->_getSysScreenMapper()->find($this->_getSsName(), $this->_getEnvironment());
    }

    /**
     * @return Minder_Page_Mapper_SysScreenVar
     */
    protected function _getEditResultMapper() {
        return new Minder_Page_Mapper_SysScreenVar();
    }

    /**
     * @return array
     */
    protected function _fetchEditResultFields() {
        return $this->_getEditResultMapper()->fetchEditResultsFields($this->_getSsName(), $this->_getEnvironment());
    }

    /**
     * @return null | array
     */
    protected function _getEditResultFields() {
        if (is_null($this->_editResultFields))
            $this->_editResultFields = $this->_fetchEditResultFields();

        return $this->_editResultFields;
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return string
     */
    protected function _getFieldType($fieldEntry) {

        switch (substr(strtoupper($fieldEntry->SSV_INPUT_METHOD), 0, 2)) {
            case 'IN':
                return 'minderInput';
            case 'DD':
                return 'minderDropDown';
            case 'DP':
                return 'minderDatePicker';
            case 'DDAJAX':
                return 'minderComboBox';
            case 'RO':
                return 'minderReadOnly';
            case 'CB':
                return 'minderCheckBox';
            default:
                return 'hidden';
        }
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return string
     */
    protected function _formatElementName($fieldEntry) {
        return 'FIELD_' . $fieldEntry->RECORD_ID;
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return string
     */
    protected function _formatElementAlias($fieldEntry) {
        return empty($fieldEntry->SSV_ALIAS) ? $fieldEntry->SSV_TABLE . '_' . $fieldEntry->SSV_NAME : $fieldEntry->SSV_ALIAS;
    }

    protected function _getEditResultActionMapper() {
        return new Minder_Page_Mapper_SysScreenAction();
    }

    protected function _fetchEditResultActions() {
        return $this->_getEditResultActionMapper()->fetchEditResultsActions($this->_getSsName(), $this->_getEnvironment());
    }

    protected function _getEditResultActions() {
        if (is_null($this->_editResultActions))
            $this->_editResultActions = $this->_fetchEditResultActions();

        return $this->_editResultActions;
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @param Minder_Db_Table_SysScreenActionRow $actionEntry
     * @return string
     */
    protected function _formatElementActionHandlerName($fieldEntry, $actionEntry) {
        return strtoupper($this->_formatElementName($fieldEntry) . '.' . $actionEntry->SSA_ACTION_TYPE);
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return array
     */
    protected function _getElementHandlers($fieldEntry) {
        $result = array();
        $ssvAction = strtoupper($fieldEntry->SSV_ACTION);

        foreach ($this->_getEditResultActions() as $actionEntry) {
            /**
             * @var Minder_Db_Table_SysScreenActionRow $actionEntry
             */

            if ($ssvAction != strtoupper($actionEntry->SSV_NAME))
                continue;

            $handlerName = $this->_formatElementActionHandlerName($fieldEntry, $actionEntry);
            $result[$handlerName] = isset($result[$handlerName]) ? $result[$handlerName] : array();
            $result[$handlerName][] = $actionEntry->SSA_ACTION;
        }

        return $result;
    }

    /**
     * @param Minder_Db_Table_SysScreenVarRow $fieldEntry
     * @return array
     */
    protected function _getElementConfig($fieldEntry) {
        return array(
            'name' => $this->_formatElementName($fieldEntry),
            'options' => array(
                'label' => $fieldEntry->SSV_TITLE,
                'minderOptions' => $fieldEntry->toArray(),
                'data-type' => 'MinderElement',
                'data-ssv_alias' => $this->_formatElementAlias($fieldEntry),
                'data-ss_name' => $fieldEntry->SS_NAME,
                'data-name' => $this->_formatElementName($fieldEntry),
                'order' => $fieldEntry->SSV_SEQUENCE,
                'handlers' => $this->_getElementHandlers($fieldEntry)
            ),
            'type' => $this->_getFieldType($fieldEntry)
        );
    }

    /**
     * @param Minder_Db_Table_SysScreenButtonRow $buttonEntry
     * @return string
     */
    protected function _formatOnClickHandlerName($buttonEntry) {
        return $this->_formatButtonName($buttonEntry) . '.CLICK';
    }

    /**
     * @param Minder_Db_Table_SysScreenButtonRow $buttonEntry
     * @return array
     */
    protected function _getButtonElementConfig($buttonEntry) {
        return array(
            'name' => $this->_formatButtonName($buttonEntry),
            'options' => array(
                'label' => $buttonEntry->SSB_TITLE,
                'order' => $buttonEntry->SSB_SEQUENCE,
                'data-name' => $this->_formatButtonName($buttonEntry),
                'handlers' => array(
                    $this->_formatOnClickHandlerName($buttonEntry) => array(
                        $buttonEntry->SSB_ACTION
                    )
                )
            ),
            'type' => 'MinderToolButton'
        );
    }

    protected function _getElements() {
        $result = array();

        foreach ($this->_getEditResultFields() as $fieldEntry) {
            $result[] = $this->_getElementConfig($fieldEntry);
        }

        foreach ($this->_getEditResultButtons() as $buttonEntry) {
            $result[] = $this->_getButtonElementConfig($buttonEntry);
        }

        return $result;
    }

    protected function _getDecorators() {
        return array(
            'FormElements',
            'HtmlTag' => array('decorator' => 'HtmlTag', 'options' => array('tag' => 'div')),
            'Form',
            'MinderEditForm'
        );
    }

    /**
     * @return Minder_Page_Mapper_SysScreenTab
     */
    protected function _getEditResultTabsMapper() {
        return new Minder_Page_Mapper_SysScreenTab();
    }

    /**
     * @return array
     */
    protected function _fetchEditResultTabs() {
        return $this->_getEditResultTabsMapper()->fetchEditResultTab($this->_getSsName(), $this->_getEnvironment());
    }

    /**
     * @return null | array
     */
    protected function _getEditResultTabs() {
        if (is_null($this->_editResultTabs))
            $this->_editResultTabs = $this->_fetchEditResultTabs();

        return $this->_editResultTabs;
    }

    protected function _formatPageName(Minder_Db_Table_SysScreenTabRow $tabEntry) {
        return $tabEntry->SS_NAME .'_ER_' . $tabEntry->SST_TAB_NAME;
    }

    protected function _getPagesDisplayGroups() {
        $result = array();

        /**
         * @var Minder_Db_Table_SysScreenTabRow $tabEntry
         */
        foreach ($this->_getEditResultTabs() as $tabEntry) {
            $result[$tabEntry->SST_TAB_NAME] = array(
                'options'  => array(
                    'displayGroupClass' => 'Minder_Form_DisplayGroup_FormPage',
                    'order' => $tabEntry->SST_SEQUENCE,
                    'attribs' => array(
                        'legend' => $tabEntry->SST_TITLE
                    ),
                    'minderOptions' => $tabEntry->toArray(),
                    'name' => $this->_formatPageName($tabEntry),
                    'decorators' => array(
                        'FormElements' => array(
                            'decorator' => 'GridLayout',
                            'options' => array(
                                'columns' => 2
                            )
                        ),
                        'FormPage' => array(
                            'decorator' => 'FormPage'
                        )
                    )
                ),
                'elements' => array()
            );
        }

        if (empty($result)) {
            $result['GENERAL'] = array(
                'options'  => array(
                    'order' => 0,
                    'name' => $this->_getSsName() . '_ER_GENERAL'
                ),
                'elements' => array()
            );
        }

        /**
         * @var Minder_Db_Table_SysScreenRow $fieldEntry
         */
        foreach ($this->_getEditResultFields() as $fieldEntry) {
            $elementName = $this->_formatElementName($fieldEntry);
            if (empty($fieldEntry->SSV_TAB)) {
                foreach ($result as &$pageConfig) $pageConfig['elements'][$elementName] = $elementName;
            } elseif (isset($result[$fieldEntry->SSV_TAB])) {
                $result[$fieldEntry->SSV_TAB]['elements'][$elementName] = $elementName;
            }
        }

        return $result;
    }

    /**
     * @return Minder_Page_Mapper_SysScreenButton
     */
    protected function _getEditResultButtonsMapper() {
        return new Minder_Page_Mapper_SysScreenButton();
    }

    /**
     * @return array
     */
    protected function _fetchEditResultButtons() {
        return $this->_getEditResultButtonsMapper()->fetchEditResultButtons($this->_getSsName(), $this->_getEnvironment());
    }

    /**
     * @return array
     */
    protected function _getEditResultButtons() {
        if (is_null($this->_editResultButtons))
            $this->_editResultButtons = $this->_fetchEditResultButtons();

        return $this->_editResultButtons;
    }

    /**
     * @param Minder_Db_Table_SysScreenButtonRow $buttonEntry
     * @return string
     */
    protected function _formatButtonName($buttonEntry) {
        return 'BUTTON_' . $buttonEntry->RECORD_ID;
    }

    protected function _getButtonGroups($minOrder, $maxOrder) {
        $result = array();

        $formButtons = $this->_getEditResultButtons();

        if (empty($formButtons))
            return $result;

        $bottomButtonPannel = $topButtonPannel = array(
            'options'  => array(
                'displayGroupClass' => 'Minder_Form_DisplayGroup_Toolbar'
            ),
            'elements' => array()
        );

        $topButtonPannel['options']['order']    = $minOrder - 1;
        $topButtonPannel['options']['name']     = 'topButtonPannel';
        $bottomButtonPannel['options']['order'] = $maxOrder + 1;
        $bottomButtonPannel['options']['name']  = 'bottomButtonPannel';

        foreach ($formButtons as $buttonEntry) {
            $buttonName = $this->_formatButtonName($buttonEntry);
            $topButtonPannel['elements'][]    = $buttonName;
            $bottomButtonPannel['elements'][] = $buttonName;
        }

        return array(
            $topButtonPannel['options']['name'] => $topButtonPannel,
            $bottomButtonPannel['options']['name'] => $bottomButtonPannel
        );
    }

    protected function _getDisplayGroups() {
        $result = array();

        $pages = $this->_getPagesDisplayGroups();
        $result = array_merge($result, $pages);

        $minOrder = 0;
        $maxOrder = 0;

        foreach ($result as $displayGroup) {
            $minOrder = ($minOrder > $displayGroup['options']['order']) ? $displayGroup['options']['order'] : $minOrder;
            $maxOrder = ($maxOrder < $displayGroup['options']['order']) ? $displayGroup['options']['order'] : $maxOrder;
        }

        if (count($pages) > 1) {
            $result['tab_container'] = array(
                'options' => array(
                    'order' => 0,
                    'displayGroupClass' => 'Minder_Form_DisplayGroup_FormTabs',
                    'data-name' => '_EDIT_FORM_TABS_' . $this->_getSsName(),
                    'data-ssv_alias' => '_EDIT_FORM_TABS_' . $this->_getSsName(),
                    'data-element-type' => 'FormTabs'
                ),
                'elements' => array_keys($pages)
            );

            $minOrder = 0;
            $maxOrder = 0;
        }

        $buttonGroups = $this->_getButtonGroups($minOrder, $maxOrder);
        $result = array_merge($result, $buttonGroups);

        return $result;
    }

    /**
     * @return Zend_Config
     * @throws Exception
     */
    protected function _doBuild() {
        $ssEntry = $this->_getSysScreenEntry();

        if (is_null($ssEntry))
            throw new Exception('SYS_SCREEN #' . $this->_getSsName() . ' not found.');

        $elements = $this->_getElements();

        if (empty($elements))
            throw new Exception('SYS_SCREEN #' . $this->_getSsName() . ' has no Edit Result fields.');

        $formConfigArray = array(
            'name' => $this->_getSsName(),
            'elements' => $elements,
            'displayGroups' => $this->_getDisplayGroups(),
            'attribs' => array(
                'data-type' => 'EditForm',
                'data-ss_name' => $this->_getSsName()
            )
        );

        $decorators = $this->_getDecorators();

        if (!empty($decorators))
            $formConfigArray['decorators'] = $decorators;

        return new Zend_Config($formConfigArray);
    }

    /**
     * @param $ssName
     * @return Zend_Config
     */
    public function build($ssName) {
        return $this->_setSsName($ssName)->_doBuild($ssName);
    }
}