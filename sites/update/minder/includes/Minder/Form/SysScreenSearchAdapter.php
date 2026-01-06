<?php

class Minder_Form_SysScreenSearchAdapter {

    /**
     * @var string
     */
    protected $_sysScreenName = null;

    /**
     * @var array
     */
    protected $_seFields = null;
    /**
     * @var array
     */
    protected $_actions  = null;
    /**
     * @var array
     */
    protected $_tabs     = null;
    /**
     * @var array
     */
    protected $_giFields = null;

    /**
     * @var Minder_Form 
     */
    protected $_minderForm = null;

    /**
     * @param Minder_Form|null $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setMinderForm(Minder_Form $val = null){
        $this->_minderForm = $val;
        return $this;
    }

    /**
     * @return Minder_Form
     */
    protected function _getMinderForm() {
        if (is_null($this->_minderForm))
            $this->_setMinderForm(new Minder_Form());

        return $this->_minderForm;
    }

    /**
     * @param string $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setSysScreenName($val) {
        $this->_sysScreenName = strval($val);

        $this->_setSeFields(null)
                ->_setActions(null)
                ->_setTabs(null)
                ->_setGiFields(null)
                ->_setMinderForm(null);

        return $this;
    }

    /**
     * @throws Exception
     * @return string
     */
    protected function _getSysScreenName() {
        if (empty($this->_sysScreenName))
            throw new Exception('sysScreenName is empty');

        return $this->_sysScreenName;
    }

    /**
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _buildSearchFormDescription() {
        $screenBuilder = new Minder_SysScreen_Builder();
        list(
            $searchFields,
            $actions,
            $tabs,
            $giFields
        ) = $screenBuilder->buildSysScreenSearchFields($this->_getSysScreenName());

        $this->_setSeFields($searchFields)->_setActions($actions)->_setTabs($tabs)->_setGiFields($giFields);

        return $this;
    }

    /**
     * @param array|null $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setSeFields(array $val = null) {
        $this->_seFields = $val;
        return $this;
    }

    /**
     * @param array|null $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setActions(array $val = null) {
        $this->_actions = $val;
        return $this;
    }

    /**
     * @param array|null $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setTabs(array $val = null) {
        $this->_tabs = $val;
        return $this;
    }

    /**
     * @param array|null $val
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _setGiFields(array $val = null) {
        $this->_giFields = $val;
        return $this;
    }

    /**
     * @return array
     */
    protected function  _getSeFields() {
        if (is_null($this->_seFields))
            $this->_buildSearchFormDescription();

        return $this->_seFields;
    }

    /**
     * @return array
     */
    protected function _getActions() {
        if (is_null($this->_actions))
            $this->_buildSearchFormDescription();

        return $this->_actions;
    }

    /**
     * @return array
     */
    protected function _getTabs() {
        if (is_null($this->_tabs))
            $this->_buildSearchFormDescription();

        return $this->_tabs;
    }

    /**
     * @return array
     */
    protected function _getGiFields() {
        if (is_null($this->_giFields))
            $this->_buildSearchFormDescription();

        return $this->_giFields;
    }

    /**
     * @return Minder_Form_SysScreenSearchAdapter
     */
    protected function _initFormAttribs() {
        $this->_getMinderForm()->setAttribs(array(
            'data-sys_screen_name' => $this->_getSysScreenName(),
            'name' => $this->_getSysScreenName() . '_SEARCH_FORM',
            'method' => 'POST'
        ));

        return $this;
    }

    protected function _initFormDecorators() {

        $this->_getMinderForm()->setDecorators(array(
                                                    'formElements' => array(
                                                        'decorator' => 'FormElements'
                                                    ),
                                                    'form' => array(
                                                        'decorator' => 'Form'
                                                    ),
                                                    'formDiv' => array(
                                                        'decorator' => array(
                                                            'formDiv' =>  'HtmlTag'
                                                        ),
                                                        'options' => array(
                                                            'tag' => 'div'
                                                        )
                                                    )
                                               ));

        return $this;
    }

    protected function _getElementName($fieldDescription) {
        return $fieldDescription['SSV_ALIAS'];
    }

    protected function _getElementTransformations($fieldDescription) {
        return isset($fieldDescription['TRANSFORMATIONS']) ? implode(' ', $fieldDescription['TRANSFORMATIONS']) : '';
    }

    protected function _buildFormElement($fieldDescription) {
        return $this->_getMinderForm()->createElement(
            $fieldDescription['SSV_INPUT_METHOD'],
            $this->_getElementName($fieldDescription),
            array(
                'order' => $fieldDescription['SSV_SEQUENCE'],
                'label' => $fieldDescription['SSV_TITLE'],
                'attribs' => array(
                    'data-transformations' => $this->_getElementTransformations($fieldDescription)
                )
            )
        );
    }

    protected function _buildFormElements() {
        $form = $this->_getMinderForm();

        foreach ($this->_getSeFields() as $fieldDescription) {
            $form->addElement($this->_buildFormElement($fieldDescription));
        }

        return $this;
    }

    protected function _buildTabbedForm() {
        //todo
    }

    protected function _buildSimpleForm() {
        $elements = array();

        foreach ($this->_getSeFields() as $fieldDescription) {
            $elements[] = $this->_getElementName($fieldDescription);
        }

        if (count($elements) < 1)
            throw new Exception('SYS_SCREEN #' . $this->_getSysScreenName() . ' has no search fields.');

        $this->_getMinderForm()->addDisplayGroup($elements, 'searchFields', array(
                                                              'order' => 0,
                                                              'decorators' =>array(
                                                                  'formElements' => array(
                                                                      'decorator' => array(
                                                                          'formElements' => 'GridLayout'
                                                                      ),
                                                                      'options' => array(
                                                                          'columns' => 3
                                                                      )
                                                                  )
                                                              )
                                                                            ));

        return $this;
    }

    protected function _getDefaultButtonOptions() {
        return array(
            'attribs' => array(
                'class' => 'green-button'
            ),
           'decorators' => array(
               'viewHelper' => 'ViewHelper',
               'htmlTag' => array(
                   'decorator' => 'HtmlTag',
                   'options' => array(
                       'tag' => 'li'
                   )
               )
           )
        );
    }

    protected function _buildSearchButtons() {
        $buttons = array();

        $searchButton = $this->_getDefaultButtonOptions();
        $searchButton['order'] = 0;
        $searchButton['label'] = 'Submit Search';
        $buttons[] = $this->_getMinderForm()->createElement(
            'button',
            'SEARCH_BUTTON',
            $searchButton
        );

        $cancelButton = $this->_getDefaultButtonOptions();
        $cancelButton['order'] = 1;
        $cancelButton['label'] = 'Clear';

        $buttons[] = $this->_getMinderForm()->createElement(
            'button',
            'CLEAR_BUTTON',
            $cancelButton
        );

        $this->_getMinderForm()->addElements($buttons);
        $this->_getMinderForm()->addDisplayGroup(array('SEARCH_BUTTON', 'CLEAR_BUTTON'), 'search_buttons', array(
                                                                                                                'order' => 1,
                                                                                           'decorators' => array(
                                                                                               'formElements' => 'FormElements',
                                                                                               'htmlTag' => array(
                                                                                                   'decorator' => 'HtmlTag',
                                                                                                   'options' => array(
                                                                                                       'tag' => 'ul',
                                                                                                       'class' => 'toolbar'
                                                                                                   )
                                                                                               )
                                                                                           )
                                                                                                           ));

        return $this;
    }

    public function build($sysScreenName, $fields) {
        $this->_setSysScreenName($sysScreenName)->_initFormAttribs()->_initFormDecorators()->_buildFormElements();

        $tabs = $this->_getTabs();

        if (count($tabs) > 1) {
            $this->_buildTabbedForm();
        } else {
            $this->_buildSimpleForm();
        }

        $this->_buildSearchButtons();

        return $this->_getMinderForm();
    }
}