<html>
    <body bgcolor="#ddddff" text="#000000">

 <h1>Barcode Printer Status</h1>

<table border="1" >
<?php
function ping($host, $port, $timeout) 
{ 
  $tB = microtime(true); 
  $fP = fSockOpen($host, $port, $errno, $errstr, $timeout); 
  if (!$fP) { return "down"; } 
  $tA = microtime(true); 
  return round((($tA - $tB) * 1000), 0)." ms"; 
}

$version = 0;
$host   = 'localhost:apcd';
$host   = 'localhost:pinpoint';
$host   = 'localhost:minder';
$username = 'minder';
$password = 'mindeR';
/* ===================================================== */
$Host = "localhost";
  $mdrExists = False;
  // expect in document root something like "/var/sites/sitename/html"
  // so the 4th entry is the sitename
  $mdrDocRoot = explode("/", $_SERVER['DOCUMENT_ROOT']);
  $mdrSitename = $mdrDocRoot[3];
  $mdrConfig = "/etc/Minder/" . $mdrSitename . "/Minder.ini";
  $mdrConfig = strtolower($mdrConfig);
  $mdrExists = False;
  if(file_exists($mdrConfig) ) {
  	$mdrExists = True;
	//echo "$mdrConfig found";
  } else {
	echo "$mdrConfig not found";
	exit();
  }
  $mdr =  parse_ini_file($mdrConfig);
  $mdrDB = explode(":", $mdr['dsn.main']);
  $Host = $mdrDB[0];
  $DBAlias = $mdrDB[1];
  $host = $Host . ":" . $DBAlias;
/* ======================================================== */

$dbInstance=ibase_connect($host, $username, $password);
$stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS from SYS_EQUIP where ( DEVICE_TYPE in ( \'PR\',\'LP\')  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE';
$sth = ibase_query($dbInstance, $stmt);
while ($row = ibase_fetch_object($sth)) {
    print("<tr><td width=\"30%\">");
    print($row->IP_ADDRESS);
    print("</td><td width=\"10%\">");
    print($row->DEVICE_TYPE);
    print("</td><td width=\"10%\">");
    print($row->DEVICE_ID);
    print("</td><td width=\"30%\">");
    $command = '/bin/ping -nqc 1 -W 1 ' . $row->IP_ADDRESS . ' 2>&1 > /dev/null';
    //$lastLion = system($command, $retval);
    $lastlion = ping($row->IP_ADDRESS, 9100, 1);
    $retval = ($lastlion == "down")  ;

    if ($retval == 0 )
    {
        print(" <span style=\"color: #0c0;\"> Connected </span>");
    }
    else
    {
        print(" <span style=\"color: #c00;\"> Unreachable </span>");
    }
    print("</td></tr>");
}
ibase_free_result($sth);
ibase_close($dbInstance);

?>
   </table>
 </body>
</html>


