<?php
require_once 'logme.php';
function sentenceCase($s)
{
	$str = strtolower($s);
	$cap = true;
	$ret = "";
	for ($x = 0; $x < strlen($str); $x++)
	{
		$letter = substr($str, $x, 1);
		if ($letter == "." || $letter == "!" || $letter == "?")
		{
			$cap = true;
		} elseif ($letter != " " && $cap == true) 
		{
			$letter = strtoupper($letter);
			$cap = false;
		}
		$ret .= $letter;
	}
	return $ret;
}
function whm2scanvars($Link, $scantype, $scancode , $scanresult )
{

	echo('<script type="text/javascript">');

	if (isset($_COOKIE["LoginUser"]))
	{
		list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
	}
	$Query = "select max_length, fixed_length, symbology_prefix, data_expression from param where data_id = '" . $scancode . "'";
	if (isset($tran_device))
	{
		$wk_device_brand = getBDCScookie($Link, $tran_device, "CURRENT_BRAND" );
		$wk_device_model = getBDCScookie($Link, $tran_device, "CURRENT_MODEL" );
	} else {
		$wk_device_brand = "DEFAULT";
		$wk_device_model = "DEFAULT"; 
	}
	$Query .= " and data_brand = '" . $wk_device_brand . "' ";
	$Query .= " and data_model = '" . $wk_device_model . "' ";

	if (!($Result3 = ibase_query($Link, $Query)))
	{
		echo("Unable to query param!<BR>\n");
		exit();
	}

	while (($Row = ibase_fetch_row($Result3))) {
		echo "var " . $scantype . "maxlen = " . $Row[0] . ";\n";
		echo "var " . $scantype . "fixlen = " . '"' . $Row[1] . '";' ."\n";
		echo "var " . $scantype . "prefix = " . '"' . $Row[2] . '";' . "\n";
		echo "var " . $scantype . "re = " . '"' . $Row[3] . '";' . "\n";
		echo "var " . $scantype . "prefixkey = " . $scantype . 'prefix.split(";");' . "\n";
	}

	ibase_free_result($Result3);
	echo "function check" . sentenceCase($scantype) . "(instring ) { \n";
	echo "var wk_instring = instring.replace(/^\s+|\s+$/gm,'');" . "\n";
	echo "	return checkForType(wk_instring, " . $scantype . "re, " . $scantype . "prefix, " . $scantype . "maxlen, " . $scantype . "fixlen, " . $scantype . 'prefixkey, "' . $scanresult . '" ); ' . "\n";
	echo "}\n";
	echo "</script>\n";
}

?>
<script type="text/javascript">
var startposn = 0;

function checkForType(instring, inre, inprefix, inmaxlen, infixlen, inprefixkey, checkType) {
	/* # test whether a type input */
	var re = new RegExp(inre,"");
	var posn = instring.match(re);
	var querytype = "none";
	var mystartposn = 0;
	var posnidx = 0;
	var mykeystr = "";
	var myinstr = "";
	var mylen = 0;
	re = new RegExp(inre,"");
	posn = instring.match(re);
	querytype = "none";
	mystartposn = 0;
	posnidx = 0;
	mykeystr = "";
	myinstr = "";
	mylen = 0;
  	/* document.getcons.message.value = posn[0]; */
  	/* document.getcons.message.value = posn[0]; */
  	/* document.getcons.message.value = document.getcons.message.value + " " +  inmaxlen; */ 
  	/* document.getcons.message.value = document.getcons.message.value + " " +  infixlen; */
	if (posn == null) 
	{
  		return querytype;
	}
	if (inprefix == "")
	{
  		/* document.getcons.message.value = document.getcons.message.value + " " +  instring.length; */
		if ((instring.length == inmaxlen) &&
		    (infixlen == "T") &&
		    (posn[0] == instring))
		{
			querytype = checkType;
			startposn = 0;
			mystartposn = 0;
  			/* document.getcons.message.value = document.getcons.message.value + " matchTT " + querytype ; */ 
		}
		else
		{
			if ((instring.length <= inmaxlen) &&
		    	    (posn[0] == instring) &&
		    	    ((infixlen == "F") ||
		     	     (infixlen == "")))
			{
				querytype = checkType;
				startposn = 0;
				mystartposn = 0;
  				/* document.getcons.message.value = document.getcons.message.value + " matchFF " + querytype ; */ 
			}
		}
	}	
	else
	{
		/* # type prefix exists terminated by ";"s */
		for (posnidx = 0;posnidx < inprefixkey.length;posnidx++)
		{
			mykeystr = inprefixkey[posnidx];
			/* alert(mykeystr); */
  			/* document.getcons.message.value = document.getcons.message.value + " " +  instring.length; */ 
  			/* document.getcons.message.value = document.getcons.message.value + " " +  instring.length; */
			if (instring.indexOf(mykeystr) == 0) 
			{
				/* # starts with prefix */
				startposn = mykeystr.length;
				mylen = instring.length - startposn;
				myinstr = instring.substr(startposn,mylen);
				posn = myinstr.match(re);
  				/* document.getcons.message.value = document.getcons.message.value + " " +  posn[0]; */
  				/* document.getcons.message.value = document.getcons.message.value + " " +  posn[0]; */
  				/* document.getcons.message.value = document.getcons.message.value + " " +  startposn; */
  				/* document.getcons.message.value = document.getcons.message.value + " " +  mylen; */
				if ((mylen == inmaxlen) &&
		    		    (infixlen == "T") &&
		    		    (posn[0] == myinstr))
				{
					querytype = checkType;
					/* alert(startposn); */
					mystartposn = startposn;
  					/* document.getcons.message.value = document.getcons.message.value + " matchT " + querytype ; */ 
				}
				else
				{
					if ((mylen <= inmaxlen) &&
		    	    		    (posn[0] == myinstr) &&
		    	    		    ((infixlen == "F") ||
		     	     		    (infixlen == "")))
					{
						querytype = checkType;
						mystartposn = startposn;
						/* alert(startposn); */
  						/* document.getcons.message.value = document.getcons.message.value + " matchF " + querytype ; */ 
					}
				}
			}
		}
	}
	startposn = mystartposn;
  	/* document.getcons.message.value = document.getcons.message.value + " return " + querytype ; */
	/* alert(document.getcons.message.value); */
  	return querytype;
}

</script>

