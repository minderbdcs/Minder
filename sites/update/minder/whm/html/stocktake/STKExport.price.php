<?php

// Include PEAR::Spreadsheet_Excel_Writer
require_once "Spreadsheet/Excel/Writer.php";

require_once('DB.php');
require('db_access.php');
ini_set("MAX_EXECUTION_TIME", "120");
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	header("Location: Stocktake_menu.php?message=Can+t+connect+to+DATABASE!");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

if (isset($_COOKIE['SaveUser']))
{
	list($UserName, $DBDevice,$UserType) = explode("|", $_COOKIE['SaveUser']);
}
$Query = "select control.ftp_directory from control";
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query Control!<BR>\n");
	exit();
}
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result)) )
{
	$ftp_root = $Row[0];
}
//release memory
//$Result->free();
ibase_free_result($Result);
$Query = "select ftp_export from sys_hh where user_type = '" . $UserType . "'";
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query SYS_HH!<BR>\n");
	exit();
}
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result)) )
{
	$ftp_export = $Row[0];
}
//release memory
//$Result->free();
ibase_free_result($Result);
$wk_filename = $ftp_root . $ftp_export . '\\' . "Variance.xls";
echo($wk_filename);
//Create instance
$xls =& new Spreadsheet_Excel_Writer($wk_filename);

//send HTTP headers so browser knows whats coming
//$xls->send("test.xls");
// add a worksheet
$sheet =& $xls->addWorksheet('Variance Sheet1');

//title for data
$titleText = "Minder Export Data " . date("dS M Y");

//create a format object
$titleFormat =& $xls->addFormat();

//set the font
$titleFormat->setFontFamily('Helvetica');

//set the text to bold
$titleFormat->setBold();

//set the text size
$titleFormat->setSize('13');

//set the text colour
$titleFormat->setColor('navy');

//set the bottom border to thick
$titleFormat->setBottom(2);

//set the color of bottom border
$titleFormat->setBottomColor('navy');

//set the alignment to merge
$titleFormat->setAlign('merge');

//add the title to the top left cell
$sheet->write(0,0,$titleText,$titleFormat);

//add 3 empty cells to merge
$sheet->write(0,1,'',$titleFormat);
$sheet->write(0,2,'',$titleFormat);
$sheet->write(0,3,'',$titleFormat);

//the row height
$sheet->setRow(0,30);

//set the column width for 1st 4 cols
$sheet->setColumn(0,0,15);
$sheet->setColumn(1,1,35);
$sheet->setColumn(2,2,12);
$sheet->setColumn(3,3,9);
$sheet->setColumn(4,5,10);
$sheet->setColumn(6,6,13);
$sheet->setColumn(7,7,15);
$sheet->setColumn(8,8,12);
$sheet->setColumn(9,9,9);
$sheet->setColumn(10,11,10);
$sheet->setColumn(12,12,15);
$sheet->setColumn(13,14,13);
$sheet->setColumn(15,15,10);

//set up some formatting
$colHeadingFormat =& $xls->addFormat();
$colHeadingFormat->setBold();
$colHeadingFormat->setFontFamily('Helvetica');
$colHeadingFormat->setBold();
$colHeadingFormat->setSize('10');
$colHeadingFormat->setAlign('center');
// an array of column names
$colNames = array('Item','Description','Company','Status','Legacy','Minder','Standard Cost','Alternate','Company2','Status2','Legacy2','Minder2','Standard Cost2','Cost($)','Cost2($)','Total','Total2');
// add col names
$sheet->writeRow(2,0,$colNames,$colHeadingFormat);
// freeze title
$freeze = array(3,0,4,0);
$sheet->freezePanes($freeze);

$currentRow = 4;

/*
$Query = "select va.prod_id, prod_profile.short_desc, va.company_id, va.variance_status, va.legacy_qty, va.available_qty, prod_profile.standard_cost ";
$Query .= "from variance va ";
$Query .= "join prod_profile on prod_profile.prod_id = variance.prod_id ";
$Query .= "where va.run_date = (select max(run_date) from variance) ";
$Query .= " and (va.legacy_qty <> 0 or va.available_qty <> 0) ";
$Query .= " order by absflt((legacy_qty - available_qty) * prod_profile.standard_cost) desc ";
*/
$Query = "select va.prod_id, pf1.short_desc, va.company_id, va.variance_status, va.legacy_qty, va.available_qty, pf1.standard_cost, pf2.prod_id, pf2.company_id, va2.variance_status, va2.legacy_qty, va2.available_qty ,pf2.standard_cost  ";
$Query .= "from variance va ";
$Query .= "left outer join prod_profile pf1 on pf1.prod_id = va.prod_id ";
$Query .= "left outer join variance va2 on va2.company_id = pf1.alternate_company_id and va2.prod_id =  pf1.alternate_id and va2.run_date = va.run_date ";
$Query .= "left outer join prod_profile pf2 on pf2.prod_id = pf1.alternate_id ";
$Query .= "where va.run_date = (select max(va3.run_date) from variance va3) ";
//$Query .= " and (va.prod_id='H897' or va2.prod_id='H897')";
//$Query .= " and ( (va.legacy_qty <> 0 or va.available_qty <> 0) ";
//$Query .= " or  (va2.legacy_qty <> 0 or va2.available_qty <> 0)) ";
$Query .= " order by va.company_id, absflt((va.legacy_qty - va.available_qty) * pf1.standard_cost) desc ";
		
if (!($Result = ibase_query($Link, $Query)))
{
	print("Unable to query product!<BR>\n");
	exit();
}
// Fetch the results from the database.
while (($Row = ibase_fetch_row($Result)) )
{
	$sheet->writeRow($currentRow,0,$Row);
	// excel row starts from 1
	$excelRow = $currentRow + 1;
	// create a string containing the formula
	//$formula = '=PRODUCT(F' . $excelRow . ':G' . $excelRow . ')';
	$formula = '=(F' . $excelRow . '-E' . $excelRow . ')*G'. $excelRow . ')';
	$sheet->writeFormula($currentRow,13,$formula);
	$formula2 = '=(L' . $excelRow . '-K' . $excelRow . ')*M'. $excelRow . ')';
	$sheet->writeFormula($currentRow,14,$formula2);
	$currentRow++;
}
// the first row
$startingExcelRow = 5;
$finalExcelRow = $currentRow;
// Excel formula to sum all the item totals to get the grand total
$gTFormula = '=SUM(N'.$startingExcelRow.':N'.$finalExcelRow.')';
$gTFormula2 = '=SUM(O'.$startingExcelRow.':O'.$finalExcelRow.')';
$gTFormat =& $xls->addFormat();
$gTFormat->setFontFamily('Helvetica');
$gTFormat->setBold();
$gTFormat->setTop(1);  // Top border
$gTFormat->setBottom(1); // Bottom border

// Add some text plus format
$sheet->write($currentRow,14,'Grand Total:',$gTFormat);
//add grand total formula
$sheet->writeFormula($currentRow,15,$gTFormula);
$sheet->writeFormula($currentRow,16,$gTFormula2);


//release memory
//$Result->free();
ibase_free_result($Result);

//finish
$xls->close();
//header("Location: Stocktake_menu.php?message=Export+Complete");
$backto = "Stocktake_menu.php";
$alt2 = "Back";
$image2 = "Back_50x100.gif";
echo("<FORM action=\"" . $backto . "\" method=\"post\" name=" . $alt2 . ">\n");
echo("<INPUT type=\"IMAGE\" width=\"$rimg_width\" ");  
echo('SRC="/icons/whm/' . $image2 . '" alt="' . $alt2 . '"></INPUT>');
echo("</FORM>");
?>

