<?php
$init = parse_ini_file('/etc/h2csv/init.ini', True);
//var_dump($init);
$filename = $init['outfile'] ; 
$wkExport = "HTML";
if (isset($_GET['export'])) {
	if ($_GET['export'] == "CSV") {
		header("Content-type: text/csv");
		//header('Content-Disposition', 'attachment; filename="export.csv"');
		$wkExport = "CSV";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
	if ($_GET['export'] == "XLS") {
 		function xlsBOF()
		{
			echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
			return;
		}
		function xlsWriteLabel($Row, $Col, $Value )
		{
			$L = strlen($Value);
			echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
			echo $Value;
			return;
		}
 		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=list.xls");
		header("Content-Transfer-Encoding: binary ");
		$wkExport = "XLS";

 		xlsBOF();
		xlsWriteLabel(0,0,"Location");
		xlsWriteLabel(0,1,"Code");
		xlsWriteLabel(0,2,"Comment");

	}
} 
if ($wkExport == "HTML") {
	/*
	No cache!!
	*/
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	// always modified
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	/*
	End of No cache
	*/
}

if ($wkExport == "HTML") {
	echo("<html>
	 <head>
	  <title>Viewer</title>\n");
	include "2buttons.php";
	include "repage.csv.php";
	//echo '<link rel=stylesheet type="text/css" href="ViewOrders.css">';
	echo('<link rel=stylesheet type="text/css" href="h2csv.css">');
	echo('</head>
	<!-- Background white, links blue (unvisited), navy (visited), red (active) -->
	 <body bgcolor="silver">
	  <h3>Viewer</h3>');
	
	function loadlog($filename) {
		$data = array();
		$line_no = 1;
		if (($fp = fopen($filename,"r")) !== FALSE)
		{
			while (($Row = fgetcsv($fp, 1000, ",")) !== FALSE) {
				$num = count($Row);
				if ($num > 0){
					$data [] = $Row;
				}
			}
	    		fclose($fp);
		}
		return $data;
	}
	
	function viewlog($filename) {
	$fp = fopen($filename,"r");
	$file = fread($fp,65535);
	$replaced = eregi_replace(",", "</td><td>", $file);
	$replaced2 = eregi_replace("\n", "</td></tr><tr><td>", $replaced);
	$replaced3 = eregi_replace("\r", "</td></tr><tr><td>", $replaced2);
	$replaced4 = eregi_replace("^", "<tr><td>", $replaced3);
	$replaced5 = eregi_replace("$", "</td></tr>", $replaced4);
	fclose($fp);
	
	return $replaced5;
	}
	
	// Start the table definition of your choice
	//echo "<table border=1 bordercolor=black cellspacing=0 cellpadding=5 width=100% style='font-size:10pt'>";
	
	// for pagination
	// want to know # lines in total
	$wk_csv_data = loadlog($filename);
	$wkNumRows = 0;
	$wkNumRows = count($wk_csv_data);
	
	// and lines per page
	$wkLinesPerPage = 8;
	
	$wkQueryList = "";
	// echo headers
	$wkHeaders = "<table border=\"1\" class=\"pg\">\n";
	$wkHeaders .= "<tr>";
	$wkHeaders .= "<th>Location</th>\n";
	$wkHeaders .= "<th>Code</th>\n";
	$wkHeaders .= "<th>Comment</th>\n";
	$wkHeaders .= "</tr>";
	
	//var_dump($wk_csv_data);
	
	bdcsRepage($wk_csv_data, $wkNumRows, $wkLinesPerPage, $wkQueryList, $wkHeaders);
	
	echo("<FORM action=\"h2page.php\" method=\"get\" name=getlines>\n");
	echo("<input name=\"export\" type=\"hidden\" value=\"CSV\">");
	{
		// html 4.0 browser
		echo("<table BORDER=\"0\" ALIGN=\"LEFT\">");
		whm2buttons("Export as CSV","h2csv.php","N","","Collect","", "N");
		echo("<td>");
		echo("<FORM action=\"h2page.php\" method=\"get\" name=getlines2>\n");
		echo("<input name=\"export\" type=\"hidden\" value=\"XLS\">");
		addScreenButton( "h2page.php", "", "Export as XLS");
		echo("</td></tr></table>");
	}
	//==================================================================================
	
	echo("</body></html>");
} 
if ($wkExport == "CSV") {
	function showlog($filename) {
		if (($fp = fopen($filename,"r")) !== FALSE)
		{
			while (($Row = fgets($fp, 1000 )) !== FALSE) {
				echo $Row;
			}
	    		fclose($fp);
		}
	}
	showlog($filename);
	
}
if ($wkExport == "XLS") {
/*
 	function xlsBOF()
	{
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}
*/
	function xlsEOF()
	{
		echo pack("ss", 0x0A, 0x00);
		return;
	}
	function xlsWriteNumber($Row, $Col, $Value)
	{
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}
/*
	function xlsWriteLabel($Row, $Col, $Value )
	{
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}
*/
	function loadlog($filename) {
		$data = array();
		$line_no = 1;
		if (($fp = fopen($filename,"r")) !== FALSE)
		{
			while (($Row = fgetcsv($fp, 1000, ",")) !== FALSE) {
				$num = count($Row);
				if ($num > 0){
					$data [] = $Row;
				}
			}
	    		fclose($fp);
		}
		return $data;
	}
	$wk_csv_data = loadlog($filename);
	foreach ($wk_csv_data as $wk_csv_idx => $wk_csv_line) {
		$wk_idx = $wk_csv_idx + 1;
 		//xlsWriteNumber($wk_idx,0,$wk__csv_line[0]);
 		xlsWriteLabel($wk_idx,0,$wk_csv_line[0]);
		xlsWriteLabel($wk_idx,1,$wk_csv_line[1]);
		xlsWriteLabel($wk_idx,2,$wk_csv_line[2]);
	}
	xlsEOF();

}
?>
