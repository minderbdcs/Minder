<?php

class PackId_Manager {
    public function findByLabel($packLabelNo) {
        $result = $this->getByLabel($packLabelNo);

        if (is_null($result)) {
            throw new Exception('Pack Id not found.');
        }

        return $result;
    }

    public function getByLabel($packLabelNo) {
        $result = Minder::getInstance()->fetchAssoc('SELECT * FROM PACK_ID WHERE DESPATCH_LABEL_NO = ?', $packLabelNo);
        return ($result === false) ? null : new PackId_PackId($result);
    }

    public function getPackAmountPerCarrier(array $carriers) {
        if (count($carriers) < 1) {
            return array();
        }

        $currentDate = date('Y-m-d');

        $query = "
            SELECT
                PICKD_CARRIER_ID,
                COUNT(PACK_ID) AS TOTAL_PACKS
            FROM
                PICK_DESPATCH
                LEFT JOIN PACK_ID ON PICK_DESPATCH.DESPATCH_ID = PACK_ID.DESPATCH_ID
            WHERE
                (PICKD_EXIT IS NULL OR PICKD_EXIT BETWEEN ZEROTIME(?) AND MAXTIME(?))
                AND PICKD_CARRIER_ID IN (" . substr(str_repeat('?, ', count($carriers)), 0, -2) . ")
            GROUP BY
                PICKD_CARRIER_ID
        ";

        array_unshift($carriers, $currentDate);
        array_unshift($carriers, $currentDate);
        array_unshift($carriers, $query);

        return call_user_func_array(array(Minder::getInstance(), 'fetchAllAssoc'), $carriers);
    }
}