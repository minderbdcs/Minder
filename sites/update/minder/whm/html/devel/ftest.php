<?php
require_once "DB.php";
require "db_access.php";
$Query = "select * from options";
$wk_format = "html";
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


	echo '</html>';
