<?php

require_once 'phplot-5.4.0/phplot.php';

/**
 * @property array orderStatistics
 */
class Minder_ChartRenderer_OrderStatistics {

    protected $_orderStatistics = null;

    function __get($name) {
        switch ($name) {
            case 'orderStatistics':
                return $this->_getOrderStatistics();
        }

        return null;
    }

    protected function _buildStatistics() {
        $minder = Minder::getInstance();

        $cfSql = 'SELECT COUNT (DISTINCT PICK_ORDER) FROM PICK_ORDER WHERE PICK_ORDER.pick_status = ?';
        $opSql = '
            SELECT
                COUNT(distinct OP.PICK_ORDER)
            FROM
                (SELECT DISTINCT PICK_ORDER FROM PICK_ITEM WHERE PICK_ITEM.pick_line_status = ?) AS OP
                LEFT JOIN (SELECT DISTINCT PICK_ORDER FROM PICK_ITEM WHERE PICK_ITEM.pick_line_status <> ?) AS NON_OP ON OP.PICK_ORDER = NON_OP.PICK_ORDER
            WHERE
                NON_OP.PICK_ORDER IS NULL
        ';
        $alSql = 'SELECT COUNT (DISTINCT PICK_ORDER) FROM PICK_ITEM WHERE PICK_ITEM.pick_line_status = ?';
        $cnSql = 'SELECT COUNT (DISTINCT PICK_ORDER) FROM PICK_ORDER WHERE PICK_ORDER.pick_status = ?';
        $upSql = 'SELECT COUNT (DISTINCT PICK_ORDER) FROM PICK_ORDER WHERE PICK_ORDER.pick_status = ?';
        $dcSql = 'SELECT COUNT (DISTINCT PICK_ORDER) FROM PICK_ORDER WHERE PICK_ORDER.pick_status = ?';
        $dxSql = '
            SELECT
                COUNT(DISTINCT PICK_ITEM_DETAIL.PICK_ORDER)
            FROM
                PICK_DESPATCH
                LEFT JOIN PICK_ITEM_DETAIL ON PICK_DESPATCH.DESPATCH_ID = PICK_ITEM_DETAIL.DESPATCH_ID
            WHERE
                PICK_DESPATCH.DESPATCH_STATUS IN (?, ?)
                AND PICK_DESPATCH.LAST_UPDATE_DATE BETWEEN ZEROTIME(?) AND MAXTIME(?)
        ';

        $date = date('Y-m-d');

        $this->_orderStatistics = array(
            'CF' => $minder->findValue($cfSql, 'CF'),
            'OP' => $minder->findValue($opSql, 'OP', 'OP'),
            'AL' => $minder->findValue($alSql, 'AL'),
            'CN' => $minder->findValue($cnSql, 'CN'),
            'UP' => $minder->findValue($upSql, 'UP'),
            'DC' => $minder->findValue($dcSql, 'DC'),
            'DX' => $minder->findValue($dxSql, 'DX', 'DC', $date, $date)
        );
    }

    protected function _getOrderStatistics() {
        if (is_null($this->_orderStatistics))
            $this->_buildStatistics();

        return $this->_orderStatistics;
    }

    public function render() {
        $plot = new PHPlot();
        $plot->SetPlotType('pie');
        $plot->SetDataType('text-data-single');
        $plot->SetTitle('Order Statistics');
        $plot->SetTTFPath(ROOT_DIR . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'fonts');
        $plot->SetUseTTF(true);
        $plot->SetFont('title', '', 18);
        $plot->SetFont('legend', '', 14);
        $plot->SetLegendStyle('left', 'right');

        $data = array();
        $legend = array();
        foreach ($this->orderStatistics as $label => $value) {
            $data[] = array($label, $value);
            $plot->SetLegend($label . ': ' . $value);
        }

        $plot->SetDataValues($data);
        $plot->label_abs_val = true;

        $plot->DrawGraph();
    }
}