<?php

class Minder_ChartRenderer {
    const ORDER_STATISTICS = 'ORDER_STATISTICS';
    

    protected static function _getChartRenderer($chartName) {
        switch ($chartName) {
            case self::ORDER_STATISTICS:
                return new Minder_ChartRenderer_OrderStatistics();
        }

        throw new Minder_Exception('Bad Chart Name "' . $chartName . '"');
    }

    public static function render($chartName) {
        self::_getChartRenderer($chartName)->render();
    }
}