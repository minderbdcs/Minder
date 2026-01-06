<html>
<head>
  <title>Label Generation via Drop Downs</title>
<?php
{
	echo('<link rel=stylesheet type="text/css" href="labeltype.css">');
}
?>
 </head>
<script>

function processEdit() {
  var mytype;
  /* document.gettype.message.value="in process edit"; */
  if ( document.gettype.sntype.value=="")
  {
  	document.gettype.message.value="Must Select the Type";
	document.gettype.sntype.focus()
  	return false
  }
/*
  if (!(( document.gettype.genbrand[0].checked) ||
       ( document.gettype.genbrand[1].checked)))
  {
  	document.gettype.message.value="Choose Brand or Generic";
	document.gettype.genbrand.focus()
  	return false
  }
*/
  return true;
}
function dogeneric() {
  var mytype;
  divbrand.style.visibility='hidden';
  divgeneric.style.visibility='visible';
  if ( document.gettype.generic1.value=="")
  {
    document.gettype.message.value="Select 1st Generic";
    document.gettype.generic1.focus(); 
  }
  else
  if ( document.gettype.generic2.value=="")
  {
    document.gettype.message.value="Select 2nd Generic";
    document.gettype.generic2.focus(); 
  }
  else
  if ( document.gettype.generic3.value=="")
  {
    document.gettype.message.value="Select 3rd Generic";
    document.gettype.generic3.focus(); 
  }
  else
  if ( document.gettype.generic4.value=="")
  {
    document.gettype.message.value="Select 4th Generic";
    document.gettype.generic4.focus(); 
  }
}
function dobrand() {
  var mytype;
  divgeneric.style.visibility='hidden';
  divbrand.style.visibility='visible';
  if ( document.gettype.brand1.value=="")
  {
    document.gettype.message.value="Select 1st Brand";
    document.gettype.brand1.focus(); 
  }
  else
  if ( document.gettype.brand2.value=="")
  {
    document.gettype.message.value="Select 2nd Brand";
    document.gettype.brand2.focus(); 
  }
  else
  if ( document.gettype.brand3.value=="")
  {
    document.gettype.message.value="Select 3rd Brand";
    document.gettype.brand3.focus(); 
  }
  else
  if ( document.gettype.brand4.value=="")
  {
    document.gettype.message.value="Select 4th Brand";
    document.gettype.brand4.focus(); 
  }
}
function do6() {
  var mytype;
  div10.style.visibility='hidden';
  div9.style.visibility='hidden';
  div8.style.visibility='hidden';
  div7.style.visibility='hidden';
  div6.style.visibility='visible';
  if ( document.gettype.other61.value=="")
  {
    document.gettype.message.value="Select 1st Other6";
    document.gettype.other61.focus(); 
  }
  else
  if ( document.gettype.other62.value=="")
  {
    document.gettype.message.value="Select 2nd Other6";
    document.gettype.other62.focus(); 
  }
  else
  if ( document.gettype.other63.value=="")
  {
    document.gettype.message.value="Select 3rd Other6";
    document.gettype.other63.focus(); 
  }
  else
  if ( document.gettype.other64.value=="")
  {
    document.gettype.message.value="Select 4th Other6";
    document.gettype.other64.focus(); 
  }
  else
  if ( document.gettype.other65.value=="")
  {
    document.gettype.message.value="Select 5th Other6";
    document.gettype.other65.focus(); 
  }
  else
  if ( document.gettype.other66.value=="")
  {
    document.gettype.message.value="Select 6th Other6";
    document.gettype.other66.focus(); 
  }
  else
  if ( document.gettype.other67.value=="")
  {
    document.gettype.message.value="Select 7th Other6";
    document.gettype.other67.focus(); 
  }
  else
  if ( document.gettype.other68.value=="")
  {
    document.gettype.message.value="Select 8th Other6";
    document.gettype.other68.focus(); 
  }
  else
  if ( document.gettype.other69.value=="")
  {
    document.gettype.message.value="Select 9th Other6";
    document.gettype.other69.focus(); 
  }
}
function do7() {
  var mytype;
  div10.style.visibility='hidden';
  div9.style.visibility='hidden';
  div8.style.visibility='hidden';
  div6.style.visibility='hidden';
  div7.style.visibility='visible';
  if ( document.gettype.other71.value=="")
  {
    document.gettype.message.value="Select 1st Other7";
    document.gettype.other71.focus(); 
  }
  else
  if ( document.gettype.other72.value=="")
  {
    document.gettype.message.value="Select 2nd Other7";
    document.gettype.other72.focus(); 
  }
  else
  if ( document.gettype.other73.value=="")
  {
    document.gettype.message.value="Select 3rd Other7";
    document.gettype.other73.focus(); 
  }
  else
  if ( document.gettype.other74.value=="")
  {
    document.gettype.message.value="Select 4th Other7";
    document.gettype.other74.focus(); 
  }
  else
  if ( document.gettype.other75.value=="")
  {
    document.gettype.message.value="Select 5th Other7";
    document.gettype.other75.focus(); 
  }
  else
  if ( document.gettype.other76.value=="")
  {
    document.gettype.message.value="Select 6th Other7";
    document.gettype.other76.focus(); 
  }
  else
  if ( document.gettype.other77.value=="")
  {
    document.gettype.message.value="Select 7th Other7";
    document.gettype.other77.focus(); 
  }
  else
  if ( document.gettype.other78.value=="")
  {
    document.gettype.message.value="Select 8th Other7";
    document.gettype.other78.focus(); 
  }
  else
  if ( document.gettype.other79.value=="")
  {
    document.gettype.message.value="Select 9th Other7";
    document.gettype.other79.focus(); 
  }
}
function do8() {
  var mytype;
  div10.style.visibility='hidden';
  div9.style.visibility='hidden';
  div6.style.visibility='hidden';
  div7.style.visibility='hidden';
  div8.style.visibility='visible';
  if ( document.gettype.other81.value=="")
  {
    document.gettype.message.value="Select 1st Other8";
    document.gettype.other81.focus(); 
  }
  else
  if ( document.gettype.other82.value=="")
  {
    document.gettype.message.value="Select 2nd Other8";
    document.gettype.other82.focus(); 
  }
  else
  if ( document.gettype.other83.value=="")
  {
    document.gettype.message.value="Select 3rd Other8";
    document.gettype.other83.focus(); 
  }
  else
  if ( document.gettype.other84.value=="")
  {
    document.gettype.message.value="Select 4th Other8";
    document.gettype.other84.focus(); 
  }
  else
  if ( document.gettype.other85.value=="")
  {
    document.gettype.message.value="Select 5th Other8";
    document.gettype.other85.focus(); 
  }
  else
  if ( document.gettype.other86.value=="")
  {
    document.gettype.message.value="Select 6th Other8";
    document.gettype.other86.focus(); 
  }
  else
  if ( document.gettype.other87.value=="")
  {
    document.gettype.message.value="Select 7th Other8";
    document.gettype.other87.focus(); 
  }
  else
  if ( document.gettype.other88.value=="")
  {
    document.gettype.message.value="Select 8th Other8";
    document.gettype.other88.focus(); 
  }
  else
  if ( document.gettype.other89.value=="")
  {
    document.gettype.message.value="Select 9th Other8";
    document.gettype.other89.focus(); 
  }
}
function do9() {
  var mytype;
  div10.style.visibility='hidden';
  div6.style.visibility='hidden';
  div7.style.visibility='hidden';
  div8.style.visibility='hidden';
  div9.style.visibility='visible';
  if ( document.gettype.other91.value=="")
  {
    document.gettype.message.value="Select 1st Other9";
    document.gettype.other91.focus(); 
  }
  else
  if ( document.gettype.other92.value=="")
  {
    document.gettype.message.value="Select 2nd Other9";
    document.gettype.other92.focus(); 
  }
  else
  if ( document.gettype.other93.value=="")
  {
    document.gettype.message.value="Select 3rd Other9";
    document.gettype.other93.focus(); 
  }
  else
  if ( document.gettype.other94.value=="")
  {
    document.gettype.message.value="Select 4th Other9";
    document.gettype.other94.focus(); 
  }
  else
  if ( document.gettype.other95.value=="")
  {
    document.gettype.message.value="Select 5th Other9";
    document.gettype.other95.focus(); 
  }
  else
  if ( document.gettype.other96.value=="")
  {
    document.gettype.message.value="Select 6th Other9";
    document.gettype.other96.focus(); 
  }
  else
  if ( document.gettype.other97.value=="")
  {
    document.gettype.message.value="Select 7th Other9";
    document.gettype.other97.focus(); 
  }
  else
  if ( document.gettype.other98.value=="")
  {
    document.gettype.message.value="Select 8th Other9";
    document.gettype.other98.focus(); 
  }
  else
  if ( document.gettype.other99.value=="")
  {
    document.gettype.message.value="Select 9th Other9";
    document.gettype.other99.focus(); 
  }
}
function do10() {
  var mytype;
  div6.style.visibility='hidden';
  div7.style.visibility='hidden';
  div8.style.visibility='hidden';
  div9.style.visibility='hidden';
  div10.style.visibility='visible';
  if ( document.gettype.other101.value=="")
  {
    document.gettype.message.value="Select 1st Other10";
    document.gettype.other101.focus(); 
  }
  else
  if ( document.gettype.other102.value=="")
  {
    document.gettype.message.value="Select 2nd Other10";
    document.gettype.other102.focus(); 
  }
  else
  if ( document.gettype.other103.value=="")
  {
    document.gettype.message.value="Select 3rd Other10";
    document.gettype.other103.focus(); 
  }
  else
  if ( document.gettype.other104.value=="")
  {
    document.gettype.message.value="Select 4th Other10";
    document.gettype.other104.focus(); 
  }
  else
  if ( document.gettype.other105.value=="")
  {
    document.gettype.message.value="Select 5th Other10";
    document.gettype.other105.focus(); 
  }
  else
  if ( document.gettype.other106.value=="")
  {
    document.gettype.message.value="Select 6th Other10";
    document.gettype.other106.focus(); 
  }
  else
  if ( document.gettype.other107.value=="")
  {
    document.gettype.message.value="Select 7th Other10";
    document.gettype.other107.focus(); 
  }
  else
  if ( document.gettype.other108.value=="")
  {
    document.gettype.message.value="Select 8th Other10";
    document.gettype.other108.focus(); 
  }
  else
  if ( document.gettype.other109.value=="")
  {
    document.gettype.message.value="Select 10th Other10";
    document.gettype.other109.focus(); 
  }
}
</script>
<body>
<?php
require_once 'DB.php';
require 'db_access.php';
include "2buttons.php";

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$message = "";
if (isset($_POST['sntype']))
{
	$sntype = $_POST['sntype'];
}
if (isset($_GET['sntype']))
{
	$sntype = $_GET['sntype'];
}
if (isset($_POST['typechange']))
{
	$typechange = $_POST['typechange'];
}
if (isset($_GET['typechange']))
{
	$typechange = $_GET['typechange'];
}
if (isset($_POST['genbrand']))
{
	$genbrand = $_POST['genbrand'];
}
if (isset($_GET['genbrand']))
{
	$genbrand = $_GET['genbrand'];
}
if (isset($_POST['generic1']))
{
	$generic1 = $_POST['generic1'];
}
if (isset($_GET['generic1']))
{
	$generic1 = $_GET['generic1'];
}
if (isset($_POST['generic2']))
{
	$generic2 = $_POST['generic2'];
}
if (isset($_GET['generic2']))
{
	$generic2 = $_GET['generic2'];
}
if (isset($_POST['generic3']))
{
	$generic3 = $_POST['generic3'];
}
if (isset($_GET['generic3']))
{
	$generic3 = $_GET['generic3'];
}
if (isset($_POST['generic4']))
{
	$generic4 = $_POST['generic4'];
}
if (isset($_GET['generic4']))
{
	$generic4 = $_GET['generic4'];
}

if (isset($_POST['brand1']))
{
	$brand1 = $_POST['brand1'];
}
if (isset($_GET['brand1']))
{
	$brand1 = $_GET['brand1'];
}
if (isset($_POST['brand2']))
{
	$brand2 = $_POST['brand2'];
}
if (isset($_GET['brand2']))
{
	$brand2 = $_GET['brand2'];
}
if (isset($_POST['brand3']))
{
	$brand3 = $_POST['brand3'];
}
if (isset($_GET['brand3']))
{
	$brand3 = $_GET['brand3'];
}
if (isset($_POST['brand4']))
{
	$brand4 = $_POST['brand4'];
}
if (isset($_GET['brand4']))
{
	$brand4 = $_GET['brand4'];
}
if (isset($_POST['other6to10']))
{
	$other6to10 = $_POST['other6to10'];
}
if (isset($_GET['other6to10']))
{
	$other6to10 = $_GET['other6to10'];
}
if (isset($_POST['other61']))
{
	$other61 = $_POST['other61'];
}
if (isset($_GET['other61']))
{
	$other61 = $_GET['other61'];
}
if (isset($_POST['other62']))
{
	$other62 = $_POST['other62'];
}
if (isset($_GET['other62']))
{
	$other62 = $_GET['other62'];
}
if (isset($_POST['other63']))
{
	$other63 = $_POST['other63'];
}
if (isset($_GET['other63']))
{
	$other63 = $_GET['other63'];
}
if (isset($_POST['other64']))
{
	$other64 = $_POST['other64'];
}
if (isset($_GET['other64']))
{
	$other64 = $_GET['other64'];
}
if (isset($_POST['other65']))
{
	$other65 = $_POST['other65'];
}
if (isset($_GET['other65']))
{
	$other65 = $_GET['other65'];
}
if (isset($_POST['other66']))
{
	$other66 = $_POST['other66'];
}
if (isset($_GET['other66']))
{
	$other66 = $_GET['other66'];
}
if (isset($_POST['other67']))
{
	$other67 = $_POST['other67'];
}
if (isset($_GET['other67']))
{
	$other67 = $_GET['other67'];
}
if (isset($_POST['other68']))
{
	$other68 = $_POST['other68'];
}
if (isset($_GET['other68']))
{
	$other68 = $_GET['other68'];
}
if (isset($_POST['other69']))
{
	$other69 = $_POST['other69'];
}
if (isset($_GET['other69']))
{
	$other69 = $_GET['other69'];
}

if (isset($_POST['other71']))
{
	$other71 = $_POST['other71'];
}
if (isset($_GET['other71']))
{
	$other71 = $_GET['other71'];
}
if (isset($_POST['other72']))
{
	$other72 = $_POST['other72'];
}
if (isset($_GET['other72']))
{
	$other72 = $_GET['other72'];
}
if (isset($_POST['other73']))
{
	$other73 = $_POST['other73'];
}
if (isset($_GET['other73']))
{
	$other73 = $_GET['other73'];
}
if (isset($_POST['other74']))
{
	$other74 = $_POST['other74'];
}
if (isset($_GET['other74']))
{
	$other74 = $_GET['other74'];
}
if (isset($_POST['other75']))
{
	$other75 = $_POST['other75'];
}
if (isset($_GET['other75']))
{
	$other75 = $_GET['other75'];
}
if (isset($_POST['other76']))
{
	$other76 = $_POST['other76'];
}
if (isset($_GET['other76']))
{
	$other76 = $_GET['other76'];
}
if (isset($_POST['other77']))
{
	$other77 = $_POST['other77'];
}
if (isset($_GET['other77']))
{
	$other77 = $_GET['other77'];
}
if (isset($_POST['other78']))
{
	$other78 = $_POST['other78'];
}
if (isset($_GET['other78']))
{
	$other78 = $_GET['other78'];
}
if (isset($_POST['other79']))
{
	$other79 = $_POST['other79'];
}
if (isset($_GET['other79']))
{
	$other79 = $_GET['other79'];
}

if (isset($_POST['other81']))
{
	$other81 = $_POST['other81'];
}
if (isset($_GET['other81']))
{
	$other81 = $_GET['other81'];
}
if (isset($_POST['other82']))
{
	$other82 = $_POST['other82'];
}
if (isset($_GET['other82']))
{
	$other82 = $_GET['other82'];
}
if (isset($_POST['other83']))
{
	$other83 = $_POST['other83'];
}
if (isset($_GET['other83']))
{
	$other83 = $_GET['other83'];
}
if (isset($_POST['other84']))
{
	$other84 = $_POST['other84'];
}
if (isset($_GET['other84']))
{
	$other84 = $_GET['other84'];
}
if (isset($_POST['other85']))
{
	$other85 = $_POST['other85'];
}
if (isset($_GET['other85']))
{
	$other85 = $_GET['other85'];
}
if (isset($_POST['other86']))
{
	$other86 = $_POST['other86'];
}
if (isset($_GET['other86']))
{
	$other86 = $_GET['other86'];
}
if (isset($_POST['other87']))
{
	$other87 = $_POST['other87'];
}
if (isset($_GET['other87']))
{
	$other87 = $_GET['other87'];
}
if (isset($_POST['other88']))
{
	$other88 = $_POST['other88'];
}
if (isset($_GET['other88']))
{
	$other88 = $_GET['other88'];
}
if (isset($_POST['other89']))
{
	$other89 = $_POST['other89'];
}
if (isset($_GET['other89']))
{
	$other89 = $_GET['other89'];
}

if (isset($_POST['other91']))
{
	$other91 = $_POST['other91'];
}
if (isset($_GET['other91']))
{
	$other91 = $_GET['other91'];
}
if (isset($_POST['other92']))
{
	$other92 = $_POST['other92'];
}
if (isset($_GET['other92']))
{
	$other92 = $_GET['other92'];
}
if (isset($_POST['other93']))
{
	$other93 = $_POST['other93'];
}
if (isset($_GET['other93']))
{
	$other93 = $_GET['other93'];
}
if (isset($_POST['other94']))
{
	$other94 = $_POST['other94'];
}
if (isset($_GET['other94']))
{
	$other94 = $_GET['other94'];
}
if (isset($_POST['other95']))
{
	$other95 = $_POST['other95'];
}
if (isset($_GET['other95']))
{
	$other95 = $_GET['other95'];
}
if (isset($_POST['other96']))
{
	$other96 = $_POST['other96'];
}
if (isset($_GET['other96']))
{
	$other96 = $_GET['other96'];
}
if (isset($_POST['other97']))
{
	$other97 = $_POST['other97'];
}
if (isset($_GET['other97']))
{
	$other97 = $_GET['other97'];
}
if (isset($_POST['other98']))
{
	$other98 = $_POST['other98'];
}
if (isset($_GET['other98']))
{
	$other98 = $_GET['other98'];
}
if (isset($_POST['other99']))
{
	$other99 = $_POST['other99'];
}
if (isset($_GET['other99']))
{
	$other99 = $_GET['other99'];
}

if (isset($_POST['other101']))
{
	$other101 = $_POST['other101'];
}
if (isset($_GET['other101']))
{
	$other101 = $_GET['other101'];
}
if (isset($_POST['other102']))
{
	$other102 = $_POST['other102'];
}
if (isset($_GET['other102']))
{
	$other102 = $_GET['other102'];
}
if (isset($_POST['other103']))
{
	$other103 = $_POST['other103'];
}
if (isset($_GET['other103']))
{
	$other103 = $_GET['other103'];
}
if (isset($_POST['other104']))
{
	$other104 = $_POST['other104'];
}
if (isset($_GET['other104']))
{
	$other104 = $_GET['other104'];
}
if (isset($_POST['other105']))
{
	$other105 = $_POST['other105'];
}
if (isset($_GET['other105']))
{
	$other105 = $_GET['other105'];
}
if (isset($_POST['other106']))
{
	$other106 = $_POST['other106'];
}
if (isset($_GET['other106']))
{
	$other106 = $_GET['other106'];
}
if (isset($_POST['other107']))
{
	$other107 = $_POST['other107'];
}
if (isset($_GET['other107']))
{
	$other107 = $_GET['other107'];
}
if (isset($_POST['other108']))
{
	$other108 = $_POST['other108'];
}
if (isset($_GET['other108']))
{
	$other108 = $_GET['other108'];
}
if (isset($_POST['other109']))
{
	$other109 = $_POST['other109'];
}
if (isset($_GET['other109']))
{
	$other109 = $_GET['other109'];
}

if (isset($_POST['x']))
{
	$image_x = $_POST['x'];
}
if (isset($_GET['x']))
{
	$image_x = $_GET['x'];
}
if (isset($_GET['y']))
{
	$image_y = $_GET['y'];
}
if (isset($_POST['y']))
{
	$image_y = $_POST['y'];
}

function ffputcsv($fp,$data,$delimit=",",$enclosure="\"")
{
	//write to file fp
	//data = array to write
	//delimiter is field seperator
	$string = "";
	$writedelim = FALSE;
	foreach($data as $line)
	{
		//replace quote by 2 quotes
		$dataelem=str_replace("\"","\"\"", $line);
		if ($writedelim) $string .= $delimit;
		$string .= $enclosure . $dataelem . $enclosure;
		$writedelim = TRUE;
	}
	// add newline
	$string .= "\r\n";
	fwrite($fp, $string);
}
//phpinfo();
$wk_restart = "";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//release memory
//ibase_free_result($Result);
if (isset($image_x) and isset($image_y))
{
	if ($image_x > 0 and $image_y > 0)
	{
		//ok can save the details to a file
		$wk_directory = '' ;
		// first get the printer directory to use
		$Query = "SELECT sys_equip.working_directory FROM control join sys_equip on sys_equip.device_id = control.default_despatch_printer ";
		$Query = "SELECT sys_equip.working_directory FROM control join sys_equip on sys_equip.device_id = control.despatch_label_printer ";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Control!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			$wk_directory = $Row[0] ;
		}
		ibase_free_result($Result);
		if ($wk_directory == '')
		{
			// no label printer so use the pack printer
			$Query = "SELECT sys_equip.working_directory FROM control join sys_equip on sys_equip.device_id = control.default_despatch_printer ";
			//echo($Query);
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to Read Control!<BR>\n");
				exit();
			}
			while ( ($Row = ibase_fetch_row($Result)) ) 
			{
				$wk_directory = $Row[0] ;
			}
			ibase_free_result($Result);
		}
		$wk_other6_label = 'Other6' ;
		$wk_other7_label = 'Other7' ;
		$wk_other8_label = 'Other8' ;
		$wk_other9_label = 'Other9' ;
		$wk_other10_label = 'Other10' ;
		// get the labels for ther fields
		$Query = "SELECT field1, field2, field3, field4,field5 from  SSN_TYPE where code = '" . $sntype . "' ";
		//echo($Query);
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to Read Type!<BR>\n");
			exit();
		}
		while ( ($Row = ibase_fetch_row($Result)) ) 
		{
			if ($Row[0] <> "") $wk_other6_label = $Row[0] ;
			if ($Row[1] <> "") $wk_other7_label = $Row[1] ;
			if ($Row[2] <> "") $wk_other8_label = $Row[2] ;
			if ($Row[3] <> "") $wk_other9_label = $Row[3] ;
			if ($Row[4] <> "") $wk_other10_label = $Row[4] ;
		}
		$wk_other6_label .= "|";
		$wk_other7_label .= "|";
		$wk_other8_label .= "|";
		$wk_other9_label .= "|";
		$wk_other10_label .= "|";
		ibase_free_result($Result);
		$wk_filename = $wk_directory . "typelbl.txt";
		//echo $wk_filename;
		$wk_dowrite = "F";
		if (is_writable($wk_filename))
		{
			if (!$wk_handle = fopen($wk_filename, 'a'))
			{
				echo "Cannot Open file ($wk_filename)";
			}
			else
			{
				$wk_dowrite = "T";
			}
		}
		else
		{
			if (!$wk_handle = fopen($wk_filename, 'w'))
			{
				echo "Cannot Open file ($wk_filename)";
			}
			else
			{
				$wk_dowrite = "T";
			}
		}
		if ($wk_dowrite == "T")
		{
			//echo "file opened";
			if (isset($sntype))
			{
				$wk_labels = array("Type|/1" . $sntype);
			}
			else
			{
				$wk_labels = array("Type|/1");
			}
			$wk_genbrand_cnt = 0;
			if (isset($generic1))
			{
				if ($generic1 <> "")
				{
					$wk_labels[] = "Generic|/2" . $generic1;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($generic2))
			{
				if ($generic2 <> "")
				{
					$wk_labels[] = "Generic|/2" . $generic2;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($generic3))
			{
				if ($generic3 <> "")
				{
					$wk_labels[] = "Generic|/2" . $generic3;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($generic4))
			{
				if ($generic4 <> "")
				{
					$wk_labels[] = "Generic|/2" . $generic4;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($brand1))
			{
				if ($brand1 <> "")
				{
					$wk_labels[] = "Brand|/3" . $brand1;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($brand2))
			{
				if ($brand2 <> "")
				{
					$wk_labels[] = "Brand|/3" . $brand2;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($brand3))
			{
				if ($brand3 <> "")
				{
					$wk_labels[] = "Brand|/3" . $brand3;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			if (isset($brand4))
			{
				if ($brand4 <> "")
				{
					$wk_labels[] = "Brand|/3" . $brand4;
					$wk_genbrand_cnt = $wk_genbrand_cnt + 1;
				}
			}
			for ($wk_cnt=$wk_genbrand_cnt; $wk_cnt<8; $wk_cnt++)
			{
				$wk_labels[] = "" ;
			}
			if (isset($other61))
			{
				if ($other61 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other61;
				}
			}
			if (isset($other62))
			{
				if ($other62 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other62;
				}
			}
			if (isset($other63))
			{
				if ($other63 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other63;
				}
			}
			if (isset($other64))
			{
				if ($other64 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other64;
				}
			}
			if (isset($other65))
			{
				if ($other65 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other65;
				}
			}
			if (isset($other66))
			{
				if ($other66 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other66;
				}
			}
			if (isset($other67))
			{
				if ($other67 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other67;
				}
			}
			if (isset($other68))
			{
				if ($other68 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other68;
				}
			}
			if (isset($other69))
			{
				if ($other69 <> "")
				{
					$wk_labels[] = $wk_other6_label . "/F" . $other69;
				}
			}

			if (isset($other71))
			{
				if ($other71 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other71;
				}
			}
			if (isset($other72))
			{
				if ($other72 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other72;
				}
			}
			if (isset($other73))
			{
				if ($other73 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other73;
				}
			}
			if (isset($other74))
			{
				if ($other74 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other74;
				}
			}
			if (isset($other75))
			{
				if ($other75 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other75;
				}
			}
			if (isset($other76))
			{
				if ($other76 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other76;
				}
			}
			if (isset($other77))
			{
				if ($other77 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other77;
				}
			}
			if (isset($other78))
			{
				if ($other78 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other78;
				}
			}
			if (isset($other79))
			{
				if ($other79 <> "")
				{
					$wk_labels[] = $wk_other7_label . "/G" . $other79;
				}
			}

			if (isset($other81))
			{
				if ($other81 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other81;
				}
			}
			if (isset($other82))
			{
				if ($other82 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other82;
				}
			}
			if (isset($other83))
			{
				if ($other83 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other83;
				}
			}
			if (isset($other84))
			{
				if ($other84 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other84;
				}
			}
			if (isset($other85))
			{
				if ($other85 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other85;
				}
			}
			if (isset($other86))
			{
				if ($other86 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other86;
				}
			}
			if (isset($other87))
			{
				if ($other87 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other87;
				}
			}
			if (isset($other88))
			{
				if ($other88 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other88;
				}
			}
			if (isset($other89))
			{
				if ($other89 <> "")
				{
					$wk_labels[] = $wk_other8_label . "/H" . $other89;
				}
			}

			if (isset($other91))
			{
				if ($other91 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other91;
				}
			}
			if (isset($other92))
			{
				if ($other92 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other92;
				}
			}
			if (isset($other93))
			{
				if ($other93 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other93;
				}
			}
			if (isset($other94))
			{
				if ($other94 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other94;
				}
			}
			if (isset($other95))
			{
				if ($other95 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other95;
				}
			}
			if (isset($other96))
			{
				if ($other96 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other96;
				}
			}
			if (isset($other97))
			{
				if ($other97 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other97;
				}
			}
			if (isset($other98))
			{
				if ($other98 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other98;
				}
			}
			if (isset($other99))
			{
				if ($other99 <> "")
				{
					$wk_labels[] = $wk_other9_label . "/I" . $other99;
				}
			}

			if (isset($other101))
			{
				if ($other101 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other101;
				}
			}
			if (isset($other102))
			{
				if ($other102 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other102;
				}
			}
			if (isset($other103))
			{
				if ($other103 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other103;
				}
			}
			if (isset($other104))
			{
				if ($other104 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other104;
				}
			}
			if (isset($other105))
			{
				if ($other105 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other105;
				}
			}
			if (isset($other106))
			{
				if ($other106 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other106;
				}
			}
			if (isset($other107))
			{
				if ($other107 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other107;
				}
			}
			if (isset($other108))
			{
				if ($other108 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other108;
				}
			}
			if (isset($other109))
			{
				if ($other109 <> "")
				{
					$wk_labels[] = $wk_other10_label . "/J" . $other109;
				}
			}
			//print_r($wk_labels);
			ffputcsv($wk_handle, $wk_labels);
			fclose($wk_handle);
			$message = "Data Saved";
		}
	}
}

echo("<FORM action=\"labeltype.php\" method=\"post\" name=gettype onsubmit=\"return processEdit();\" >");
echo("<INPUT type=\"hidden\" name=\"typechange\" value=\"0\" >");

echo("<INPUT type=\"text\" name=\"message\" readonly size=\"50\" class=\"message\"><br>\n");
echo("<div id=\"col2\">");
echo("Type:<br>");
echo("<br>");
echo("</div>\n");
echo("<div id=\"col3\">");
//echo('<table border="0">');
//echo("<tr><td>");
$Query = "SELECT code, description FROM ssn_type ORDER BY description ";
//echo("Type:</td><td><SELECT name=\"sntype\" size=\"1\" class=\"sel50\" onchange=\"document.gettype.typechange.value=1\">\n");
echo("<SELECT name=\"sntype\" size=\"1\" class=\"sel50\" onchange=\"document.gettype.typechange.value=1;document.gettype.submit()\" >\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Type!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	{
		echo( "<option value=\"" . $Row[0] .  "\"");
		if (isset($sntype))
		{
			if ($sntype == $Row[0])
			{
				echo(" selected ");
				$wk_selected = "Y";
			}
		}
		//echo( "<option value=\"" . $Row[0] .  "\">$Row[1]\n");
		echo( ">$Row[1]\n");
	}
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
//echo ("</SELECT></td></tr>");
echo ("</SELECT>");
ibase_free_result($Result);
//echo "<br><input type=\"radio\" name=\"genbrand\" value=\"GENERIC\" onclick=\"dogeneric();\">Generic\n";
echo "<br><input type=\"radio\" name=\"genbrand\" value=\"GENERIC\" onclick=\"dogeneric();\"";
if (isset($genbrand))
{
	if ($genbrand == "GENERIC")
	{
		echo " checked ";
		$wk_restart .= "dogeneric();";
	}
}
echo ">Generic\n";
//echo "<input type=\"radio\" name=\"genbrand\" value=\"BRAND\" onclick=\"dobrand();\">Brand\n";
echo "<input type=\"radio\" name=\"genbrand\" value=\"BRAND\" onclick=\"dobrand();\"";
if (isset($genbrand))
{
	if ($genbrand == "BRAND")
	{
		echo " checked ";
		$wk_restart .= "dobrand();";
	}
}
echo ">Brand\n";
//echo ("</table>");
echo("</div>\n");
echo("<div id=\"divgeneric\" style=\"visibility:hidden\">");
echo("<label for=\"generic1\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic1\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($generic1))
	{
		if($generic1 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"generic2\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic2\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($generic2))
	{
		if($generic2 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"generic3\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic3\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($generic3))
	{
		if($generic3 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"generic4\">");
$Query = "SELECT code, description FROM generic ORDER BY description ";
echo("<SELECT name=\"generic4\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Generic!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($generic4))
	{
		if($generic4 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");
echo("<div id=\"divbrand\" style=\"visibility:hidden\">");
echo("<label for=\"brand1\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand1\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($brand1))
	{
		if($brand1 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"brand2\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand2\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($brand2))
	{
		if($brand2 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"brand3\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand3\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($brand3))
	{
		if($brand3 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"brand4\">");
$Query = "SELECT code, description FROM brand ORDER BY description ";
echo("<SELECT name=\"brand4\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Brand!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[1]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($brand4))
	{
		if($brand4 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[1]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");
echo("<div id=\"divradio2\" >");
//echo "<br><input type=\"radio\" name=\"other6to10\" value=\"6\" onclick=\"do6();\">Other6\n";
echo "<br><input type=\"radio\" name=\"other6to10\" value=\"6\" onclick=\"do6();\"";
if (isset($other6to10))
{
	if ($other6to10 == "6")
	{
		echo " checked ";
		$wk_restart .= "do6();";
	}
}
echo ">Other6\n";
//echo "<input type=\"radio\" name=\"other6to10\" value=\"7\" onclick=\"do7();\">Other7\n";
echo "<input type=\"radio\" name=\"other6to10\" value=\"7\" onclick=\"do7();\"";
if (isset($other6to10))
{
	if ($other6to10 == "7")
	{
		echo " checked ";
		$wk_restart .= "do7();";
	}
}
echo ">Other7\n";
//echo "<input type=\"radio\" name=\"other6to10\" value=\"8\" onclick=\"do8();\">Other8\n";
echo "<input type=\"radio\" name=\"other6to10\" value=\"8\" onclick=\"do8();\"";
if (isset($other6to10))
{
	if ($other6to10 == "8")
	{
		echo " checked ";
		$wk_restart .= "do8();";
	}
}
echo ">Other8\n";
//echo "<input type=\"radio\" name=\"other6to10\" value=\"9\" onclick=\"do9();\">Other9\n";
echo "<input type=\"radio\" name=\"other6to10\" value=\"9\" onclick=\"do9();\"";
if (isset($other6to10))
{
	if ($other6to10 == "9")
	{
		echo " checked ";
		$wk_restart .= "do9();";
	}
}
echo ">Other9\n";
//echo "<input type=\"radio\" name=\"other6to10\" value=\"10\" onclick=\"do10();\">Other10\n";
echo "<input type=\"radio\" name=\"other6to10\" value=\"10\" onclick=\"do10();\"";
if (isset($other6to10))
{
	if ($other6to10 == "10")
	{
		echo " checked ";
		$wk_restart .= "do10();";
	}
}
echo ">Other10\n";
echo("</div>\n");
echo("<div id=\"div6\" style=\"visibility:hidden\">");
echo("<label for=\"other61\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other61\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other61))
	{
		if($other61 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other62\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other62\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other62))
	{
		if($other62 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other63\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other63\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other63))
	{
		if($other63 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other64\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other64\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other64))
	{
		if($other64 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other65\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other65\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other65))
	{
		if($other65 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other66\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other66\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other66))
	{
		if($other66 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other67\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other67\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other67))
	{
		if($other67 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other68\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other68\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other68))
	{
		if($other68 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other69\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '1' ORDER BY description ";
echo("<SELECT name=\"other69\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other69))
	{
		if($other69 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");

echo("<div id=\"div7\" style=\"visibility:hidden\">");
echo("<label for=\"other71\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other71\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other71))
	{
		if($other71 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other72\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other72\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other72))
	{
		if($other72 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other73\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other73\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other73))
	{
		if($other73 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other74\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other74\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other74))
	{
		if($other74 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other75\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other75\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other75))
	{
		if($other75 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other76\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other76\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other76))
	{
		if($other76 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other77\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other77\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other77))
	{
		if($other77 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other78\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other78\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other78))
	{
		if($other78 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other79\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '2' ORDER BY description ";
echo("<SELECT name=\"other79\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other79))
	{
		if($other79 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");

echo("<div id=\"div8\" style=\"visibility:hidden\">");
echo("<label for=\"other81\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other81\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other81))
	{
		if($other81 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other82\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other82\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other82))
	{
		if($other82 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other83\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other83\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other83))
	{
		if($other83 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other84\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other84\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other84))
	{
		if($other84 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other85\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other85\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other85))
	{
		if($other85 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other86\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other86\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other86))
	{
		if($other86 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other87\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other87\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other87))
	{
		if($other87 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other88\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other88\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other88))
	{
		if($other88 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other89\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '3' ORDER BY description ";
echo("<SELECT name=\"other89\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other89))
	{
		if($other89 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");

echo("<div id=\"div9\" style=\"visibility:hidden\">");
echo("<label for=\"other91\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other91\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other91))
	{
		if($other91 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other92\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other92\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other92))
	{
		if($other92 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other93\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other93\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other93))
	{
		if($other93 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other94\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other94\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other94))
	{
		if($other94 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other95\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other95\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other95))
	{
		if($other95 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other96\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other96\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other96))
	{
		if($other96 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other97\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other97\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other97))
	{
		if($other97 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other98\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other98\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other98))
	{
		if($other98 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other99\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '4' ORDER BY description ";
echo("<SELECT name=\"other99\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other99))
	{
		if($other99 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("</div>\n");

echo("<div id=\"div10\" style=\"visibility:hidden\">");
echo("<label for=\"other101\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other101\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other101))
	{
		if($other101 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other102\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other102\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other102))
	{
		if($other102 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other103\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other103\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other103))
	{
		if($other103 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other104\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other104\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other104))
	{
		if($other104 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other105\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other105\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other105))
	{
		if($other105 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other106\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other106\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other106))
	{
		if($other106 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other107\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other107\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other107))
	{
		if($other107 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other108\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other108\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other108))
	{
		if($other108 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
ibase_free_result($Result);
echo("<label for=\"other109\">");
$Query = "SELECT description FROM product_description where type_code = '" . $sntype . "' and field_code = '5' ORDER BY description ";
echo("<SELECT name=\"other109\" size=\"1\" maxlength=\"50\" class=\"sel50\">\n");
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read Product Description!<BR>\n");
	exit();
}
$wk_selected = "N";
while ( ($Row = ibase_fetch_row($Result)) ) {
	//if (isset($current_type))
	//echo( "<option value=\"" . $Row[0] . "\">$Row[0]\n");
	echo( "<option value=\"" . $Row[0] . "\"");
	if(isset($other109))
	{
		if($other109 == $Row[0])
		{
			echo(" selected ");
			$wk_selected = "Y";
		}
	}
	echo( ">$Row[0]\n");
}
{
	echo( "<option value=\"\" ");
	if ($wk_selected == "N")
	{
		echo( " selected");
	}
	echo( ">NO VALUE\n");
}
echo ("</select><br>");
echo("</div>\n");
echo("<div id=\"col4\">");
echo('<table border="0">');
//release memory
ibase_free_result($Result);
//commit
ibase_commit($dbTran);
echo ("<BR>");
{
	// html 4.0 browser
	//echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">");
	//whm2buttons("Accept","./despatch_menu.php");
	whm2buttons('Accept',"./desc_menu.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
}
echo("</div>\n");
echo("<script>");
if ($wk_restart <> "")
{
	echo $wk_restart;
}
if (isset($message))
{
	echo("document.gettype.message.value=\"" . $message . " Select Type" . "\";");
	echo('document.gettype.sntype.focus();');
}
else
{
	echo('document.gettype.message.value="Select Type";');
	echo('document.gettype.sntype.focus();');
}
?>
</script>
</body>
</html>
