<?php
include "../login.inc";

	$rcount = 0;
	$cookiedata = "";
	$wk_dummy = "";
	if (isset($_POST['ssn_id'])) 
	{
		$ssn_id = $_POST['ssn_id'];
	}
	if (isset($_GET['ssn_id'])) 
	{
		$ssn_id = $_GET['ssn_id'];
	}
	if (isset($_POST['grn'])) 
	{
		$wk_dummy = $_POST['grn'];
	}
	if (isset($_GET['grn'])) 
	{
		$wk_dummy = $_GET['grn'];
	}
	// must get type and location from current issn
	require_once 'DB.php';
	require 'db_access.php';
	//$Link = DB::connect($dsn,true);
	//if (DB::isError($Link))
	//{
	//	header ("Location: GetSSNFrom.php?message=connect");
	//	exit();
	//}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header ("Location: GetSSNFrom.php?message=connect");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	if (isset($ssn_id))
	{
		$Query = "SELECT iss.wh_id, iss.locn_id, sn.ssn_type, sn.question_id, sn.answer_id, issn.original_ssn  from issn iss join ssn sn on iss.original_ssn = sn.ssn_id  where iss.ssn_id = '".$ssn_id."'";
		$Query = "SELECT iss.wh_id, iss.locn_id, sn.ssn_type, sn.question_id, sn.answer_id, iss.original_ssn  from issn iss join ssn sn on iss.original_ssn = sn.ssn_id  where iss.ssn_id = '".$ssn_id."'";
		//echo ($Query);
		//$Result = $Link->query($Query);
		//if (DB::isError($Result))
		//{
		//	header ("Location: GetSSNFrom.php?message=query");
		//	exit();
		//}
		if (!($Result = ibase_query($Link, $Query)))
		{
			header ("Location: GetSSNFrom.php?message=query");
			exit();
		}
		//if ( ($Row = $Result->fetchRow()) ) 
		//{
		//	$locationfrom = $Row[0].$Row[1];
		//	$type = $Row[2];
		//	$answer = $Row[3] . '-' . $Row[4];
		//	$answer_id = $Row[4];
		//	$question_id = $Row[3];
			//echo("locn ".$locationfrom. "type".$type)
		//	$cookiedata .= "|" . $locationfrom . "|" . $ssn_id . "|" . $type;
		//}
		if (($Row = ibase_fetch_row($Result)))
		{
			$locationfrom = $Row[0].$Row[1];
			$type = $Row[2];
			$answer = $Row[3] . '-' . $Row[4];
			$answer_id = $Row[4];
			$question_id = $Row[3];
			$original_ssn = $Row[5];
			//echo("locn ".$locationfrom. "type".$type)
			//$cookiedata .= "|" . $locationfrom . "|" . $ssn_id . "|" . $type;
			$cookiedata .= $wk_dummy . "|" . $locationfrom . "|" . $original_ssn . "|" . $type;
		}
		else
		{
			header ("Location: GetSSNFrom.php?message=nossn");
			exit();
		}
	}
	setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	if (isset($type))
	{
		if ($type > "")
		{
			//echo  ("Location: GetQuestion.php?seq=-1&type=".$type);
			//echo  (urlencode("Location: GetQuestion.php?seq=-1&type=".$type));
			if ($Row[4] == "")
			{
				//last response is null
				//release memory
				//$Result->free();
				ibase_free_result($Result);

				header ("Location: GetQuestion.php?seq=-1&type=".urlencode($type));
				exit();
			}
			else
			{
				// must resume
				// but 1st need the sequence and branch to of this question
				//release memory
				//$Result->free();
				ibase_free_result($Result);
				$Query = "SELECT sequence FROM test_questions WHERE question_id=". $question_id;
				//echo ($Query);
				//$Result = $Link->query($Query);
				//if (DB::isError($Result))
				//{
				//	header ("Location: GetSSNFrom.php?message=query+question");
				//	exit();
				//}
				if (!($Result = ibase_query($Link, $Query)))
				{
					header ("Location: GetSSNFrom.php?message=query+question");
					exit();
				}
				//if ( ($Row = $Result->fetchRow()) ) 
				if (($Row = ibase_fetch_row($Result)))
				{
					$seq = $Row[0];
				}
				else
				{
					header ("Location: GetSSNFrom.php?message=no+question");
					exit();
				}
				//release memory
				//$Result->free();
				ibase_free_result($Result);
				$Query = "SELECT branch_to FROM valid_responses WHERE response_id=". $answer_id;
				//echo ($Query);
				//$Result = $Link->query($Query);
				//if (DB::isError($Result))
				//{
				//	header ("Location: GetSSNFrom.php?message=query+response+branch");
				//	exit();
				//}
				if (!($Result = ibase_query($Link, $Query)))
				{
					header ("Location: GetSSNFrom.php?message=query+response+branch");
					exit();
				}
				//if ( ($Row = $Result->fetchRow()) ) 
				if (($Row = ibase_fetch_row($Result)))
				{
					$answer .= '-' . $Row[0];
				}
				else
				{
					header ("Location: GetSSNFrom.php?message=no+response");
					exit();
				}
				//release memory
				//$Result->free();
				ibase_free_result($Result);


				header ("Location: GetQuestion.php?seq=".$seq."&type=".urlencode($type)."&answer=".$answer);
				exit();
			}
		}
	}
	echo("<html>");
	echo("<head>");
include "viewport.php";
  	echo("<title>Get Type you are working with</title>\n");
	echo("</head>");
	echo("<body>");
  	echo("<h4 ALIGN=\"LEFT\">Enter Type</h4>\n");

	$Query = "SELECT code, description FROM ssn_type ORDER BY code ";
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
	//	header ("Location: GetSSNFrom.php?message=query");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		header ("Location: GetSSNFrom.php?message=query");
		exit();
	}
        include "2buttons.php";
 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<FORM action=\"tran_NITP.php\" method=\"post\" name=gettype>\n");
	echo("<INPUT type=\"hidden\" name=\"seq\" value=\"-1\">\n");
	echo("<INPUT type=\"hidden\" name=\"ssn_id\" value=\"" . $ssn_id . "\">\n");
	echo("<SELECT name=\"type\">\n");
	// Fetch the results from the database.
	//while ( ($Row = $Result->fetchRow())  and ($rcount < $rscr_limit) ) {
	//while ( ($Row = $Result->fetchRow() ) ) {
	while (($Row = ibase_fetch_row($Result))) {
 		$lastcode = $Row[0];
		//echo($Row[1] . "<INPUT type=\"checkbox\" name=\"type\" value=\"" . $Row[0] . "\">\n");
		echo("<OPTION value=\"" . $Row[0] . "\">$Row[1]\n");
		$rcount++;
	}
	echo("</SELECT>\n");
/*
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		// html 3.2 browser
		echo("<INPUT type=\"submit\" name=\"savetype\" value=\"Save Type!\">\n");
		echo("</FORM>\n");
		echo("<FORM action=\"GetSSNFrom.php\" method=\"post\" name=goback>\n");
		echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
		echo("</FORM>\n");
	}
	else
*/
	{
		// html 4.0 browser
		//whm2buttons('Save Type!', 'GetSSNFrom.php');
		whm2buttons('Save Type',"GetSSNFrom.php" ,"Y" ,"Back_50x100.gif" ,"Back" ,"savetype.gif");
/*
		echo("<BUTTON name=\"savetype\" value=\"Save Type!\" type=\"submit\">\n");
		echo("Save Type<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
		echo("</FORM>\n");
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='GetSSNFrom.php';\">\n");
		echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
	}
	//release memory
	//$Result->free();
	ibase_free_result($Result);

	//commit
	//$Link->commit();
	ibase_commit($dbTran);

	//close
	//$Link->disconnect();
	ibase_close($Link);

?>
<script type="text/javascript">
document.gettype.type.focus();
</script>
</body>
</html>
