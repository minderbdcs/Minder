<?php
include "../login.inc";
?>
<html>
 <head>
<?php
include "viewport.php";
?>
  <title>SSN Description of Condition via Drop Downs</title>
<?php
{
	echo('<link rel=stylesheet type="text/css" href="dropssn.css">');
}
?>
 </head>
<script>

function passFields() {
 document.model.currentssn.value = document.getssn.ssn.value;
 return true
}
</script>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}
$message = "";
if (isset($_POST['currentssn']))
{
	$ssn = $_POST['currentssn'];
}
if (isset($_GET['currentssn']))
{
	$ssn = $_GET['currentssn'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['class']))
{
	$class = $_POST['class'];
}
if (isset($_GET['class']))
{
	$class = $_GET['class'];
}
if (!isset($class))
{
	$class = "A";
}
if (isset($_POST['when']))
{
	$when = $_POST['when'];
}
if (isset($_GET['when']))
{
	$when = $_GET['when'];
}
if (!isset($when))
{
	$when = "S";
	//$when = "O";
}
if (isset($_POST['productcond']))
{
	$productcond = $_POST['productcond'];
}
if (isset($_GET['productcond']))
{
	$productcond = $_GET['productcond'];
}
if (isset($_POST['classchange']))
{
	$classchange = $_POST['classchange'];
}
if (isset($_GET['classchange']))
{
	$classchange = $_GET['classchange'];
}
if (isset($_POST['whenchange']))
{
	$whenchange = $_POST['whenchange'];
}
if (isset($_GET['whenchange']))
{
	$whenchange = $_GET['whenchange'];
}
if (isset($_POST['productcondchange']))
{
	$productcondchange = $_POST['productcondchange'];
}
if (isset($_GET['productcondchange']))
{
	$productcondchange = $_GET['productcondchange'];
}
switch($class)
{
	case "A":
		$wk_ssn_var = "ssn.other1";
		$wk_field_no = 1;
		$tran_add = "NIAP";
		$tran_del = "NXAP";
		break;
	case "B":
		$wk_ssn_var = "ssn.other2";
		$wk_field_no = 2;
		$tran_add = "NIOP";
		$tran_del = "NXOP";
		break;
	case "C":
		$wk_ssn_var = "ssn.other3";
		$wk_field_no = 3;
		$tran_add = "NICP";
		$tran_del = "NXCP";
		break;
	case "D":
		$wk_ssn_var = "ssn.other4";
		$wk_field_no = 4;
		$tran_add = "NIDP";
		$tran_del = "NXDP";
		break;
	case "E":
		$wk_ssn_var = "ssn.other5";
		$wk_field_no = 5;
		$tran_add = "NIEP";
		$tran_del = "NXEP";
		break;
}

if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//release memory
//ibase_free_result($Result);


echo("<FONT size=\"2\">\n");
echo("<div id=\"col3\">");
if (isset($ssn))
{
	$Query = "select issn.wh_id, issn.locn_id ";
	$Query .= " FROM issn join ssn on ssn.ssn_id = issn.original_ssn where issn.ssn_id = '" . $ssn . "' ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read ISSN!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_location = $Row[0] . $Row[1];
	}
	if (isset($whenchange) and isset($classchange) )
	{
		include "transaction.php";
		if (($productcondchange == 1) and
		    ($whenchange == 0) and
		    ($classchange == 0)) 
		{
			if ($when == 'S')
			{
				//phpinfo();
				$Query = "SELECT description FROM product_cond_status where ssn_id = '" . $ssn . "' and code = '" . $class . "' and original_test = '" . $when . "' ";
				//echo($Query);
				if (!($Result2 = ibase_query($Link, $Query)))
				{
					echo("Unable to Read PRODUCT_COND_STATUS!<BR>\n");
					exit();
				}
				$wk_prodc_currlist = "";
				while ( ($Row2 = ibase_fetch_row($Result2)) ) 
				{
					$wk_prodc_currlist .= "|" . $Row2[0];
				}
				$wk_prodc_currlist .= "|";
				$wk_prodc_currarray = explode('|', $wk_prodc_currlist);
				//print_r($wk_prodc_currarray);
				// check whether condition exists
				$Query = "SELECT description FROM product_condition where code = '" . $class . "' ";
				//echo($Query);
				if (!($Result = ibase_query($Link, $Query)))
				{
					echo("Unable to Read PROD_CONDITION!<BR>\n");
					exit();
				}
				$wk_prodc_list = "";
				while ( ($Row = ibase_fetch_row($Result)) ) {
					$wk_this_cond = $Row[0];
					$wk_this_cond2 = str_replace(" ", "_", $wk_this_cond);
					$wk_this_cond = $wk_this_cond2;
					if (isset($_POST['PC-' . $wk_this_cond]))
					{
						// checknot already in current array
						$wk_this_prodc = $_POST['PC-' . $wk_this_cond];
						//print($wk_this_prodc);
						if (array_search($wk_this_prodc, $wk_prodc_currarray) === false)
						{
							// is ok so add to trans
							//print("not found");
							$wk_prodc_list .= "|" . $wk_this_prodc;
						}
						else
						{
							// flag as not to use
							//print("found");
							$wk_index = array_search($wk_this_prodc, $wk_prodc_currarray);
							$wk_prodc_currarray[$wk_index] = '';
						}
					}
					if (isset($_GET['PC-' . $wk_this_cond]))
					{
						// checknot already in current array
						$wk_this_prodc = $_GET['PC-' . $wk_this_cond];
						if (array_search($wk_this_prodc, $wk_prodc_currarray) === false)
						{
							// is ok so add to trans
							$wk_prodc_list .= "|" . $wk_this_prodc;
						}
						else
						{
							// flag as not to use
							$wk_index = array_search($wk_this_prodc, $wk_prodc_currarray);
							$wk_prodc_currarray[$wk_index] = '';
						}
					}
				}
				$wk_prodc_list .= "|";
				$wk_prodc_array = explode('|', $wk_prodc_list);
				foreach ($wk_prodc_array as $wk_key => $wk_prod_cond_item )
				{
					//echo $wk_key;
					if ($wk_prod_cond_item > "")
					{
						// do cond change transaction
						$my_message = dotransaction($tran_add, "A", $ssn, $current_location, "", $wk_prod_cond_item, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
						if ($my_message > "")
						{
							list($my_mess_field, $my_mess_label) = explode("=", $my_message);
							$my_responsemessage = urldecode($my_mess_label) . " ";
						}
						else
						{
							$my_responsemessage = " OK";
						}
						$message .= $wk_prod_cond_item. " " . $my_responsemessage;
					}
				}
				foreach ($wk_prodc_currarray as $wk_key => $wk_prod_cond_item )
				{
					//echo $wk_key;
					//echo $wk_prod_cond_item;
					if ($wk_prod_cond_item > "")
					{
						// do cond change transaction
						$my_message = dotransaction($tran_del, "A", $ssn, $current_location, "", $wk_prod_cond_item, 1, "SSSSSSSSS", $tran_user, $tran_device, "N");
						if ($my_message > "")
						{
							list($my_mess_field, $my_mess_label) = explode("=", $my_message);
							$my_responsemessage = urldecode($my_mess_label) . " ";
						}
						else
						{
							$my_responsemessage = " OK";
						}
						$message .= $wk_prod_cond_item. " " . $my_responsemessage;
					}
				}
			}
			else
			{
				// when is original
				$message = "Cannot Change the Original ";
			}
		}
		$wk_ssn_var = 0;
		$Query = "select  1 ";
		$Query .= " FROM issn join ssn on ssn.ssn_id = issn.original_ssn ";
		$Query .= "where issn.ssn_id = '" . $ssn . "' ";
		$Query .= " and ssn.other1 is null ";
		$Query .= " and ssn.other2 is null ";
		$Query .= " and ssn.other3 is null ";
		$Query .= " and ssn.other4 is null ";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read ISSN!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) {
			$current_ssn_var = $Row[0] ;
		}
	}
}
$Query = "select field1, dd_other1, rm_other1,";
$Query .= "field2, dd_other2, rm_other2,";
$Query .= "field3, dd_other3, rm_other3,";
$Query .= "field4, dd_other4, rm_other4,";
$Query .= "field5, dd_other5, rm_other5";
$Query .= " from ssn_group where ssn_group = 'DEFAULT' ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read SSN_GROUP!<BR>\n");
	exit();
}
$wk_other = array();
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_other[1] = array($Row2[0],$Row2[1], $Row2[2])  ;
		$wk_other[2] = array($Row2[3],$Row2[4], $Row2[5])  ;
		$wk_other[3] = array($Row2[6],$Row2[7], $Row2[9])  ;
		$wk_other[4] = array($Row2[9],$Row2[10], $Row2[11])  ;
		$wk_other[5] = array($Row2[12],$Row2[13], $Row2[14])  ;
}
ibase_free_result($Result2);
//print_r($wk_other);
echo("<FORM action=\"dropcond.php\" method=\"post\" name=getssn >");
echo("<INPUT type=\"hidden\" name=\"classchange\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"whenchange\" value=\"0\">");
echo("<INPUT type=\"hidden\" name=\"productcondchange\" value=\"0\">");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"100\" class=\"message\">\n");
echo('<table border="0">');
echo("<tr><td>");
echo("<input type=\"radio\" name=\"when\" value=\"O\" ");
if ($when == "O")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.whenchange.value='1';document.getssn.submit();\" ");
echo(">Original");
echo("</td><td>");
echo("<input type=\"radio\" name=\"when\" value=\"S\" ");
if ($when == "S")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.whenchange.value='1';document.getssn.submit();\" ");
echo(">Current");
echo ("</td></tr>");
echo("<tr><td>");
echo("Ssn:</td><td><INPUT type=\"text\" name=\"ssn\" readonly=\"Y\" size=\"20\" ");
echo(" value=\"");
if (isset($ssn))
{
	echo $ssn;
}
echo ("\" ></td></tr>\n");
echo("<tr><td>");
echo("<input type=\"radio\" name=\"class\" value=\"A\" ");
if ($class == "A")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.classchange.value='1';document.getssn.submit();\" ");
echo(">" . $wk_other[1][0]);
echo("</td><td>");
echo("<input type=\"radio\" name=\"class\" value=\"C\" ");
if ($class == "C")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.classchange.value='1';document.getssn.submit();\" ");
echo(">" . $wk_other[3][0]);
echo("</td></tr>\n");
echo("<tr><td>");
echo("<input type=\"radio\" name=\"class\" value=\"B\" ");
if ($class == "B")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.classchange.value='1';document.getssn.submit();\" ");
echo(">" . $wk_other[2][0]);
echo("</td><td>");
echo("<input type=\"radio\" name=\"class\" value=\"D\" ");
if ($class == "D")
{
	echo "checked " ;
}
echo(" onclick=\"document.getssn.classchange.value='1';document.getssn.submit();\" ");
echo(">" . $wk_other[4][0]);
$Query = "SELECT description FROM product_condition where code = '" . $class . "' ORDER BY description ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read PRODUCT_CONDITION!<BR>\n");
	exit();
}
$wk_condition = array();
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_condition[] = array($Row2[0],"F")  ;
}
ibase_free_result($Result2);
$wk_got_fault = "P";
//print_r($wk_condition);
$Query = "SELECT description FROM product_cond_status where ssn_id = '" . $ssn . "' and code = '" . $class . "' and original_test = '" . $when . "' ORDER BY description ";
//echo($Query);
if (!($Result2 = ibase_query($Link, $Query)))
{
	echo("Unable to Read PRODUCT_COND_STATUS!<BR>\n");
	exit();
}
//print_r(array_keys($wk_condition));
while ( ($Row2 = ibase_fetch_row($Result2)) ) {
		$wk_got_fault = "F";
		$wk_index = -1;	
		foreach ($wk_condition as $wk_key => $wk_prod_status )
		{
			//echo $wk_key;
			if ($wk_prod_status[0] == $Row2[0])
			{
				$wk_index = $wk_key;
				break;
			}
		}
		//echo("condition:" . $Row2[0] . " index " . $wk_index);
		if ($wk_index > -1)
		{
			$wk_condition[$wk_index][1] = "T" ;
			//echo("condition:" . $Row2[0] . " index " . $wk_index . " val " . $wk_condition[$wk_index][1]);
		}
}
//print_r($wk_condition);
ibase_free_result($Result2);
if ($current_ssn_var == 0)
{
	$Query = "select description ";
	$Query .= " FROM global_conditions where other_no = $wk_field_no and result = '" . $wk_got_fault . "'  ";
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to Read GLOBAL_CONDITIONS!<BR>\n");
		exit();
	}
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$current_ssn_var = $Row[0] ;
	}
}
else
{
	$current_ssn_var = "";
}
echo("<tr><td colspan=\"2\"><input type=\"text\" name=\"current_status\" value=\"" . $current_ssn_var . "\" readonly=\"Y\" size=\"40\"></td></tr>\n");
$Query = "SELECT description FROM product_condition where code = '" . $class . "' ORDER BY description ";
$wk_inline = 0;
foreach ($wk_condition as $wk_key => $wk_prod_status )
{
	//echo $wk_key;
	//print_r($wk_prod_status);
	if ($wk_prod_status[1] == "F")
	{
		if ($wk_inline == 0)
		{
			echo("<tr>");
			$wk_inline = 1;
		}
		else
		{
			$wk_inline = 0;
		}
		echo("<td>");
		echo("<input type=\"checkbox\" name=\"PC-" . $wk_prod_status[0] . "\" value=\"" . $wk_prod_status[0] . "\" ");
		echo(" onchange=\"document.getssn.productcondchange.value=1\" ");
		echo(">" . $wk_prod_status[0]);
		echo("</td>");
		if ($wk_inline == 0)
		{
			echo("</tr>\n");
		}
	}
	else
	{
		if ($wk_inline == 0)
		{
			echo("<tr>");
			$wk_inline = 1;
		}
		else
		{
			$wk_inline = 0;
		}
		echo("<input type=\"checkbox\" name=\"PC-" . $wk_prod_status[0] . "\" value=\"" . $wk_prod_status[0] . "\" ");
		echo "checked " ;
		echo(" onchange=\"document.getssn.productcondchange.value=1\" ");
		echo(">" . $wk_prod_status[0]);
		echo("</td>");
		if ($wk_inline == 0)
		{
			echo("</tr>\n");
		}
	}
}

echo("<tr><td>");
//release memory
ibase_free_result($Result);
//commit
ibase_commit($dbTran);
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Awhenept","./despatch_menu.php");
	whm2buttons('Accept',"./dropssn.php" ,"N" ,"SSN_50x100.gif" ,"Next SSN" ,"accept.gif");
	echo("<tr><td><form action=\"drop610.php\" method=\"post\" name=model onsubmit=\"return passFields();\" >\n");
	echo("<input type=\"hidden\" name=\"currentssn\" > ");  
	echo("<input type=\"IMAGE\" width=\"$rimg_width\" ");  
	echo('SRC="/icons/whm/Back_50x100.gif" alt="Back" >');
	echo("</form>\n");
	echo("</td></tr></table>\n");
}
echo("</div>\n");
echo("<script>");
if (isset($message))
{
	echo("document.getssn.message.value=\"" . $message . "\";");
}
else
{
	echo('document.getssn.message.value="Enter Changes";');
}
?>
</script>
</html>
