<?php

class Minder_SysScreen_PartBuilder_ModelFields extends Minder_SysScreen_PartBuilder_SRFields {
    protected $_partName                      = 'MODEL_FIELDS';

    protected function _doBuild()
    {
        $result = array();
        $rows = $this->_selectDbRows();

        foreach ($rows as $row) {
            $result[$row['RECORD_ID']] = $this->_prepareDbRow($row);
        }

        return $result;
    }


}