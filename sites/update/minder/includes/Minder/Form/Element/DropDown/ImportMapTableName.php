<?php

class Minder_Form_Element_DropDown_ImportMapTableName extends Minder_Form_Element_DropDown {
    public function init()
    {
        $sql = "
            SELECT DISTINCT 
                DESCRIPTION,
                DESCRIPTION AS LABEL
            FROM
                OPTIONS
            WHERE
                GROUP_CODE = ?
        ";

        $this->setMultiOptions(Minder::getInstance()->fetchAllAssoc($sql, 'IMPORT_MAP'));
        $this->setValueField('DESCRIPTION');
        $this->setLabelField('LABEL');

        if (!Minder::getInstance()->isSysAdmin())
            $this->setAttrib('disabled', 'disabled');
    }
}