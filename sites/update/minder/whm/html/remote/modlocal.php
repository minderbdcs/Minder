  <?php
/*
want the database - use info from db_access for this
show on screen the current default wh_id
have an input field for the wh_id to use to change the default to
this must then do all the processing involved with setremote
*/
  $docself = $_SERVER['PHP_SELF'];
  $ver = "2.0 - 2015.05.27";
  $contentarea = "";
require_once 'DB.php';
include "db_access.php";
include "transaction.php";
  
/*
*/
   $currentRemoteWarehouse = "";
   $currentRemoteCostCenter = "NO";
   $currentRemoteCostCenterLocation =  "";
   //if (isset($_GET['remoteWarehouse']))
   //{
   //	$currentRemoteWarehouse = $_GET['remoteWarehouse'];
   //}
   $remoteWarehousesOptions = "";
   $remoteWarehouses = array();
   $remoteWarehousesPart = array();
   $remoteCommand =  "";
   $remoteCostCentersOptions = "";
   $remoteCostCenters = array();
   $remoteCostCentersPart = array();
   $remoteSystemId =  0;
// connect to db
   if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
   {
	echo("Can't connect to DATABASE!");
	//exit();
   }
   $dbTran = ibase_trans(IBASE_DEFAULT, $Link);
   $Query = "select w1.wh_id,w1.description,c1.default_wh_id from warehouse w1 join control c1 on c1.record_id=1  where w1.wh_id < 'X ' or w1.wh_id > 'X~'  order by w1.wh_id" ;
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read Warehouse!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$remoteWarehouses[] =  $Row[0];
		$remoteWarehousesPart[$Row[0]] = array('NAME' => $Row[1], 'CURRENT_WH_ID' => $Row[2]);
		$currentRemoteWarehouse = $Row[2];
	}
   }
   //release memory
   ibase_free_result($Result);
   // get my system id
   $Query = "select remote_system_id from control "; 
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read Control!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$remoteSystemId =  $Row[0];

	}
   }
   //release memory
   ibase_free_result($Result);
   // get current cost center location
   $Query = "select description  from options where group_code = 'DEF_RET_CC' and code = '" . $remoteSystemId . "'  "; 
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read OPTIONS!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$currentRemoteCostCenterLocation =  $Row[0];

	}
   }
   //release memory
   ibase_free_result($Result);
   if (!($currentRemoteCostCenterLocation == NULL))
   {
   	$currentRemoteCostCenter =  substr($currentRemoteCostCenterLocation,2,2);
   }
   foreach ($remoteWarehouses as $remoteWarehouseIdx => $remoteWarehouseValue ) {
	if ($remoteWarehouseValue != "")
	{
		if ($remoteWarehouseValue == $currentRemoteWarehouse)
		{
			$remoteWarehousesOptions .=  "<option value=\"$remoteWarehouseValue\" selected >$remoteWarehouseValue";
		}
		else
		{
			$remoteWarehousesOptions .=  "<option value=\"$remoteWarehouseValue\">$remoteWarehouseValue\n";
		}
	}
   }
// get cost centers
   $Query = "select w1.code,w1.description from cost_centre w1  where len(w1.code)= 2  order by w1.code" ;
   //echo($Query);
   if (!($Result = ibase_query($Link, $Query)))
   {
	echo("Unable to Read Cost Centres!<BR>\n");
	//exit();
   }
   while ( ($Row = ibase_fetch_row($Result)) ) {
	if ($Row[0] > "")
	{
		$remoteCostCenters[] =  $Row[0];
		$remoteCostCentersPart[$Row[0]] = array('NAME' => $Row[1] );
	}
   }
   //release memory
   ibase_free_result($Result);
   // add the none Cost center
   $remoteCostCenters[] =  "NO";
   $remoteCostCentersPart["NO"] = array('NAME' => "None" );

   foreach ($remoteCostCenters as $remoteCCIdx => $remoteCCValue ) {
	if ($remoteCCValue != "")
	{
		$remoteCCName = $remoteCostCentersPart[$remoteCCValue]['NAME'];
		if ($remoteCCValue == $currentRemoteCostCenter)
		{
			//$remoteCostCentersOptions .=  "<option value=\"$remoteCCValue\" selected >$remoteCCValue";
			$remoteCostCentersOptions .=  "<option value=\"$remoteCCValue\" selected >$remoteCCValue $remoteCCName";
		}
		else
		{
			//$remoteCostCentersOptions .=  "<option value=\"$remoteCCValue\">$remoteCCValue\n";
			$remoteCostCentersOptions .=  "<option value=\"$remoteCCValue\">$remoteCCValue $remoteCCName\n";
		}
	}
   }

   $Query = "select description  from options where group_code = 'UPDDEFWH' ORDER BY CODE "; 
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

        <h1>Set Default Warehouse</h1> 
        <h2 style="text-align: center;">Version: $ver</h2>
        
HTMLCODE;

    
  // get action or use default
  if (isset($_GET['action'])) { 
    $action = $_GET['action']; 
  } else {
    $action = 'default'; 
  }  
  
  // save file
  if( $action == "Update") {
    //$content = stripslashes($content);
    if (isset($_GET['remoteWarehouse'])) {
      $currentRemoteWarehouse = $_GET['remoteWarehouse'];
      $currentCostCenter = $_GET['remoteCostCenter'];
      if ($currentRemoteWarehouse != "") {
      	// now get the ip address and remote system id for the device
	$remoteName = $remoteWarehousesPart[$currentRemoteWarehouse]['NAME'];
	$remoteWHID = $remoteWarehousesPart[$currentRemoteWarehouse]['CURRENT_WH_ID'];
	// ok now have the parameters
	// need the command to run - this will be from the options table
//var_dump($remoteCommand);
        $wkCMD = addslashes($remoteCommand) . " " . $currentRemoteWarehouse . " " . $remoteWHID . " 2>&1";
//var_dump($wkCMD);
        //$content = shell_exec($wkCMD);
        //$content = htmlspecialchars($content);
	$content = "";
	$wkResults = array();
	$retval = 0;
	$retval2 = 0;
	//exec($wkCMD,$wkResults, $retval2);
	// run procedure to update this
        $content = "";
	{
		$tran_device = "XX";
		$tran_user = "bdcs";
		$transaction_type = "WHSD";
		$my_object = $currentRemoteWarehouse ;
		$my_source = 'SSSSSSSSS';
		$tran_tranclass = "M";
		$tran_qty = 0;
		$my_sublocn = $remoteWHID; /* the current warehouse */
		//$my_location = $currentRemoteWarehouse . '|' . $tran_device  . '|'  ;
		$my_location = $currentRemoteWarehouse . '|' . $currentRemoteCostCenter  . '|'  ;
		$my_ref = $remoteName . "|"  ;

		$my_message = "";
		$wk_db_error = False;
		$my_responsemessage = " ";
		//$my_message = dotransaction($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device,"Y");
		//$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device);
		$my_message = dotransaction_response($transaction_type, $tran_tranclass, $my_object, $my_location, $my_sublocn, $my_ref, $tran_qty, $my_source, $tran_user, $tran_device, '', '', '', '', '', 'ADMIN');
		//echo($my_message);
		if ($my_message > "") {
			list($my_mess_field, $my_mess_label) = explode("=", $my_message);
			$my_responsemessage = urldecode($my_mess_label) . " ";
		} else {
			$my_responsemessage = " ";
		}
		$content .= "\n" . $my_responsemessage;
	}
	//echo ("Retval2:" . $retval2);
        //$content = $wkResults;
        //foreach($wkResults as $wkResultsIdx=>$wkResultsValue) {
		//$content .= "\n" . $wkResultsValue;
		// if a line says Exported ### Transactions and ### > 0
		//$wkmyPosn = strpos($wkResultsValue, "Exported"); 
		//if (($wkmyPosn ) !== FALSE)
		//{
		//	$wkExportedTrans = intval(substr($wkResultsValue,strpos($wkResultsValue,"Exported") + 9));
		//	if ($wkExportedTrans > 0) {
		//		// then update the control.last_mirror_date
		//	}
		//}
	//}
//var_dump($wkResults);
        $contentarea = "  <tr><td colspan=5>
          <textarea name=\"content\" cols=\"80\" rows=\"10\">". print_r($content,True)."</textarea><br>
          <center>Command contents</center><br>
          </td></tr>";
      }
      $remoteWarehousesOptions = "";
      foreach ($remoteWarehouses as $remoteWarehouseIdx => $remoteWarehouseValue ) {
	if ($remoteWarehouseValue != "")
	{
		if ($remoteWarehouseValue == $currentRemoteWarehouse)
		{
			$remoteWarehousesOptions .=  "<option value=\"$remoteWarehouseValue\" selected >$remoteWarehouseValue";
		}
		else
		{
			$remoteWarehousesOptions .=  "<option value=\"$remoteWarehouseValue\">$remoteWarehouseValue\n";
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
          Default Warehouse:<select name="remoteWarehouse" >
          $remoteWarehousesOptions
          </select>
          </td><td>
          Cost Centre:<select name="remoteCostCenter" >
          $remoteCostCentersOptions
          </select>
          <br>
          </td></tr>
          <tr>
          <td align=center><input type="submit" name="action" value="Update"  onClick="return showPleaseWait();"></td><td></td><td align=center><input type=button value="Help" onclick="togglevisibility('help');"></td><td></td><td align=center><input type=button value="Back" onclick="location.href='../..';"></td></tr>
          </table>
        </fieldset>
      </form>
    </td></tr>
    <tr><td>
    <div id="help">
      <br>
      <input type=button value="Hide Help" style = "font-size: 70%;" onclick="togglevisibility('help');"><br>
      <h2>Set Default Warehouse</h2>

      Version: $ver<br>
      By: Frank Leih  - MinderSeries.com<br>
      <br>
      Enter the Default Warehouse.<br>
      Enter the Cost Centre to use then Click Update to set it.<br>
      <br>
    </div>
    <div id="msgDiv"></div>
    </td></tr>    
    </table>
    
    

  </body>
</html>
HTMLCODE;

?>

