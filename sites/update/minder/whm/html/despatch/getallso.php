<?php
include "../login.inc";
?>
<html>
<head>
<?php
 include "viewport.php";
?>
</head>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";
include "logme.php";

// 18/09/07 must be able to see orders that don't have a company or wh_id !!!

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
	//echo "Host:" . $Host;
	//echo "User:" . $User;
	//echo "Password:" . $Password;
	//echo "DB:" . $mydb;

	$conn->connect($Host,$User,$Password,$mydb);

	//send a select
	$pager = new ADODB_Pager($conn,$Query,'SalesOrder',true);
	$pager->Render(7);

}

?>

<script type="text/javascript">
function saveMe(myorder) {

/* # save my order */
  var mytype;
	/* alert("in saveMe"); */
	/* alert(myorder); */
  	document.getperson.order.value = myorder; 
  	document.getperson.submit(); 
	return true;
}
</script>

<?php
list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);

if (isset($_GET['message']))
{
	$message = $_GET['message'];
	echo ("<B><FONT COLOR=RED>$message</FONT></B>\n");
}
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

// what fields have been saved

$wk_person = "";
$wk_company = "";
$wk_wh = "";

//phpinfo();

//echo($Query);


echo(" <body BGCOLOR=\"#0FFFFF\">\n");
echo("<h2>Choose Shipping Order to Despatch</h2>\n");
echo("<FONT size=\"2\">\n");

//echo("<FORM action=\"getallso.new.php\" method=\"post\" name=getorder\n>");
echo("<FORM action=\"getallso.php\" method=\"post\" name=getorder\n>");


//$Query = "select pick_item_detail.pick_label_no, pick_item.pick_order "; 
$Query = "select pick_item.pick_order as shiporder, count(distinct(pick_item_detail.pick_label_no)) as lines "; 
$Query .= "from pick_item_detail  ";
$Query .= "join pick_item on pick_item.pick_label_no = pick_item_detail.pick_label_no  ";
$Query .= " where pick_item_detail.pick_detail_status = 'DS' " ;
//$Query .= " group by pick_item.pick_order, pick_item_detail.pick_label_no " ;
$Query .= " group by pick_item.pick_order  " ;
$Query = "select pick_item_detail.pick_order as shiporder, count(distinct(pick_item_detail.pick_label_no)) as lines "; 
$Query .= "from pick_item_detail  ";
$Query .= " where pick_item_detail.pick_detail_status = 'DS' " ;
//$Query .= " group by pick_item_detail.pick_order, pick_item_detail.pick_label_no " ;
$Query .= " group by pick_item_detail.pick_order  " ;

$Query1 = str_replace("'","~",$Query);

//echo $Query;

if (strlen($Query1) > 250)
{
	$wk_query1 = substr($Query1,0,250);
	$wk_query2 = substr($Query1,250);

}
else
{
	$wk_query1 = $Query1;
	$wk_query2 = "";
}

// setBDCScookie($Link, $tran_device, "msalesquery3", $wk_query1);
// setBDCScookie($Link, $tran_device, "msalesquery4", $wk_query2);
//echo $Query;

dopager($Query);


{
	// html 4.0 browser
	echo("<table border=\"0\" align=\"left\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./despatch_menu.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
	echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='./despatch_menu.php';\">\n");
	echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
echo("<form action=\"getcarrier.php\" method=\"post\" name=\"getperson\">\n");
echo("<input type=\"hidden\" name=\"order\"> ");  

echo("</form>");
}
?>
</body>
</html>
