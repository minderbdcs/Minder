<?php

class Printer
{
    public $data;

    private $host;

    private $errno;

    private $errstr;


    public function __construct($host, $port = 9100)
    {
        $this->data = array();
        $this->host = $host;
        $this->port = $port;
    }

    public function send($tpl, $save = null)
    {
        $msg = preg_replace_callback('/%([\w\d]*)%/', array($this, 'callback'), $tpl);
        $fp = fsockopen($this->host, $this->port, $this->errno, $this->errstr, 8);
        if ($save) {
            fwrite($save, $msg);
        }
        if ($fp) {
            fwrite($fp, $msg);
            fclose($fp);
            return true;
        }

        return false;
    }

    public function callback($m)
    {
        if (array_key_exists($m[1], $this->data)) {
            return $this->data[$m[1]];
        }
        return '%' . $m[1] . '%';
    }
}

