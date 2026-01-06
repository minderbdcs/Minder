<?php

abstract class Transaction_DSOT extends Transaction {

    /**
     * @param string $transactionResponse
     * @return Transaction_Response_DSOT|Transaction_Response_Interface
     */
    public function parseResponse($transactionResponse)
    {
        return new Transaction_Response_DSOT($transactionResponse);
    }

}