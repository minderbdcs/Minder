<?php

class Minder_View_Helper_PackDimensions extends Zend_View_Helper_Abstract {
    protected $_screenName = '';
    protected $_screenFields = array();
    protected $_screenTitle = '';
    protected $_screenActions = array();
    protected $_palletOwnerList = array('NONE' => 'NONE');
    protected $_uomGroups = null;
    protected $_uoms = null;
    protected $_ssccButtons = false;
    protected $_summary = true;

    public function packDimensions($screenName, $screenTitle, $screenFields, $screenActions, $palletOwnerList, $ssccButtons = false) {
        $this->setScreenFields($screenFields);
        $this->setScreenName($screenName);
        $this->setScreenTitle($screenTitle);
        $this->setScreenActions($screenActions);
        $this->setPalletOwnerList($palletOwnerList);
        $this->setSsccButtons($ssccButtons);
        return $this;
    }

    public function title($pad = '') {
        return $pad . '<div style="font-weight: bold; text-transform: uppercase;" title="' . $this->getScreenName() . '">' . $this->getScreenTitle() . '</div>';
    }

    public function headerField($fieldDescription, $uomOptions = null, $pad = '') {
        $result = $pad . $fieldDescription['SSV_TITLE'];

        $uomOptions = is_null($uomOptions) ? $this->_getUomOptions() : $uomOptions;

        switch ($fieldDescription['SSV_ALIAS']) {
            case 'L':
            case 'W':
            case 'H':
                $defaultDT = $this->_getDefaultDT();
                $result .= $this->_formSelect('DIMENSION_UOM', $defaultDT['CODE'], array('style' => 'width:50px;'), $uomOptions['DT']);
                break;
            case 'VOL':
                $vt = $this->_getConnoteVT();
                $result .= '&nbsp;(' . $vt['CODE'] . ')';
                break;
            case 'WT':
                $defaultWT = $this->_getDefaultWT();
                $result .= $this->_formSelect('PACK_WEIGHT_UOM', $defaultWT['CODE'], array('style' => 'width:50px;'), $uomOptions['WT']);
                break;
            case 'TOTAL_WT':
                $wt = $this->_getConnoteWT();
                $result .= '&nbsp;(' . $wt['CODE'] . ')';
                break;
            case 'TOTAL_VOL':
                $vt = $this->_getConnoteVT();
                $result .= '&nbsp;(' . $vt['CODE'] . ')';
                break;
        }
        return $result;

    }

    public function dimensionsHeader($pad = '') {
        $parts = array();

        $uomOptions = $this->_getUomOptions();
        $parts[] = $pad . '<thead><tr>';

        foreach ($this->getScreenFields() as $fieldDescription) {
            $parts[] = $pad . '    <th>';
            $parts[] = $this->headerField($fieldDescription, $uomOptions, $pad);
            $parts[] = $pad . '    </th>';
        }

        $parts[] = $pad . '</tr></thead>';

        return implode("\n", $parts);

    }

    public function dimensionsBody($pad = '') {
        $parts = array();

        $parts[] = $pad . '<tbody class="dimensions"><tr class="sample_row hidden">';

        foreach ($this->getScreenFields() as $fieldDescription) {
            $parts[] = $pad . '    <td data-column="' . $fieldDescription['SSV_ALIAS'] . '" data-row-id="sample">';
            $parts[] = $this->view->sysScreenFormElement(
                array(
                    'data-row-id' => 'sample',
                    'style' => 'width: 70px;'
                ),
                $fieldDescription,
                $this->getScreenActions()
            );
            if ($fieldDescription['SSV_ALIAS'] == 'TYPE') {
                $parts[] = $this->_formSelect(
                    'PALLET_OWNER',
                    'NONE',
                    array(
                        'data-row-id' => 'sample',
                        'style' => 'width: 70px;',
//                        'class' => 'hidden'
                    ),
                    $this->getPalletOwnerList()
                );
            }

            $parts[] = $pad . '    </td>';
        }

        $parts[] = $pad . '</tr></tbody>';

        return implode("\n", $parts);

    }

    public function dimensions($pad = '') {
        $parts = array();

        $parts[] = $pad . '<table class="withborder tablesorter pack_dimentions" style="margin-bottom: 0px; margin-top: 0px;">';
        $parts[] = $this->dimensionsHeader($pad . '    ');
        $parts[] = $this->dimensionsBody($pad . '    ');
        $parts[] = $pad . '</table>';

        return implode("\n", $parts);
    }

    public function summary($pad = '') {
        $parts = array();

        $parts[] = $pad . '<table class="withborder" width="100%">';
        $parts[] = $pad . '    <tr>';
        $parts[] = $pad . '        <td>Total Entered Pallets: <span class="total_pallets">0</span></td>';
        $parts[] = $pad . '        <td>Total Entered Cartons: <span class="total_cartons">0</span></td>';
        $parts[] = $pad . '        <td>Total Entered Satchels: <span class="total_satchels">0</span></td>';
        $parts[] = $pad . '        <td>Total Required Labels: <span class="total_labels">0</span></td>';
        $parts[] = $pad . '    </tr>';
        $parts[] = $pad . '    <tr>';
        $parts[] = $pad . '        <td>Overall Total Packages: <span class="total_packages">0</span></td>';
        $parts[] = $pad . '        <td>&nbsp;</td>';
        $parts[] = $pad . '        <td>Total Weight: <span class="total_weight">0</span> Kgs</td>';
        $parts[] = $pad . '        <td>Total Volume: <span class="total_volume">0</span> Cu. Mtrs.</td>';
        $parts[] = $pad . '    </tr>';
        $parts[] = $pad . '</table>';

        return implode("\n", $parts);
    }

    public function ssccButtons($pad = '') {
        $parts = array();

        $parts[] = $pad . '<ul class="toolbar">';
        $parts[] = $pad . '    <li><input type="submit" name="ACCEPT_SSCC" class="mdr-tool-button" value="Accept" onclick="return false;" /></li>';
        $parts[] = $pad . '    <li><input type="submit" name="CANCEL_ACCEPT_SSCC" class="mdr-tool-button" value="Cancel Accept" onclick="return false;" /></li>';
        $parts[] = $pad . '    <li><input type="submit" name="REPACK_SSCC" class="mdr-tool-button" value="RePack" onclick="return false;" /></li>';
        $parts[] = $pad . '</ul>';

        return implode("\n", $parts);
    }

    function __toString() {
        $parts = array();

        $parts[] = '<div class="ui-tabs-panel"><form autocomplete="off">';
        $parts[] = $this->title('    ');
        $parts[] = '    <div>';
        $parts[] = $this->dimensions('        ');
        if ($this->hasSummary()) {
            $parts[] = $this->summary('        ');
        }
        if ($this->hasSsccButtons()) {
            $parts[] = $this->ssccButtons('        ');
        }
        $parts[] = '    </div>';
        $parts[] = '</form></div>';

        return implode("\n", $parts);
    }

    /**
     * @return string
     */
    public function getScreenName()
    {
        return $this->_screenName;
    }

    /**
     * @param string $screenName
     * @return $this
     */
    public function setScreenName($screenName)
    {
        $this->_screenName = $screenName;
        return $this;
    }

    /**
     * @return array
     */
    public function getScreenFields()
    {
        return $this->_screenFields;
    }

    /**
     * @param array $screenFields
     * @return $this
     */
    public function setScreenFields($screenFields)
    {
        $this->_screenFields = $screenFields;

        usort($this->_screenFields, function($a, $b) {
            return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
        });
        return $this;
    }

    /**
     * @return string
     */
    public function getScreenTitle()
    {
        return $this->_screenTitle;
    }

    /**
     * @param string $screenTitle
     * @return $this
     */
    public function setScreenTitle($screenTitle)
    {
        $this->_screenTitle = $screenTitle;
        return $this;
    }

    protected function _formSelect($string, $value, $attribs, $options)
    {
        return $this->view->formSelect($string, $value, $attribs, $options);
    }

    protected function _getUomGroups() {
        if (is_null($this->_uomGroups)) {
            $this->_uomGroups = $this->_getUomHelper()->getUomDescriptionByType(array('DT', 'WT'));
        }

        return $this->_uomGroups;
    }

    protected function _getUomOptions() {
        $uomSelects = array();

        foreach ($this->_getUomGroups() as $groupCode => $group) {
            $uomSelects[$groupCode] = array();
            foreach ($group as $code => $description) {
                $uomSelects[$groupCode][$code] = $code;
            }
        }

        return $uomSelects;
    }

    protected function _getDefaultDT() {
        $standardUoms = $this->_getUomHelper()->getUomTypes(array('DT'));
        $uoms = $this->_getUoms();

        return $uoms[$standardUoms['DT']['STANDARD_UOM']];
    }

    protected function _getDefaultWT() {
        $standardUoms = $this->_getUomHelper()->getUomTypes(array('WT'));
        $uoms = $this->_getUoms();

        return $uoms[$standardUoms['WT']['STANDARD_UOM']];
    }

    protected function _getConnoteWT() {
        $uoms = $this->_getUoms();
        $dsotUoms = $this->_getMinder()->getDsotUoms();
        return $uoms[$dsotUoms['WT']];
    }

    protected function _getConnoteVT() {
        $uoms = $this->_getUoms();
        $dsotUoms = $this->_getMinder()->getDsotUoms();
        return $uoms[$dsotUoms['VT']];
    }

    /**
     * @return array
     */
    public function getScreenActions()
    {
        return $this->_screenActions;
    }

    /**
     * @param array $screenActions
     * @return $this
     */
    public function setScreenActions($screenActions)
    {
        $this->_screenActions = $screenActions;
        return $this;
    }

    /**
     * @return array
     */
    public function getPalletOwnerList()
    {
        return $this->_palletOwnerList;
    }

    /**
     * @param array $palletOwnerList
     * @return $this
     */
    public function setPalletOwnerList($palletOwnerList)
    {
        $this->_palletOwnerList = $palletOwnerList;
        return $this;
    }

    protected function _getConnoteDTForVT() {
        $uoms = $this->_getUoms();
        $dsotUoms = $this->_getMinder()->getDsotUoms();
        return $uoms[$dsotUoms['DT_FOR_VT']];
    }

    protected function _fetchUoms() {
        $codes = array();

        foreach ($this->_getUomGroups() as $group) {
            foreach ($group as $code => $description) {
                $codes[] = $code;
            }
        }

        $standardUoms = $this->_getUomHelper()->getUomTypes(array('DT', 'WT'));

        $dsotUoms = $this->_getMinder()->getDsotUoms();
        $codes[] = $standardUoms['DT']['STANDARD_UOM'];
        $codes[] = $standardUoms['WT']['STANDARD_UOM'];
        $codes[] = $dsotUoms['VT'];
        $codes[] = $dsotUoms['WT'];
        $codes[] = $dsotUoms['DT_FOR_VT'];

        return $this->_getUomHelper()->UomDescriptor($codes);
    }

    protected function _getUoms() {
        if (is_null($this->_uoms)) {
            $this->_uoms = $this->_fetchUoms();
        }

        return $this->_uoms;
    }

    public function getUoms() {
        return array(
            'uom' => $this->_getUoms(),
            'DTtoVT' => $this->_getConnoteDTForVT(),
            'VT' => $this->_getConnoteVT(),
            'WT' => $this->_getConnoteWT(),
            'DEFAULT_DT' => $this->_getDefaultDT(),
            'DEFAULT_WT' => $this->_getDefaultWT(),
        );
    }

    /**
     * @return Minder_View_Helper_UomDescriptor
     */
    protected function _getUomHelper() {
        return $this->view->getHelper('UomDescriptor');
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @return boolean
     */
    public function hasSsccButtons()
    {
        return $this->_ssccButtons;
    }

    /**
     * @param boolean $ssccButtons
     * @return $this
     */
    public function setSsccButtons($ssccButtons)
    {
        $this->_ssccButtons = $ssccButtons;
        return $this;
    }

    public function hasSummary() {
        return $this->_summary;
    }

    /**
     * @param bool $summary
     * @return $this
     */
    public function setNoSummary($summary = false) {
        $this->_summary = $summary;
        return $this;
    }
}