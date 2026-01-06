<?php
require_once "DB.php";
require "db_access.php";
if (isset($_POST['Query']))
{
	$Query = $_POST['Query'];
	$Query = urldecode($Query);
	$Query = stripslashes($Query);
}
if (isset($_GET['Query']))
{
	$Query = $_GET['Query'];
	$Query = urldecode($Query);
	$Query = stripslashes($Query);
}
if (isset($_POST['ExportAs']))
{
	$ExportAs = $_POST['ExportAs'];
}
if (isset($_GET['ExportAs']))
{
	$ExportAs = $_GET['ExportAs'];
}
$wk_format = "html";
if (isset($ExportAs))
{
	if ($ExportAs == "CSV")
	{
		header("Content-type: text/csv");
		//header('Content-Disposition', 'attachment; filename="export.csv"');
		$filename = "dbquery.csv";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
		$wk_format = "csv";
	}
}
if ($wk_format == "html")
{
	echo("<html>\n");
	echo("<head>\n");
	include "viewport.php";
	echo("<title>General Query of Database</title>\n");
	include "nopad.css";
	echo("</head>\n");
	echo("<body>\n");
	include "2buttons.php";
}
//if (!($Link = ibase_connect($DBName2, $User, $Password)))
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	echo("Unable to Connect!<BR>\n");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
// Set the variables for the database access:

if (isset($Query))
{
	if (!($Result = ibase_query($Link, $Query)))
	{
		echo("Unable to query tables!<BR>\n");
		exit();
	}

	if ($wk_format == "html")
	{
		echo '<table align="center" cellpadding="2" cellspacing="2" border="1">';
	
		echo '<tr>';
		for ($i=0; $i<ibase_num_fields($Result); $i++)
		{
			$info = ibase_field_info($Result, $i);
			if ($info['alias'] <> '')
			{
				echo("<th>{$info['alias']}</th>\n");
			}
			else
			{
				echo("<th>{$info['name']}</th>\n");
			}
		}
		echo '</tr>';
	}
	if ($wk_format == "csv")
	{
		for ($i=0; $i<ibase_num_fields($Result); $i++)
		{
			$info = ibase_field_info($Result, $i);
			if ($i > 0)
			{
				echo ",";
			}
			if ($info['alias'] <> '')
			{
				echo("\"{$info['alias']}\"");
			}
			else
			{
				echo("\"{$info['name']}\"");
			}
		}
		echo "\n";
	}
	while ( ($Row = ibase_fetch_row($Result)))
	{
		if ($wk_format == "html")
		{
	 		echo '<tr>';
			foreach ($Row as $Key => $Value) 
			{
				echo "<td>$Value</td>";
			}
			echo '</tr>';
		}
		if ($wk_format == "csv")
		{
			foreach ($Row as $Key => $Value) 
			{
				if ($Key > 0)
				{
					echo ",";
				}
				echo "\"$Value\"";
			}
			echo "\n";
		}
	}
	if ($wk_format == "html")
	{
		echo '</table><BR /><HR />';
	}
	
	//release memory
	//$Result->free();
	ibase_free_result($Result);
	
	//commit
	//$Link->commit();
	ibase_commit($dbTran);
}

//close
//$Link->disconnect();
ibase_close($Link);

//close
//$Link->disconnect();


if ($wk_format == "html")
{
	//<input type="submit" name="submit" value="Submit">
	echo 'This page allows you to query a database. Enter your Query in proper SQL form in the text box below, then click Accept.<br />';
	echo '<form action="dbquery.php" method="post" name="dbquery"> ';
	
	echo '<textarea name="Query" rows="5" cols="60">';
	if (isset($Query))
	{
		echo $Query;
	}
	echo '</textarea><br>';
	echo '<table align="left"  border="0">';
	whm2buttons('Accept', 'util_Menu.php',"N","Back_50x100.gif","Back","accept.gif","N");
	{
		// html 4.0 browser
		$backto = "dbquery.csv";
		$backto = "dbquery.php";
		echo ("<td>");
		echo("<form action=\"" . $backto . "\" method=\"post\" name=exportcsv onsubmit=\"document.forms.exportcsv.Query.value=document.forms.dbquery.Query.value;\">\n");
		echo("<input type=\"hidden\" name=\"Query\">");  
		echo("<input type=\"hidden\" name=\"ExportAs\" value=\"CSV\">");  
		echo("<input type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/button.php?text=Export+CSV" alt="ExportAsCSV">');
		echo("</form>");
		echo ("</td>");
		echo ("</tr>");
		echo ("</table>");
	}
	//echo '</form>';
	echo '</body>';
	echo("<script type=\"text/javascript\">\n");
	echo 'document.dbquery.Query.focus();';
	echo '</script>';
	echo '</html>';
}
?>
