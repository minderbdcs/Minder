<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
include "../login.inc";
?>
<html>
 <head>
  <title>Pick Select Order to Revert</title>
<?php
include "viewport.php";
if (strpos($_SERVER['HTTP_USER_AGENT'] , "NetFront") === false)
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD.css">');
}
else
{
	echo('<link rel=stylesheet type="text/css" href="verifyLD-netfront.css">');
}
?>
 </head>
<script type="text/javascript">
function errorHandler(errorMessage,url,line) 
{
	document.write("<p><b>Error in JS:</b> "+errorMessage+"<br>")
	document.write("<b>URL:</b> "+url+"<br>")
	document.write("<b>Line:</b> "+line+"</p>")
	return true
}
function processEdit( ) {
  document.getdetails.message.value=" ";
  return true;
}
function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getperson.selorder.value = myorder; 
  	document.getperson.submit(); 
	return true;
}
/* onerror = errorHandler */
</script>

<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";


function dopager( $Query)
{
	global $Host, $User, $Password, $DBName2;
	//load the adodb code
	require 'adodb/adodb.inc.php';
	//require 'adodb/adodb-pager.inc.php';
	require 'adodb/bdcs_pager.php';

	//connect to the database
	$conn = &ADONewConnection('ibase');
	list($myhost,$mydb) = explode(":", $DBName2,2);

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	//$pager = new ADODB_Pager($conn,$Query,'SalesOrder',false);
	$pager->Render(15);

}

echo "<body>";
//echo("<h4>Allocate - Get Qtys</h4>\n");
echo("<FORM action=\"getDXtoPL.php\" method=\"post\" name=\"getdetails\">\n");

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (isset($_POST['message']))
{
	$message = $_POST['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_POST['matchorders']))
{
	$matchorders = $_POST['matchorders'];
}
if (isset($_GET['matchorders']))
{
	$matchorders = $_GET['matchorders'];
}
if (isset($matchorders))
{
	if ($matchorders <> "")
	{
		setBDCScookie($Link, $tran_device, "MATCHORDERS", $matchorders  );
	}
}

if (!isset($matchorders))
{
	$matchorders = 	getBDCScookie($Link, $tran_device, "MATCHORDERS"  );
}

echo("<h4>Revert DX Orders Back to PL</h4>\n");
//          and diffdate(p1.create_date, 'NOW',4) < 45
$Query = "select p2.pick_order , p2.company_id as company, p1.awb_consignment_no as connote,p1.pickd_carrier_id as carrier, p1.pickd_service_type as serviceType, p1.pickd_service_record_id as serviceRecord, diffdate(p1.create_date, 'NOW',4) as ageDays
          from pick_despatch p1 
          join pick_order p2 on p1.pickd_pick_order1 = p2.pick_order
          where p1.despatch_status IN ('DX','DC')  
          and   p2.pick_status IN ('DX','DA')  
         and  ( not exists (select p3.pick_order from pick_invoice p3 where p3.pick_order=p1.pickd_pick_order1)) ";
        // and diffdate(p1.create_date, 'NOW',4) < 145 ";
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			$Query .= "and   p1.pickd_pick_order1 like '%" . $matchorders . "%' ";
		}
	}
	//$Query .= "
        //  group by p2.pick_order , p2.company_id , p1.awb_consignment_no,p1.pickd_carrier_id, p1.pickd_service_type, p1.pickd_service_record_id , ageDays ";
	//$Query .= "
        //  group by ageDays, p2.pick_order , p2.company_id , p1.awb_consignment_no,p1.pickd_carrier_id, p1.pickd_service_type, p1.pickd_service_record_id , ageDays ";
	$Query .= "
          group by p2.company_id,  p2.pick_order , p1.awb_consignment_no,p1.pickd_carrier_id, p1.pickd_service_type, p1.pickd_service_record_id , ageDays ";

//echo($Query);

/*
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Get Orders for Choice!<BR>\n");
	exit();
}

*/


echo ("<table>\n");
echo ("<tr><td>\n");
//echo("Orders Matching:</td><td><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
echo("Matching:</td><td colspan=\"3\"><INPUT name=\"matchorders\" type=\"text\" value=\"") ;
if (isset($matchorders))
{
	echo($matchorders);
}
//echo( "\"  size=\"10\" maxlength=\"10\" onchange=\"return processEdit('". $wk_max_orders . "');\" >\n");
echo( "\"  size=\"15\" maxlength=\"15\" onchange=\"return processEdit('". $wk_max_orders . "');\" >\n");
echo ("</td></tr>\n");
echo ("</table>\n");

echo ("<table>\n");
echo ("<tr>\n");
echo("<th>Select Order</th>\n");
echo ("</tr>\n");
echo ("</table>\n");
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	echo("<INPUT type=\"submit\" name=\"accept\" value=\"Accept\">\n");
}
else
{
	echo("<BUTTON name=\"accept\" value=\"Accept\" type=\"submit\">\n");
	echo("Accept<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
}
*/
//echo("</form>\n");

dopager($Query);
/*
if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
{
	// html 3.2 browser
	echo("<form action=\"./despatch_menu.php\" method=\"post\" name=goback>\n");
	echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
	echo("</form>\n");
}
else
*/
{
	// html 4.0 browser
	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./pick_Menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
}
	echo("<form action=\"getDXtoPLs.php\" method=\"post\" name=getperson> \n");
	echo("<INPUT type=\"hidden\" name=\"selorder\"> ");  
	echo("<INPUT type=\"hidden\" name=\"matchorders\"  ");  
	if (isset($matchorders))
	{
		if ($matchorders <> "")
		{
			echo(" value=\"" . $matchorders . "\" ");
		}
		echo(" >\n");
	}
	echo("</form>\n");
?>
</body>
</html>
