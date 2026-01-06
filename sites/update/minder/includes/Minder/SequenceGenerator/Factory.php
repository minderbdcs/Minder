<?php

class Minder_SequenceGenerator_Factory {
    public static function createGenerator($name, $startValue, $endValue, $sequenceType, $increment) {
        $increment = empty($increment) ? 1 : $increment;
        $sequenceType = strtoupper($sequenceType);
        switch ($sequenceType) {
            case 'A1':
                return new Minder_SequenceGenerator($name, $startValue,$endValue, $increment, 1, new Minder_SequenceGenerator_RegisterBase_Alpha());
            case 'N1':
                return new Minder_SequenceGenerator($name, $startValue,$endValue, $increment, 1, new Minder_SequenceGenerator_RegisterBase_Base10());
            case 'N2':
                return new Minder_SequenceGenerator($name, $startValue, $endValue, $increment, 2, new Minder_SequenceGenerator_RegisterBase_Base10());
            case 'A2':
                return new Minder_SequenceGenerator($name, $startValue, $endValue, $increment, 2, new Minder_SequenceGenerator_RegisterBase_Alpha());
            case 'N3':
                return new Minder_SequenceGenerator($name, $startValue, $endValue, $increment, 3, new Minder_SequenceGenerator_RegisterBase_Base10());
            case 'A3':
                return new Minder_SequenceGenerator($name, $startValue, $endValue, $increment, 3, new Minder_SequenceGenerator_RegisterBase_Alpha());
            case 'AN':
                return new Minder_SequenceGenerator(
                    $name,
                    $startValue,
                    $endValue,
                    $increment,
                    2,
                    array(
                        new Minder_SequenceGenerator_RegisterBase_Base10(),
                        new Minder_SequenceGenerator_RegisterBase_Alpha()
                    )
                );
            case 'NA':
                return new Minder_SequenceGenerator(
                    $name,
                    $startValue,
                    $endValue,
                    $increment,
                    2,
                    array(
                        new Minder_SequenceGenerator_RegisterBase_Alpha(),
                        new Minder_SequenceGenerator_RegisterBase_Base10()
                    )
                );
            default:
                throw new Minder_LocationGenerator_Exception('Unsupported Sequence Type "' . $sequenceType . '"');
        }
    }

    public static function createComplexGenerator($settings) {
        $result = array();

        foreach ($settings as $name => $gs) {
            $result[] = self::createGenerator($name, $gs['from'], $gs['to'], $gs['type'], $gs['step']);
        }

        return new Minder_SequenceGenerator_Composite(array_reverse($result));
    }
}