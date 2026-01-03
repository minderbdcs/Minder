<html>
    <body bgcolor="#ddddff" text="#000000">

 <h1>Barcode Printer Status</h1>

<table border="1" >
<?php
$version = 0;
$host   = 'localhost:apcd';
$host   = 'localhost:sfi';
$host   = 'localhost:pinpoint';
$host   = 'localhost:minder';
$username = 'sysdba';
$password = 'masterkey';

$dbInstance=ibase_connect($host, $username, $password);
$stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS from SYS_EQUIP where ( DEVICE_TYPE = \'PR\'  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE';
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
    $lastLion = system($command, $retval);
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


