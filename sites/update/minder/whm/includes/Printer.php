<?php

require_once "logme.php";
//require_once "Mdr/Minder/Version.php";
require_once "Minder/Version.php";

class Printer
{
    public $data;

    private $host;

    private $errno;

    private $errstr;


    public function Printer($host, $port = 9100)
    {
        $this->data = array();
        $this->host = $host;
        $this->port = $port;
    }

    public function send($tpl, $save = null )
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

    public function save($tpl, $save = null )
    {
        $msg = preg_replace_callback('/%([\w\d]*)%/', array($this, 'callback'), $tpl);
        if ($save) {
            fwrite($save, $msg);
            return true;
        }
        return false;
    }
    
    public function getLabelFieldWrapper($Link )
    {
	$result = '';
	$sql = 'SELECT LABEL_FIELD_WRAPPER FROM CONTROL';
	$q = ibase_prepare($Link, $sql);
	if ($q) {
        	$r = ibase_execute($q );
		if ($r) {
			$d = ibase_fetch_row($r);
			if ($d) {
				$result = $d[0];
			}
			ibase_free_result($r);
		}
		ibase_free_query($q);
	}
	if ($result == "")
	{
		$result = "%";
	}
	return $result ;
    }

    public function sysLabel($Link, $printerId, $printType, $printCopys = 1, $printNow = "T" )
    {
	/* calc the person */
    	list($user, $userDevice) = explode("|", $_COOKIE["LoginUser"]);
	/* calc the field seperator */
	$wkPrnSeperator = $this->getLabelFieldWrapper($Link);
	/* calc the data string */
	$wkPrnData = "";
	foreach ($this->data as $prnKey => $prnValue)
	{
		// now if the data is for a timestamp and end .nnnn
		// then trim back to the seconds
		$wkReplacedData1 =  preg_replace("/(\d+)-([A-Z]{3})-(\d+)\s(\d+):(\d+):(\d+)\.(\d+)/", "$1-$2-$3 $4:$5:$6", $prnValue);
		// eg 14-SEP-2013 11:55:58.0000
		$wkReplacedData =  preg_replace("/(\d+)\/([A-Z]{3})\/(\d+)\s(\d+):(\d+):(\d+)\.(\d+)/", "$1-$2-$3 $4:$5:$6", $wkReplacedData1);
		// eg 14/SEP/2013 11:55:58.0000
		//$wkPrnData = $wkPrnData . $wkPrnSeperator . $prnKey . $wkPrnSeperator . "=" . $prnValue . "|";
		$wkPrnData = $wkPrnData . $wkPrnSeperator . $prnKey . $wkPrnSeperator . "=" . $wkReplacedData . "|";
	}
	$myVersion = new Minder_Version;
	$thisVersion = $myVersion->getFull();
	$thisVersionPart = explode(".", $thisVersion);
	$wkPrnRelease = "";
	foreach ($thisVersionPart as $prnKey => $prnValue)
	{
		if ($prnKey < 3) {
			$wkPrnRelease .=  $prnValue . ".";
		}
	}
	$wkPrnRelease = substr($wkPrnRelease, 0, strlen($wkPrnRelease) - 1);
	//$wkPrnData .= $wkPrnSeperator . "MDRVersion" . $wkPrnSeperator . "=" . $thisVersionPart[3] . "|";
	$wkPrnData .= $wkPrnSeperator . "MDRVersion" . $wkPrnSeperator . "=" . $thisVersionPart[2] . "|";
	$wkPrnData .= $wkPrnSeperator . "MDRRelease" . $wkPrnSeperator . "=" . $wkPrnRelease . "||";
	// trim last bar
	if (strlen($wkPrnData) > 0)
	{
		$prnData = substr($wkPrnData, 0, strlen($wkPrnData) - 2);
	}
	else
	{
		$prnData = $wkPrnData;
	}
	/* send to pc_label procedure */
	$result = '';
	$errorText = '';
        if ($printNow == "T")
	{
		//$sql = 'SELECT RES, ERROR_TEXT FROM PC_LABEL(  ?, ?, ?, ?, ?, ?)';
		$sql = 'SELECT RES, ERROR_TEXT FROM PC_LABEL(  ?, ?, ?, ?, ?, ?, ?)';
		$q = ibase_prepare($Link, $sql);
		if ($q) {
        		//$r = ibase_execute($q, $printerId, $printType, $printCopys , "" , $prnData , $user );
        		$r = ibase_execute($q, $printerId, $printType, $printCopys , "" , $prnData , $user, $userDevice );
			if ($r) {
				$d = ibase_fetch_row($r);
				if ($d) {
					$result = $d[0];
					$errorText = $d[1];
				}
				ibase_free_result($r);
			}
			ibase_free_query($q);
		}
	}
	else
	{
		$sql = 'SELECT RES, ERROR_TEXT FROM PC_LABEL_LATER(  ?, ?, ?, ?, ?, ?, ?)';
		$q = ibase_prepare($Link, $sql);
		if ($q) {
        		$r = ibase_execute($q, $printerId, $printType, $printCopys , "" , $prnData , $user , $userDevice);
			if ($r) {
				$d = ibase_fetch_row($r);
				if ($d) {
					$result = $d[0];
					$errorText = $d[1];
				}
				ibase_free_result($r);
			}
			ibase_free_query($q);
		}
	}
	// if result is 0 then is OK
	// result -1 -> bartender file
	// anyother results must use the normal send
	//var_dump($errorText);
	setBDCScookie($Link, $userDevice, "LabelErrorText", $errorText);
	return (($result == 0) or ($result == -1));
    }
}

