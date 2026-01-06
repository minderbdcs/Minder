<?php
//function bdcsRepage($Link, $numrows, $rowsperpage, $wkQuery , $wkHeaders )
function bdcsRepage($Link, $numrows, $rowsperpage, $wkQuery , $wkHeaders, $wkDoCheck = 'F' )
{
	// find out total pages
	$totalpages = ceil($numrows / $rowsperpage);

	// get the current page or set a default
	if (isset($_POST['currentpage']) && is_numeric($_POST['currentpage'])) {
	   // cast var as int
	   $currentpage = (int) $_POST['currentpage'];
	} else {
	    if (isset($_GET['currentpage']) && is_numeric($_GET['currentpage'])) {
	       // cast var as int
	       $currentpage = (int) $_GET['currentpage'];
	    } else {
	       // default page num
	       $currentpage = 1;
	    } // end if
	} // end if
	if (!isset($currentpage))
	{
	    $currentpage = 0;
	}

	// if current page is greater than total pages...
	if ($currentpage > $totalpages) {
	   // set current page to last page
	   $currentpage = $totalpages;
	} // end if
	// if current page is less than first page...
	if ($currentpage < 1) {
	   // set current page to first page
	   $currentpage = 1;
	} // end if

	/******  build the pagination links ******/
	// range of num links to show
	$range = 3;

	// if not on page 1, don't show back links
	if ($currentpage > 1) {
	   // show << link to go back to page 1
	   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=1'><<</a> ";
	   // get previous page num
	   $prevpage = $currentpage - 1;
	   // show < link to go back to 1 page
	   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$prevpage'><</a> ";
	} // end if 

	// loop to show links to range of pages around current page
	for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
	   // if it's a valid page number...
	   if (($x > 0) && ($x <= $totalpages)) {
	      // if we're on current page...
	      if ($x == $currentpage) {
	         // 'highlight' it but don't make a link
	         echo " [<b>$x</b>] ";
	      // if not current page...
	      } else {
	         // make it a link
	         echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$x'>$x</a> ";
	      } // end else
	   } // end if 
	} // end for
                 
	// if not on last page, show forward and last page links        
	if ($currentpage != $totalpages) {
	   // get next page
	   $nextpage = $currentpage + 1;
	    // echo forward link for next page 
	   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$nextpage'>></a> ";
	   // echo forward link for lastpage
	   echo " <a href='{$_SERVER['PHP_SELF']}?currentpage=$totalpages'>>></a> ";
	} // end if
	/****** end build pagination links ******/

	// now show this pages data
	// the offset of the list, based on current page 
	$offset = ($currentpage - 1) * $rowsperpage;

	echo $wkHeaders;

	// get the info from the db 
	$sql = "SELECT FIRST $rowsperpage SKIP $offset  " . $wkQuery;
	//echo $sql;

	if (!($Result = ibase_query($Link, $sql)))
	{
		echo("Unable to Read Lines!<BR>\n");
		exit();
	}

	$wk_cnt = 0;
	while ( ($Row = ibase_fetch_row($Result)) ) {
		$wk_cnt = $wk_cnt + 1;
		if (is_float($wk_cnt/2)) {
			// number is odd
			echo("<tr class=\"pgodd\">");
		} else {
			// number is even
			echo("<tr class=\"pgeven\">");
		}
		//echo("<tr>");
		for ($i=0; $i<ibase_num_fields($Result); $i++)
		{
			if (($wkDoCheck == "T") and ($i== 0)) {
				echo("<td><input type=\"checkbox\" name=\"reserveme[]\" value=\"" . $Row[$i] . "\" onfocus=\"saveMe('" . $Row[$i] . "');\"></td>\n");
			}
			//echo("<td>".$Row[$i]."</td>\n");
			echo("<td>". htmlspecialchars($Row[$i]) ."</td>\n");
		}
		echo("</tr>");
	}
	//release memory
	ibase_free_result($Result);
	echo ("</table>\n");
}

?>
