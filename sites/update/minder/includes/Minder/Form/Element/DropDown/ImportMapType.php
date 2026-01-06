<?php

class Minder_Form_Element_DropDown_ImportMapType extends Minder_Form_Element_DropDown {
    public function init()
    {
        $sql = "
            SELECT
                CODE,
                DESCRIPTION
            FROM
                OPTIONS
            WHERE
                GROUP_CODE = ?
        ";

        if (false !== ($result = Minder::getInstance()->fetchAllAssoc($sql, 'IMPORT_MAP'))) {
            foreach ($result as &$resultRow) {
                list( $resultRow['LABEL'], $resultRow['VALUE']) = explode('|', $resultRow['CODE']);
            }
        } else {
            $result = array();
        }

        $this->setMultiOptions($result);
        $this->setValueField('VALUE');
        $this->setLabelField('CODE'); // LABEL
    }

}