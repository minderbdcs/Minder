<?php
include "../login.inc";

	
	if (isset($_COOKIE['BDCSData']))
	{
		list( $dummy, $location_from, $ssn_from , $orig_type ) = explode("|", $_COOKIE["BDCSData"]);
	}
	if (isset($_GET['type']))
	{
		$type = $_GET['type'];
	}
	if (isset($_POST['type']))
	{
		$type = $_POST['type'];
	}
	//echo ("type:".$type." orig ".$orig_type);
	if (!isset($type))
	{
		$type = $orig_type;
	}
	if (isset($_GET['seq']))
	{
		$seq = $_GET['seq'];
	}
	if (isset($_POST['seq']))
	{
		$seq = $_POST['seq'];
	}
	//echo ("seq:".$seq);

	// if exists answer then must split response id and branch to
	// from last answer - must use this branch to for
	// the next question
	// must write transaction for last entry
	// if this branch to is null or the question no doesn't exist
	// or the sequence of this question is zero
	// then go to get next ssn
	if (isset($_GET['answer']))
	{
		$answer = $_GET['answer'];
	}
	if (isset($_POST['answer']))
	{
		$answer = $_POST['answer'];
	}
	if (isset($answer))
	{
		list( $last_question, $last_response, $last_branch ) = explode("-", $answer);
		//echo("last answer". $answer);
		//echo("last branch". $last_branch);
		// once transaction is written then
		if ($last_branch == "")
		{
			//echo ("Location: GetSSNFrom.php?message="."End of Questions");
			//header("Location: GetSSNFrom.php?message="."End+of+Questions");
			header("Location: tran_QANE.php");
			exit();
		}
	}


	// Set the variables for the database access:
	require_once('DB.php');
	require('db_access.php');
	
	//$Link = DB::connect($dsn,true);
	//if (DB::isError($Link))
	//{
		//echo ("Location: GetSSNFrom.php?message="."connect");
	//	header("Location: GetSSNFrom.php?message="."connect");
	//	exit();
	//}
	if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
	{
		header("Location: GetSSNFrom.php?message="."connect");
		exit();
	}
	$dbTran = ibase_trans(IBASE_DEFAULT, $Link);
	
	if (isset($answer))
	{
		$Query = "SELECT sequence, question_id, question FROM test_questions where question_id = '" . $last_branch . "'";
	}
	else
	{
		$Query = "SELECT sequence, question_id, question FROM test_questions where ssn_type = '" . $type . "' AND sequence > " . $seq . " ORDER BY sequence";
	}
	//echo($Query);

	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
		//echo ("Location: GetSSNFrom.php?message="."query test_questions");
	//	header("Location: GetSSNFrom.php?message="."query+test_questions");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: GetSSNFrom.php?message="."query+test_questions");
		exit();
	}
	//if ( ($Row = $Result->fetchRow()) ) 
	if (($Row = ibase_fetch_row($Result)))
	{
		$question_seq = $Row[0];
		$question_id = $Row[1];
		$question_text = $Row[2];
		//echo("seq:".$question_seq);
		//echo("question_id:".$question_id);
	}
	else
	{
		if (isset($answer))
		{
			//echo  ("Location: GetSSNFrom.php?message="."No More Questions for this Type");
			//header("Location: GetSSNFrom.php?message="."No+More+Questions+for+this+Type");
			header("Location: tran_QANE.php");
		}
		else
		{
			//echo  ("Location: GetSSNFrom.php?message="."No Questions for this Type");
			//header("Location: GetSSNFrom.php?message="."No+Questions+for+this+Type");
			header("Location: tran_QANE.php");
		}
		exit();
	}
	//release memory
	//$Result->free();
	ibase_free_result($Result);

	if (isset($answer) and $question_seq == 0)
	{
		//commit
		//$Link->commit();
		ibase_commit($dbTran);

		//close
		//$Link->disconnect();
		ibase_close($Link);

		// end of test questions
		//echo ("Location: GetSSNFrom.php?message="."No More Questions for this Type");
		//header("Location: GetSSNFrom.php?message="."No+More+Questions+for+this+Type");
		header("Location: tran_QANE.php");
		exit();
	}

	// get count of responses
	$Query = "SELECT count(*) FROM valid_responses WHERE question_id = " . $question_id  ;
	//echo($Query);
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
		//echo ("Location: GetSSNFrom.php?message="."query valid responses count");
	//	header("Location: GetSSNFrom.php?message="."query+valid+responses+count");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: GetSSNFrom.php?message="."query+valid+responses+count");
		exit();
	}
	$responses_cnt = 0;
	//if ( ($Row = $Result->fetchRow() ) ) {
	if (($Row = ibase_fetch_row($Result))) {
 		$responses_cnt = $Row[0];
	}
	//release memory
	//$Result->free();
	ibase_free_result($Result);

	$Query = "SELECT response_id, valid_response, branch_to, mandatory_input FROM valid_responses WHERE question_id = " . $question_id . " ORDER BY response_id ";
	//echo($Query);
	//$Result = $Link->query($Query);
	//if (DB::isError($Result))
	//{
		//echo ("Location: GetSSNFrom.php?message="."query valid responses");
	//	header("Location: GetSSNFrom.php?message="."query+valid+responses");
	//	exit();
	//}
	if (!($Result = ibase_query($Link, $Query)))
	{
		header("Location: GetSSNFrom.php?message="."query+valid+responses");
		exit();
	}

	echo("<html>\n");
	echo("<head>\n");
 include "viewport.php";
  	echo("<title>Answer a Question</title>\n");
	echo("</head>");
	echo("<body>\n");
	// Create a table.
	echo ("<TABLE BORDER=\"1\">\n");
	echo("<TH>". $question_text . "</TH>\n");
	echo ("</TR>\n");
	echo ("</TABLE>\n");
	include "2buttons.php";

  	echo("<h4 ALIGN=\"LEFT\">Enter Response</h4>\n");

 	echo("<TABLE BORDER=\"0\" ALIGN=\"LEFT\">\n");
	echo("<FORM action=\"tran_QANS.php\" method=\"post\" name=getanswer>\n");
	echo("<INPUT type=\"hidden\" name=\"seq\" value=\"". $question_seq . "\">\n");
	echo("<INPUT type=\"hidden\" name=\"type\" value=\"". $type . "\">\n");
	$echoed_select = 'N';
	// Fetch the results from the database.
	//while ( ($Row = $Result->fetchRow())  and ($rcount < $rscr_limit) ) {
	//while ( ($Row = $Result->fetchRow() ) ) {
	while (($Row = ibase_fetch_row($Result))) {
 		$lastcode = $Row[0];
		if ($echoed_select == 'N')
		{
			if ($responses_cnt > 1)
			{
				// more than 1 response
				echo("<SELECT name=\"answer\">\n");
				$echoed_select = 'Y';
			}
			else
			{
				// B for Blob 
				$echoed_select = 'B';
			}
		}
		if ($echoed_select == 'Y')
		{
			echo("<OPTION value=\"" . $question_id . "-" . $Row[0] . "-" . $Row[2] . "\">$Row[1]\n");
		}
		if ($echoed_select == 'B')
		{
			echo("$Row[1]<BR><INPUT type=\"text\" name=\"note\" ");
			echo(" size=\"40\"");
			echo(" maxlength=\"40\"><BR>\n");
			echo("<INPUT type=\"hidden\" name=\"answer\" value=\"" . $question_id . "-" . $Row[0] . "-" . $Row[2] . "\">\n");
		}
		//$rcount++;
	}
	if ($echoed_select == 'Y')
	{
		echo("</SELECT>\n");
	}
/*
	if ($_SERVER['HTTP_USER_AGENT'] < "Mozilla/4.0")
	{
		// html 3.2 browser
		echo("<INPUT type=\"submit\" name=\"saveanswer\" value=\"Save Answer!\">\n");
		echo("</FORM>\n");
		echo("<FORM action=\"tran_QANX.php\" method=\"post\" name=goprev>\n");
		echo("<INPUT type=\"hidden\" name=\"type\" value=\"". $type . "\">\n");
		echo("<INPUT type=\"submit\" name=\"prev\" value=\"Prev\">\n");
		echo("</FORM>\n");
		echo("<FORM action=\"GetSSNFrom.php\" method=\"post\" name=goback>\n");
		echo("<INPUT type=\"submit\" name=\"back\" value=\"Back\">\n");
		echo("</FORM>\n");
	}
	else
*/
	{
		// html 4.0 browser
		//whm2buttons('Send', 'GetSSNFrom.php', 'N');
		whm2buttons('Send',"GetSSNFrom.php" ,"N" ,"Back_50x100.gif" ,"Back" ,"accept.gif");
/*
		echo("<BUTTON name=\"saveanswer\" value=\"Save Answer!\" type=\"submit\">\n");
		echo("Send<IMG SRC=\"/icons/forward.gif\" alt=\"forward\"></BUTTON>\n");
		echo("</FORM>\n");
		echo("<FORM action=\"tran_QANX.php\" method=\"post\" name=goprev>\n");
		echo("<INPUT type=\"hidden\" name=\"seq\" value=\"". $question_seq . "\">\n");
		echo("<INPUT type=\"hidden\" name=\"type\" value=\"". $type . "\">\n");
		echo("</FORM>\n");
		echo("<BUTTON name=\"prev\" type=\"button\" onfocus=\"location.href='tran_QANX.php?type=".urlencode($type)."';\">\n");
		echo("Prev<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
		echo("<BUTTON name=\"back\" type=\"button\" onfocus=\"location.href='GetSSNFrom.php';\">\n");
		echo("Back<IMG SRC=\"/icons/back.gif\" alt=\"back\"></BUTTON>\n");
*/
		echo ("<TR>");
		echo ("<TD>");
		echo("<FORM action=\"tran_QANX.php\" method=\"post\" name=goprev>\n");
		echo("<INPUT type=\"hidden\" name=\"seq\" value=\"". $question_seq . "\">\n");
		echo("<INPUT type=\"hidden\" name=\"type\" value=\"". $type . "\">\n");
		echo("<INPUT type=\"IMAGE\" ");  
		echo('SRC="/icons/whm/Prev_50x100.gif" alt="Prev"></INPUT>');
		echo("</FORM>");
		echo ("</TD>");
		echo ("</TR>");
		echo("</TABLE>\n");
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

	echo("<script type=\"text/javascript\">\n");
	if ($echoed_select == 'B')
	{
		echo("document.getanswer.note.focus();\n");
	}
	else
	{
		echo("document.getanswer.answer.focus();\n");
	}
	echo("</script>\n");
	echo("</body>\n");
	echo("</html>\n");
?>
