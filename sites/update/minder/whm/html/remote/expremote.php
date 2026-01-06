  <?php
/*
want the database - use info from db_access for this
then want a list of possible devices to export to
then once decided run the shell script d:\asset.rf\script\expRemote.bat ipaddress remoteno
*/
  $docself = $_SERVER['PHP_SELF'];
  $ver = "2.0 - 2015.05.07";
  $contentarea = "";
require_once 'DB.php';
include "db_access.php";
  
/*
*/
   $currentRemoteDevice = "";
   if (isset($_GET['remoteDevice']))
   {
	$currentRemoteDevice = $_GET['remoteDevice'];
   }
   $remoteDevicesOptions = "";
   $remoteDevices = array();
   $remoteDevicesPart = array();
   $remoteCommand =  "";
// connect to db
   if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
   {
	echo("Can't connect to DATABASE!");
	//exit();
   }
   $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
   $Query = "select device_id,ip_address,remote_system_id from sys_equip where (remote_system_id is not null) and (remote_system_id > 1) "; 
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read SYS_EQUIP!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$remoteDevices[] =  $Row[0];
		$remoteDevicesPart[$Row[0]] = array('IP_ADDRESS' => $Row[1], 'REMOTE_ID' => $Row[2]);
	}
   }
   //release memory
   ibase_free_result($Result);
   foreach ($remoteDevices as $remoteDeviceIdx => $remoteDeviceValue ) {
	if ($remoteDeviceValue != "")
	{
		if ($remoteDeviceValue == $currentRemoteDevice)
		{
			//$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\" selected >$remoteDeviceValue";
			$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\" selected >$remoteDeviceValue" .  "-" . $remoteDevicesPart[$remoteDeviceValue]['IP_ADDRESS'];
		}
		else
		{
			//$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\">$remoteDeviceValue\n";
			$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\">$remoteDeviceValue" .  "-" . $remoteDevicesPart[$remoteDeviceValue]['IP_ADDRESS'];
		}
	}
   }
   $Query = "select description  from options where group_code = 'EXPREMOTE' ORDER BY CODE "; 
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read OPTIONS!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$remoteCommand =  $Row[0];

	}
   }
   //release memory
   ibase_free_result($Result);
//var_dump($remoteCommand);
  echo <<<HTMLCODE
  <!doctype html>
  <html>
<!DOCTYPE html>  
 <html>      
    <head>
    
      <style type="text/css">    
        body {
             font-size        : 12 pt;
             font-family      : arial;
             color            : #fa6;   /* white font color */
             background-color : #113;
             background-image : none;          
             background-repeat : no-repeat;
             line-height      : 12pt;
             margin           : 75px;  
          }
        a         {text-decoration: none;}
        a:link    {color:#9ef;} 
        a:visited {color:#9ef;} 
        a:active  {color:#9ef;} 
        a:hover   {color:#fb3; text-decoration: underline overline;}     
        h1 { text-align: center; color: #9cf;   /* white font color */ }  
        h2 { text-align: left; color: #afa; font-size: 100%  /* white font color */ }
        fieldset, textarea { text-decoration: none; -moz-border-radius:10px; border-radius: 10px; -webkit-border-radius: 10px; }
        input { text-decoration: none; font-size: 120%; -moz-border-radius:5px; border-radius: 5px; -webkit-border-radius: 5px; }
x
        </style>
      
      <script type="text/javascript">
      
        window.onload = function () {
           help.style.visibility = 'hidden';
        }
        
        function togglevisibility(id) {
          var e = document.getElementById(id);
          if(e.style.visibility == 'visible')
            e.style.visibility = 'hidden';
          else
            e.style.visibility = 'visible';
        } 
        
	function showPleaseWait() {
		var butt = document.getElementById("msgDiv");
		butt.innerHTML="Loading</br>Please Wait...";
	 return true;
	}
      </script>
      
    </head>    
    <body  >

        <h1>Export to Remote</h1> 
        <h2 style="text-align: center;">Version: $ver</h2>
        
HTMLCODE;

    
  // get action or use default
  if (isset($_GET['action'])) { 
    $action = $_GET['action']; 
  } else {
    $action = 'default'; 
  }  
  
  // save file
  if( $action == "Export") {
    //$content = stripslashes($content);
    if (isset($_GET['remoteDevice'])) {
      $currentRemoteDevice = $_GET['remoteDevice'];
      if ($currentRemoteDevice != "") {
      	// now get the ip address and remote system id for the device
	$remoteIPAddress = $remoteDevicesPart[$currentRemoteDevice]['IP_ADDRESS'];
	$remoteSystemID = $remoteDevicesPart[$currentRemoteDevice]['REMOTE_ID'];
	// ok now have the parameters
	// need the command to run - this will be from the options table
//var_dump($remoteCommand);
        $wkCMD = addslashes($remoteCommand) . " " . $remoteIPAddress . " " . $remoteSystemID . " 2>&1";
//var_dump($wkCMD);
        //$content = shell_exec($wkCMD);
        //$content = htmlspecialchars($content);
	$content = "";
	$wkResults = array();
	$retval = 0;
	$retval2 = 0;
	exec($wkCMD,$wkResults, $retval2);
	//echo ("Retval2:" . $retval2);
        //$content = $wkResults;
        $content = "";
        foreach($wkResults as $wkResultsIdx=>$wkResultsValue) {
		$content .= "\n" . $wkResultsValue;
		// if a line says Exported ### Transactions and ### > 0
		//$wkmyPosn = strpos($wkResultsValue, "Exported"); 
		//if (($wkmyPosn ) !== FALSE)
		//{
		//	$wkExportedTrans = intval(substr($wkResultsValue,strpos($wkResultsValue,"Exported") + 9));
		//	if ($wkExportedTrans > 0) {
		//		// then update the control.last_mirror_date
		//	}
		//}
	}
//var_dump($wkResults);
        $contentarea = "  <tr><td colspan=5>
          <textarea name=\"content\" cols=\"80\" rows=\"10\">". print_r($content,True)."</textarea><br>
          <center>Command contents</center><br>
          </td></tr>";
      }
      $remoteDevicesOptions = "";
      foreach ($remoteDevices as $remoteDeviceIdx => $remoteDeviceValue ) {
	if ($remoteDeviceValue != "")
	{
		if ($remoteDeviceValue == $currentRemoteDevice)
		{
			$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\" selected >$remoteDeviceValue";
		}
		else
		{
			$remoteDevicesOptions .=  "<option value=\"$remoteDeviceValue\">$remoteDeviceValue\n";
		}
	}
      }
    }
  }
  
  
    echo <<<HTMLCODE
    <table align=center ><tr><td>
      <form action="$docself" method=get >
        <fieldset width=500px>
          <table align=center >
          <tr><td width=100> </td> <td width=100> </td> <td width=100> </td> <td width=100> </td> <td width=100> </td></tr>
          $contentarea
          <tr><td colspan=2>
          Remote Device:<select name="remoteDevice" >
          $remoteDevicesOptions
          </select>
          <br>
          </td></tr>
          <tr>
          <td align=center><input type="submit" name="action" value="Export"  onClick="return showPleaseWait();"></td><td></td><td align=center><input type=button value="Help" onclick="togglevisibility('help');"></td><td></td><td align=center><input type=button value="Back" onclick="location.href='../..';"></td></tr>
          </table>
        </fieldset>
      </form>
    </td></tr>
    <tr><td>
    <div id="help">
      <br>
      <input type=button value="Hide Help" style = "font-size: 70%;" onclick="togglevisibility('help');"><br>
      <h2>Export Database to Remote</h2>

      Version: $ver<br>
      By: Frank Leih  - MinderSeries.com<br>
      <br>
      Choose the Device to Export the Current Database to  then click Export to Start the Export.<br>
      <br>
    </div>
    <div id="msgDiv"></div>
    </td></tr>    
    </table>
    
    

  </body>
</html>
HTMLCODE;

?>

