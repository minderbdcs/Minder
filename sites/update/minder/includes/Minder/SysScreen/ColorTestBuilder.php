<?php

class Minder_SysScreen_ColorTestBuilder {
    public function buildColorTest($testDesc, $testExprPattern) {
        $operator = strtoupper(trim($testDesc['SSC_OPERATOR']));

        if ($operator == 'TRUE')
            return array("WHEN (1 = 1) THEN '" . $testDesc['SSC_COLOUR_NAME'] . "' " => array());

        $args          = array();

        switch ($operator) {
            case 'EQUAL':
                $testCondition = $testExprPattern . ' = ?';
                $args[]        = $testDesc['SSC_VALUE1'];
                break;
            case 'START':
                $testCondition = $testExprPattern . ' STARTING ?';
                $args[]        = $testDesc['SSC_VALUE1'];
                break;
            case 'END':
                $testCondition = $testExprPattern . ' LIKE ?';
                $args[]        = '%' . trim($testDesc['SSC_VALUE1'], "'");
                break;
            case 'ANY':
                $testCondition = $testExprPattern . ' LIKE ?';
                $args[]        = '%' . trim($testDesc['SSC_VALUE1'], "'") . '%';
                break;
            case 'BETWEEN':
                $tmpStr      = $testExprPattern . ' BETWEEN ';
                if (strtoupper($testDesc['SSC_VALUE1']) == "MAXTIME('TODAY')") {
                    $tmpStr .= "MAXTIME('TODAY') AND ";
                } else {
                    $tmpStr .= "? AND ";
                    $args[]  = $testDesc['SSC_VALUE1'];
                }

                if (strtoupper($testDesc['SSC_VALUE2']) == "MAXTIME('TODAY')") {
                    $tmpStr .= "MAXTIME('TODAY')";
                } else {
                    $tmpStr .= "?";
                    $args[]  = $testDesc['SSC_VALUE2'];
                }
                $testCondition = $tmpStr;

                break;
            case 'LESS':
                if (strtoupper($testDesc['SSC_VALUE1']) == "MAXTIME('TODAY')") {
                    $testCondition = $testExprPattern . " < MAXTIME('TODAY')";
                } else {
                    $testCondition = $testExprPattern . ' < ?';
                    $args[]        = $testDesc['SSC_VALUE1'];
                }
                break;
            case 'GREATER':
                if (strtoupper($testDesc['SSC_VALUE1']) == "MAXTIME('TODAY')") {
                    $testCondition = $testExprPattern . " > MAXTIME('TODAY')";
                } else {
                    $testCondition = $testExprPattern . ' > ?';
                    $args[]        = $testDesc['SSC_VALUE1'];
                }
                break;
            default:
                if (strtoupper($testDesc['SSC_VALUE1']) == "MAXTIME('TODAY')") {
                    $testCondition = $testExprPattern . ' ' . $operator . " MAXTIME('TODAY')";
                } else {
                    $testCondition = $testExprPattern . ' ' . $operator . " ?";
                    $args[]        = $testDesc['SSC_VALUE1'];
                }
        }
        return array("WHEN (" . $testCondition . ") THEN '" . $testDesc['SSC_COLOUR_NAME'] . "' " => $args);
    }

}