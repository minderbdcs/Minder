<html>
<head>
    <script type="text/javascript" src="/minder/scripts/modernizr.custom.js"></script>
    <script type="text/javascript" src="/minder/scripts/detectizr.min.js"></script>
</head>
<body bgcolor="#ddddff" text="#000000">

<h1>Barcode Printer Status</h1>

<table border="1" >
    <?php
include "../whm/includes/db_access.php";
    function ping($host, $port, $timeout)
    {
        $tB = microtime(true);
        $fP = fSockOpen($host, $port, $errno, $errstr, $timeout);
        if (!$fP) { return "down"; }
        $tA = microtime(true);
        return round((($tA - $tB) * 1000), 0)." ms";
    }
    function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__FUNCTION__, $d);
		}
		else {
			// Return array
			return $d;
		}
     }


    $version = 0;
    //$host   = 'localhost:apcd';
    //$host   = 'localhost:sfi';
    //$host   = 'localhost:pinpoint';
    $host   = $Host . ":" . $DBAlias;
    //$username = 'minder';
    //$password = 'mindeR';

    //$dbInstance=ibase_connect($host, $username, $password);
    $dbInstance=ibase_pconnect($host, $User, $Password);
    $dbtran = ibase_trans(IBASE_DEFAULT, $dbInstance);
    //$command = '/usr/bin/python /data/asset.rf/python/script/printCups.py';
    $command = '/usr/bin/python /data/minder/python/script/printCups.py';
    $lastLion = shell_exec($command );
    $Printers = json_decode($lastLion);
    //var_dump($Printers);
    $PrintersArray = objectToArray($Printers);
    //var_dump($PrintersArray);
    //$stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS from SYS_EQUIP where ( DEVICE_TYPE in ( \'PR\',\'LP\')  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE';
    $stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS,COMPUTER_QUEUE from SYS_EQUIP where ( DEVICE_TYPE in ( \'PR\',\'LP\')  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE';
    $stmt = 'select DEVICE_ID,DEVICE_TYPE,IP_ADDRESS,COMPUTER_QUEUE, WH_ID from SYS_EQUIP where ( DEVICE_TYPE in ( \'PR\',\'LP\')  and IP_ADDRESS is not NULL and IP_ADDRESS <> \'DHCP\' ) order by DEVICE_TYPE, IP_ADDRESS';
    $sth = ibase_query($dbInstance, $stmt);
    while ($row = ibase_fetch_object($sth)) {
/*
        print("<tr><td width=\"30%\">");
        print($row->IP_ADDRESS);
        print("</td><td width=\"10%\">");
        print($row->DEVICE_TYPE);
        print("</td><td width=\"10%\">");
        print($row->DEVICE_ID);
        print("</td><td width=\"30%\">");
*/
        echo("<tr><td width=\"20%\">");
        echo($row->IP_ADDRESS);
        echo("</td><td width=\"5%\">");
        echo($row->DEVICE_TYPE);
        echo("</td><td width=\"5%\">");
        echo($row->DEVICE_ID);
        echo("</td><td width=\"15%\">");
        $command = '/bin/ping -nqc 1 -W 1 ' . $row->IP_ADDRESS . ' 2>&1 > /dev/null';
        //$lastLion = system($command, $retval);
        $lastlion = ping($row->IP_ADDRESS, 9100, 1);
        $retval = ($lastlion == "down")  ;

        if ($retval == 0 )
        {
            //print(" <span style=\"color: #0c0;\"> Connected </span>");
            echo(" <span style=\"color: #0c0;\"> Connected </span>");
        }
        else
        {
            //print(" <span style=\"color: #c00;\"> Unreachable </span>");
            echo(" <span style=\"color: #c00;\"> Unreachable </span>");
      }
      echo("</td><td width=\"5%\">");
      echo($row->COMPUTER_QUEUE);
      if ($row->COMPUTER_QUEUE <> "")
      {
         echo("</td><td width=\"20%\">");
         echo $PrintersArray[$row->COMPUTER_QUEUE]['printer-state-message'];
      }
      echo("</td><td width=\"5%\">");
      echo($row->WH_ID);
        //print("</td></tr>");
        echo("</td></tr>");
    }
    ibase_free_result($sth);
    ibase_commit($dbtran);
    ibase_close($dbInstance);

    ?>
</table>

<script>
    Modernizr.Detectizr.detect({});
    Modernizr.addTest("screenAttributes",function() {
        var _windowHeight = (window.innerHeight > 0) ? window.innerHeight : screen.width;
        var _windowWidth  = (window.innerWidth > 0) ? window.innerWidth : screen.width;
        var _colorDepth   = screen.colorDepth;
        return { windowHeight: _windowHeight, windowWidth: _windowWidth, colorDepth: _colorDepth };
    });
    console.log(Modernizr);
</script>

<h1>Device features</h1>
<table style="text-align: left">
    <tr>
        <th>Screen width</th>
        <td><script>document.write(Modernizr.screenattributes.windowWidth)</script></td>
    </tr>
    <tr>
        <th>Screen height</th>
        <td><script>document.write(Modernizr.screenattributes.windowHeight)</script></td>
    </tr>
    <tr>
        <th>Browser</th>
        <td><script>document.write(Modernizr.Detectizr.device.browser + ' ' + Modernizr.Detectizr.device.browserVersion + ' (' + Modernizr.Detectizr.device.browserEngine + ')' )</script></td>
    </tr>
    <tr>
        <th>OS</th>
        <td><script>document.write(Modernizr.Detectizr.device.os + ' ' + Modernizr.Detectizr.device.browser.osVersion + ' (' + Modernizr.Detectizr.device.browser.osVersionFull + ')')</script></td>
    </tr>
    <tr>
        <th>User Agent string</th>
        <td><script>document.write(Modernizr.Detectizr.device.userAgent)</script></td>
    </tr>

</table>

</body>
</html>
