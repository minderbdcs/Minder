<?php

class PostcodeDepot_NullRecord extends PostcodeDepot {
    public function __get($name)
    {
        return null;
    }

    public function existedRecord()
    {
        return false;
    }


}