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
	if (!isset($thisdir))
	{
		$Query = "select working_directory from sys_equip where device_id='" . $thisprinter . "' ";
		if (!($Result = ibase_query($Link, $Query)))
		{
			echo("Unable to query devices!<BR>\n");
			exit();
		}
		while (($Row = ibase_fetch_row($Result))) 
		{
			$thisdir = $Row[0];
			$thisdir2 = substr($thisdir, 
				0, 
				strlen($thisdir) - 1);
			$thisdir = $thisdir2;
		}
		//release memory
		ibase_free_result($Result);
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
		$lastren = rename($thisdir."/".$thisfile,
			          $thisdir."/".substr($thisfile,0,$filelen - 3)."txt");
	}
	echo("<TABLE BORDER=\"1\">\n");
	// create a header
	echo("<TR>\n");
	echo("<TH>Filename</TH>\n");
	echo("<TH>Last Mod Date</TH>\n");
	echo("</TR>\n");

	//open dir
	$mydir = opendir($thisdir);

	$rcount= 0;
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
	closedir($mydir);

	echo("</TABLE>\n");
	echo("<FORM action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getprinter>\n");
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
	echo("<INPUT type=\"submit\" name=\"dir2\" value=\"Show Directory\">\n");
	echo("</FORM>\n");
	//commit
	ibase_commit($dbTran);
	//close
	ibase_close($Link);
?>

