<?php

class Minder_View_Helper_ExitCarrierDialog extends Zend_View_Helper_Abstract {
    /**
     * @var string
     */
    protected $_jsModelName;

    /**
     * @var array
     */
    protected $_searchResults;

    /**
     * @var string
     */
    protected $_dialogId;

    public function exitCarrierDialog($id, $jsModelName, $searchResults) {
        $this->_setDialogId($id);
        $this->_setJsModelName($jsModelName);
        $this->_setSearchResults($searchResults);

        return $this;
    }

    public function scannedIndicators($pad) {
        $xhtml = array();

        $xhtml[] = $pad . '<table>';
        $xhtml[] = $pad . '    <tr>';
        $xhtml[] = $pad . '        <td><label>Last Carrier</label></td>';
        $xhtml[] = $pad . '        <td><input type="text" readonly name="carrierId" /></td>';
        $xhtml[] = $pad . '    </tr>';
        $xhtml[] = $pad . '    <tr>';
        $xhtml[] = $pad . '        <td><label>Last Pack</label></td>';
        $xhtml[] = $pad . '        <td><input type="text" readonly name="packId" /></td>';
        $xhtml[] = $pad . '    </tr>';
        $xhtml[] = $pad . '    <tr>';
        $xhtml[] = $pad . '        <td><label>Last Pack Carrier</label></td>';
        $xhtml[] = $pad . '        <td><input type="text" readonly name="packCarrier" /></td>';
        $xhtml[] = $pad . '    </tr>';
        $xhtml[] = $pad . '</table >';

        return implode("\n", $xhtml);
    }

    public function carrierStatistics($pad) {
        $xhtml = array();

        $xhtml[] = $pad . '<div class="ui-tabs-panel">';
        $xhtml[] = $pad . '    <div class="sys-screen-title">Carrier Statistics</div>';
        $xhtml[] = $pad . '    <table class="tablesorter withborder">';
        $xhtml[] = $pad . '        <thead>';
        $xhtml[] = $pad . '            <tr>';
        $xhtml[] = $pad . '                <th>Carrier</th>';
        $xhtml[] = $pad . '                <th>Total Packs</th>';
        $xhtml[] = $pad . '            </tr>';
        $xhtml[] = $pad . '        </thead>';
        $xhtml[] = $pad . '        <tbody class="carriers-statistics">';
        $xhtml[] = $pad . '        </tbody>';
        $xhtml[] = $pad . '    </table>';
        $xhtml[] = $pad . '</div>';

        return implode("\n", $xhtml);
    }

    protected function _searchResults() {
        return $this->_standardPageHelper()->searchResultsContainer($this->_getSearchResults());
    }

    /**
     * @return Minder_View_Helper_StandardPage
     */
    protected function _standardPageHelper() {
        return $this->view->standardPage($this->_getJsModelName());
    }

    function __toString()
    {
        $xhtml = array();

        $xhtml[] = '<div id="' . $this->_getDialogId() . '" class="flora" style="height: 500px; overflow: auto">';
        $xhtml[] = '    <form action="#" style="overflow: auto" autocomplete="off">';
        $xhtml[] = '        <table style="width: 95%">';
        $xhtml[] = '            <tr>';
        $xhtml[] = '                <td>';
        $xhtml[] = $this->scannedIndicators('                ');
        $xhtml[] = '                </td>';
        $xhtml[] = '                <td><strong data-carrier-message="data-carrier-message"></strong></td>';
        $xhtml[] = '            </tr>';
        $xhtml[] = '            <tr>';
        $xhtml[] = '                <td colspan="2">';
        $xhtml[] = $this->carrierStatistics('                ');
        $xhtml[] = '                </td>';
        $xhtml[] = '            </tr>';
        $xhtml[] = '            <tr>';
        $xhtml[] = '                <td colspan="2">';
        $xhtml[] = $this->_searchResults();
        $xhtml[] = '                </td>';
        $xhtml[] = '            </tr>';
        $xhtml[] = '        </table >';
        $xhtml[] = '    </form>';
        $xhtml[] = '</div>';

        return implode("\n", $xhtml);
    }

    /**
     * @return string
     */
    protected function _getJsModelName()
    {
        return $this->_jsModelName;
    }

    /**
     * @param string $jsModelName
     * @return $this
     */
    protected function _setJsModelName($jsModelName)
    {
        $this->_jsModelName = $jsModelName;
        return $this;
    }

    /**
     * @return array
     */
    protected function _getSearchResults()
    {
        return $this->_searchResults;
    }

    /**
     * @param array $searchResults
     * @return $this
     */
    protected function _setSearchResults($searchResults)
    {
        $this->_searchResults = $searchResults;
        return $this;
    }

    /**
     * @return string
     */
    protected function _getDialogId()
    {
        return $this->_dialogId;
    }

    /**
     * @param string $dialogId
     * @return $this
     */
    protected function _setDialogId($dialogId)
    {
        $this->_dialogId = $dialogId;
        return $this;
    }


}