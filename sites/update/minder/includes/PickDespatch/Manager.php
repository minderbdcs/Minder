<?php

class PickDespatch_Manager {
    public function findByPackId(PackId_PackId $packId) {
        return $this->find($packId->DESPATCH_ID);
    }

    public function find($despatchId) {
        $result = $this->get($despatchId);

        if (is_null($result)) {
            throw new Exception('PICK_DESPATCH was not found.');
        }

        return $result;
    }

    public function get($despatchId) {
        $result = Minder::getInstance()->fetchAssoc('SELECT * FROM PICK_DESPATCH WHERE DESPATCH_ID = ?', $despatchId);

        return ($result === false) ? null : new PickDespatch_PickDespatch($result);
    }
}