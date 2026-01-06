<?php

class Minder2_Model_Mapper_Control {

    /**
     * @return Minder2_Model_Control
     */
    public function getControls() {
        $sql = "
            SELECT FIRST 1
                *
            FROM
                CONTROL
        ";

        $result = Minder::getInstance()->fetchAssoc($sql);

        if (false === $result) {
            return new Minder2_Model_Control();
        } else {
            $tmpControl = new Minder2_Model_Control($result);
            $tmpControl->existed = true;
        }

        return $tmpControl;
    }
}