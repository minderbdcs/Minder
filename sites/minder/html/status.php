<html>
    <body bgcolor="#ddddff" text="#000000">

 <h1>Barcode Printer Status</h1>

<table border="1" >
<?php
function testconn($host, $port, $timeout) 
{ 
  $tB = microtime(true); 
  $fP = fSockOpen($host, $port, $errno, $errstr, $timeout); 
  if (!$fP) { return "down"; } 
  $tA = microtime(true); 
  return round((($tA - $tB) * 1000), 0)." ms"; 
}

$version = 0;

$host   = 'downerdb:minder';
$username = 'minder';
$password = 'mindeR';

$dbInstance=ibase_connect($host, $username, $password);
$stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS from SYS_EQUIP where ( DEVICE_TYPE in ( \'PR\',\'LP\')  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE';
$sth = ibase_query($dbInstance, $stmt);
// Error handling?
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
    $lastlion = testconn($row->IP_ADDRESS, 9100, 1);
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


