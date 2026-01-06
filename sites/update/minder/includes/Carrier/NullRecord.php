<?php

class Carrier_NullRecord extends Carrier {
    public function existedRecord()
    {
        return false;
    }

    public function __get($name)
    {
        return null;
    }


}