<?php
	// print directory to a table
	include "DB.php";
	include "db_access.php";
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
	if ($thisprinter == "PA")
	{
		$thisdir = $printerPA;
	}
	if ($thisprinter == "PB")
	{
		$thisdir = $printerPB;
	}
	if ($thisprinter == "PC")
	{
		$thisdir = $printerPC;
	}
  	print("<H4 ALIGN=\"LEFT\">Printer Files for " . $thisprinter . "</H4>\n");
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
		//print($thisdir."/".$thisfile);
		//print("$filelen");
		//print($thisdir."/".substr($thisfile,0,$filelen - 3)."txt");
		$lastren = rename($thisdir."/".$thisfile,
			          $thisdir."/".substr($thisfile,0,$filelen - 3)."txt");
	}
	print("<TABLE BORDER=\"1\">\n");
	// create a header
	print("<TR>\n");
	print("<TH>Filename</TH>\n");
	print("<TH>Last Mod Date</TH>\n");
	print("</TR>\n");

	//open dir
	$mydir = opendir($thisdir);

	$rcount= 0;
	while($entryName = readdir($mydir))
	{
		$rcount= $rcount + 1;
		if ($rcount > 2)
		{
			print("<TR>");
			//print("<TD>$entryName</TD>");
			print("<TD>");
			print("<FORM action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getfile>\n");
			print("<INPUT type=\"hidden\" name=\"printer\" value=\"$thisprinter\">\n");
			print("<INPUT type=\"submit\" name=\"file\" value=\"$entryName\">\n");
			print("</FORM>\n");
			print("</TD>");
			print("<TD>");
			$lastmod = filemtime($thisdir."/".$entryName);
			if ($lastmod == FALSE)
			{
				print("Not Available");
			}
			else
			{
				print(date("l F d, Y H:i:s",$lastmod)); 
			}
			print("</TD>");
			print("<TD>");
			if (!($myfile = fopen($thisdir."/".$entryName,"r")))
			{
				print("cannot open file");
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
				print ($myline);
				fclose($myfile);
			}

			print("</TD>");
			print("</TR>\n");
		}
	}

	closedir($mydir);

	print("</TABLE>\n");
	print("<FORM action=\"" . $_SERVER['PHP_SELF'] . "\" method=\"post\" name=getprinter>\n");
	print("<SELECT name=\"printer\"size=\"3\">\n");
	print("<OPTION value=\"PA\"");
	if ($thisprinter == "PA")
	{
		print(" selected=\"Y\"");
	}
	print(">PA\n");
	print("<OPTION value=\"PB\"");
	if ($thisprinter == "PB")
	{
		print(" selected=\"Y\"");
	}
	print(">PB\n");
	print("<OPTION value=\"PC\"");
	if ($thisprinter == "PC")
	{
		print(" selected=\"Y\"");
	}
	print(">PC\n");
	print("</SELECT>\n");
	print("<INPUT type=\"submit\" name=\"dir\" value=\"Show Directory\">\n");
	print("</FORM>\n");
?>

