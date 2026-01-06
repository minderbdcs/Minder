<?php

class Transaction
{
    public $code;
    public $class;
    public $timestamp;

    public function __construct()
    {
    }
}


class Transaction_GRND extends Transaction
{
    public $consignmentNote;
    public $shipDate;
    public $carrier;
    public $vehicalRegistration;
    public $type;
    
    public function __construct()
    {
        $this->code = 'GRND';
        $this->class = 'B';
        $this->timestamp = date('YmdHis');
    }

    public function __toString()
    {
        return $this->code
            . $this->class
            . $this->timestamp
            . str_pad(substr($this->consignmentNote, 0, 20), 20, ' ', STR_PAD_RIGHT)
            . '|'
            . $this->shipDate
            . str_pad(substr($this->carrier, 0, 20), 20, ' ', STR_PAD_RIGHT);
    }
}

$t = new Transaction_GRND();
$t->consignmentNote = 'Broken on arrival';
echo "$t\n";
