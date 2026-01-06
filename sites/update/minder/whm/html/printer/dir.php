<?php
session_start();
?>
<html>
<head>
<?php
 include "viewport.php";
?>
  <title>Reprint Files</title>
<style type="text/css">
body {
     font-family: sans-serif;
     font-size: 13px;
     border: 0; padding: 0; margin: 0;
}
form, div {
    border: 0; padding:0 ; margin: 0;
}
table {
    border: 0; padding: 0; margin: 0;
}
.odd   {
background-color  : greenyellow;
}
.id   {
width :15%;
}
.desc {
width :200%;
}
.date {
width :13%
}
.date2 {
width :90%
}
.status {
width :7%
}
.status2 {
width :40%
}
.type   {
width :10%
}
.type2 {
width :90%
}
</style>
</head>
<body>
<?php
	// print directory to a table
	include "DB.php";
	include "db_access.php";
	//if (!($Link = ibase_connect($DBName2, $User, $Password)))
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		echo("Unable to Connect!<BR>\n");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

	include "logme.php";

/*
  WK_DO_SYSLABEL = '';
  SELECT GENERATE_LABEL_TEXT 
  FROM CONTROL
  INTO :WK_DO_SYSLABEL;
  IF (WK_DO_SYSLABEL  = 'F')
  BEGIN
  	use print_requests table for print jobs 
		just update the print request to NP to reprint
  END
  ELSE
  BEGIN
	if system is using bartender have to search for print files in the folders
		then copy/rename to reprint
		this is the current mode of operation for a non linux OS
	if system is creating flat files which are sent to the device on port 9100
		use Printer send command to reprint
		this is the current mode of operation for a linux OS
  END
*/
/**
 * getPrinterIp
 *
 * @param $Link
 * @param string $printerId
 * @return string or null
 */
function getPrinterIp($Link, $printerId) {
    $result = '';
    $sql = 'SELECT IP_ADDRESS FROM SYS_EQUIP WHERE DEVICE_ID = ?';
    $q = ibase_prepare($Link, $sql);
    if ($q) {
        $r = ibase_execute($q, $printerId);
        if ($r) {
            $d = ibase_fetch_row($r);
            if ($d) {
                $result = $d[0];
            }
            ibase_free_result($r);
        }
        ibase_free_query($q);
    }
    return $result;
}

	$wk_do_syslabel = "T" ;
	$Query = "select generate_label_text from control ";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query control!<BR>\n");
		exit();
	}
	while (($Row = ibase_fetch_row($Result))) {
		$wk_do_syslabel = $Row[0] ;
	}

	//release memory
	ibase_free_result($Result);
	if (is_null($wk_do_syslabel))
	{
		$wk_do_syslabel = 'T';
	} 


	if (isset($_POST['printer'])) 
	{
		$thisprinter = $_POST['printer'];
	}
	if (isset($_GET['printer'])) 
	{
		$thisprinter = $_GET['printer'];
	}
	if (!isset($thisprinter)) 
	{
		$thisprinter = "PA";
	}
	if (isset($_POST['directory'])) 
	{
		$thisdir = $_POST['directory'];
	}
	if (isset($_GET['directory'])) 
	{
		$thisdir = $_GET['directory'];
	}
	{
		$Query = "select working_directory from sys_equip where device_id='" . $thisprinter . "' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query devices!<BR>\n");
			exit();
		}
		while (($Row = ibase_fetch_row($Result))) 
		{
			$initdir = $Row[0];
			$initdir2 = substr($initdir, 
				0, 
				strlen($initdir) - 1);
			$initdir = $initdir2;
		}
		//release memory
		ibase_free_result($Result);
	}
	if (!isset($thisdir))
	{
		$thisdir = $initdir;
	}

  	echo("<H4 ALIGN=\"LEFT\">Printer Files for " . $thisprinter . "</H4>\n");
	if (isset($_POST['dir'])) 
	{
		$thisdir3 = $_POST['dir'];
	}
	if (isset($_GET['dir'])) 
	{
		$thisdir3 = $_GET['dir'];
	}
	if (isset($thisdir3)) 
	{
		$thisdir .= "/" . $thisdir3;
	}
	if (isset($_POST['file'])) 
	{
		$thisfile = $_POST['file'];
	}
	if (isset($_GET['file'])) 
	{
		$thisfile = $_GET['file'];
	}
	if (isset($thisfile)) 
	{
		$filelen = strlen($thisfile);
		//echo($thisdir."/".$thisfile);
		//echo("$filelen");
		//echo($thisdir."/".substr($thisfile,0,$filelen - 3)."txt");
		$testdir = addslashes($initdir);
		//echo("[".$thisdir."][".$initdir."][".$testdir."]");
		if ($wk_do_syslabel == "F")
		{
			// update the request to be NP status so that its reprinted
			$Query = "update print_requests set request_status = 'NP' where message_id = '" . $thisfile . "' ";
			if (!($Result = ibase_query($Link, $Query)))
			{
				echo("Unable to update print_request!<BR>\n");
				//exit();
			}
		}
		else 
		{
			if ($system_type == "LINUX")
			{
				if (isset($_COOKIE['LoginUser']))
				{
					list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
				}
				//include 'Printer.php';
				require_once 'Printer.php';
			        $printerIp = getPrinterIp($Link, $thisprinter );
			        if ($printerIp == '') {
			            $errors['printer_id'] = 'No IP address for printer';
			        }
			        $p = new Printer($printerIp);
				$wk_file = $thisdir."/".$thisfile;
			        {
					if ($wk_file <> '')
					{
						logme($Link, $tran_user, $tran_device, "reprint print file " . $wk_file);
			                	//$save = fopen($wk_file , 'r');
			                        $tpl = file_get_contents($wk_file);
						// send it to the printer
			                        $p->send($tpl );
					}
			        }
			}
			else
			{
				if ($thisdir == $testdir)
				{
					$lastren = rename($thisdir."/".$thisfile,
				        	  $thisdir."/".substr($thisfile,0,$filelen - 3)."txt");
				}
				else
				{
					copy($thisdir."/".$thisfile,
				        	  $initdir."/".substr($thisfile,0,$filelen - 3)."txt");
				}
			}
		}
	}
	echo("<table border=\"1\" width=\"100%\">\n");
	// create a header
	echo("<tr>");
	echo("<th>Filename</th>");
	echo("<th>Last Mod Date</th>");
	if ($wk_do_syslabel == "F")
	{
		echo("<th>Type</th>");
		echo("<th>Status</th>");
		echo("<th>Description</th>");
	}
	echo("</tr>");
	echo("<tbody>");

	//open dir
	//echo $thisdir;
	$dirdata = Array();
	$dirdir = Array();
	$rcount= 0;
	if ($wk_do_syslabel == "T")
	{
		$mydir = opendir($thisdir);

		while($entryName = readdir($mydir))
		{
			$rcount= $rcount + 1;
			if ($rcount > 2)
			{
				if (is_dir($thisdir."/".$entryName))
				{
					$dirdir[] = $entryName;
				}
				else
				{
					$dirdata[] = $entryName;
				}
			}
		}
		closedir($mydir);
	}

	sort($dirdir);
	sort($dirdata);
	$rcount= 0;
	// do the dirs
	foreach ($dirdir as $Key_results => $entryName) 
	{
		$rcount= $rcount + 1;
		if ($rcount == 1)
		{
			echo("<tr>");
		}
			//echo("<TD>$entryName</TD>");
			echo("<td>");
			echo("<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getfile>");
			echo("<input type=\"hidden\" name=\"printer\" value=\"$thisprinter\">");
			echo("<input type=\"hidden\" name=\"directory\" value=\"$thisdir\">");
			{
				echo("<input type=\"submit\" name=\"dir\" value=\"$entryName\">");
			}
			echo("</form>");
			echo("</td>");
/*
			echo("<td>");
			$lastmod = filemtime($thisdir."/".$entryName);
			if ($lastmod == FALSE)
			{
				echo("Not Available");
			}
			else
			{
				echo(date("l F d, Y H:i:s",$lastmod)); 
			}
			echo("</td>");
			echo("<td>");
			echo("</td>");
*/
		if ($rcount == 3)
		{
			echo("</tr>");
			$rcount = 0;
		}
	}
	if ($rcount > 0)
	{
			echo("<td>");
			echo("</td>");
			$rcount--;
	}
	if ($rcount > 0)
	{
			echo("<td>");
			echo("</td>");
			$rcount--;
	}
	//do the files
	//while($entryName = readdir($mydir))
	foreach ($dirdata as $Key_results => $entryName) 
	{
		{
			echo("<tr>");
			//echo("<TD>$entryName</TD>");
			echo("<td>");
			echo("<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getfile>");
			echo("<input type=\"hidden\" name=\"printer\" value=\"$thisprinter\">");
			echo("<input type=\"hidden\" name=\"directory\" value=\"$thisdir\">");
			if (is_dir($thisdir."/".$entryName))
			{
				echo("<input type=\"submit\" name=\"dir\" value=\"$entryName\">");
			}
			else
			{
				echo("<input type=\"submit\" name=\"file\" value=\"$entryName\">");
			}
			echo("</form>");
			echo("</td>");
			echo("<td>");
			$lastmod = filemtime($thisdir."/".$entryName);
			if ($lastmod == FALSE)
			{
				echo("Not Available");
			}
			else
			{
				echo(date("l F d, Y H:i:s",$lastmod)); 
			}
			echo("</td>");
			echo("<td>");
			if (is_file($thisdir."/".$entryName) and
			    is_readable($thisdir."/".$entryName))
			{
				if (!($myfile = fopen($thisdir."/".$entryName,"r")))
				{
					echo("cannot open file");
				}
				else
				{
					if (!feof($myfile))
					{
						$myline = fgetss($myfile,40);
					}
					else
					{
						$myline = "No Data";
					}
					echo($myline);
					fclose($myfile);
				}
			}
			echo("</td>");
			echo("</tr>");
		}
	}
	if ($wk_do_syslabel == "F")
	{
		$Query = "select message_id, prn_type,prn_data,prn_date,base_file_name,request_status from print_requests  where device_id='" . $thisprinter . "' order by prn_date desc ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query print_requests!<BR>\n");
			//exit();
		}
		while (($Row = ibase_fetch_row($Result))) 
		{
			$initdir = $Row[0];
			$rcount= $rcount + 1;
			//echo("<TD>$entryName</TD>");
			if ($rcount %2) 
			{
				echo("<tr>");
			} else {
				echo("<tr class=\"odd\">");
			}
			echo("<td class=\"id\">");
			echo("<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getfile>");
			echo("<input type=\"hidden\" name=\"printer\" value=\"$thisprinter\">");
			echo("<input type=\"hidden\" name=\"directory\" value=\"$thisdir\">");
			echo("<input type=\"submit\" name=\"file\" value=\"$Row[0]\" class=\"id2\">");
			echo("</td><td class=\"date\">");
			echo("<input type=\"text\" name=\"date\" value=\"$Row[3]\" class=\"date2\">");
			echo("</td><td class=\"type\">");
			echo("<input type=\"text\" name=\"filetype\" value=\"$Row[1]\" class=\"type2\">");
			echo("</td><td class=\"status\" >");
			echo("<input type=\"text\" name=\"status\" value=\"$Row[5]\" class=\"status2\">");
			echo("</td><td >");
			echo("<input type=\"text\" name=\"desc\" value=\"$Row[2]\" class=\"desc\">");
			echo("</td>");

			echo("</form>");
			echo("</tr>");
		}
		//release memory
		ibase_free_result($Result);
	}

/*
	while($entryName = readdir($mydir))
	{
		$rcount= $rcount + 1;
		if ($rcount > 2)
		{
			echo("<TR>");
			//echo("<TD>$entryName</TD>");
			echo("<TD>");
			echo("<FORM action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getfile>\n");
			echo("<INPUT type=\"hidden\" name=\"printer\" value=\"$thisprinter\">\n");
			echo("<INPUT type=\"hidden\" name=\"directory\" value=\"$thisdir\">\n");
			if (is_dir($thisdir."/".$entryName))
			{
				echo("<INPUT type=\"submit\" name=\"dir\" value=\"$entryName\">\n");
			}
			else
			{
				echo("<INPUT type=\"submit\" name=\"file\" value=\"$entryName\">\n");
			}
			echo("</FORM>\n");
			echo("</TD>");
			echo("<TD>");
			$lastmod = filemtime($thisdir."/".$entryName);
			if ($lastmod == FALSE)
			{
				echo("Not Available");
			}
			else
			{
				echo(date("l F d, Y H:i:s",$lastmod)); 
			}
			echo("</TD>");
			echo("<TD>");
			if (is_file($thisdir."/".$entryName) and
			    is_readable($thisdir."/".$entryName))
			{
				if (!($myfile = fopen($thisdir."/".$entryName,"r")))
				{
					echo("cannot open file");
				}
				else
				{
					if (!feof($myfile))
					{
						$myline = fgetss($myfile,40);
					}
					else
					{
						$myline = "No Data";
					}
					echo($myline);
					fclose($myfile);
				}
			}
			echo("</TD>");
			echo("</TR>\n");
		}
	}
*/
	echo("</tbody>");
	echo("</table>");
	echo("<table border=\"0\">\n");
	echo("<tbody>");
	echo("<tr>");
	echo("<td>");
	echo("<form action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getprinter>\n");
	echo("<SELECT name=\"printer\"size=\"3\">\n");
	$Query = "select device_id from sys_equip where device_type='PR' order by device_id";
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query devices!<BR>\n");
		exit();
	}
	while (($Row = ibase_fetch_row($Result))) {
		echo("<OPTION value=\"" . $Row[0] . "\"");
		if ($thisprinter == $Row[0])
		{
			echo(" selected=\"Y\"");
		}
		echo(">" . $Row[0] . "\n");
	}

	//release memory
	ibase_free_result($Result);

	echo("</SELECT>\n");
	echo("<input type=\"submit\" name=\"dir2\" value=\"Show Directory\">\n");
	echo("</form>\n");
	echo("</td>");
	echo("<td>");
	echo("<form action=\"print_Menu.php\" method=\"post\" name=getback>\n");
	echo("<input type=\"submit\" name=\"dir3\" value=\"Back\">\n");
	echo("</form>\n");
	echo("</td>");
	echo("</tr>");
	echo("</tbody>");
	echo("</table>");
	//commit
	ibase_commit($dbTran);
	//close
	ibase_close($Link);
?>
</body>
</html>
