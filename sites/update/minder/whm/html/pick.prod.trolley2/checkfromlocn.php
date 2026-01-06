<?php
include "../login.inc";
require_once 'DB.php';
require 'db_access.php';

/**
 * get Allowed Status list for ISSNs to Pick
 *
 * @param ibase_link $Link Connection to database
 * @return string
 */
function getAllowedStatus ($Link  )
{
	// get allowed statuses
	$Query2 = "select pick_import_ssn_status from control "; 
	if (!($Result = ibase_query($Link, $Query2)))
	{
		//header("Location pick_Menu.php?message=Unable+to+Read+Control");
		$allowed_status = ",,";
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$allowed_status = $Row[0];
		}
		else
		{
			$allowed_status = ",,";
		}
	}

	//release memory
	ibase_free_result($Result);
	return $allowed_status;
}

/**
 * get Order WH and Company
 *
 * @param ibase_link $Link Connection to database
 * @param string $order
 * @return array
 */
function getOrderWhCompany ($Link, $order )
{
	$pickResult = array();
	$pick_wh_id  = "";
	$pick_company_id  = "";
	$pick_zone  = "";
	$pick_sub_type  = "";
	$pick_alternate_companys  = "";
	$Query2 = "select wh_id, company_id, zone_c, pick_order_sub_type, alternate_companys from pick_order where pick_order = '" . $order . "' "; 
	if (!($Result = ibase_query($Link, $Query2)))
	{
	}
	else
	{
		if ( ($Row = ibase_fetch_row($Result)) ) {
			$pick_wh_id  = $Row[0];
			$pick_company_id  = $Row[1];
			$pick_zone  = $Row[2];
			$pick_sub_type  = $Row[3];
			$pick_alternate_companys  = $Row[4];
		}
	}

	//release memory
	ibase_free_result($Result);
	if (is_null($pick_alternate_companys))
	{
		$pick_alternate_companys = $pick_company_id;
	}
	$pickResult['WH_ID'] = $pick_wh_id;
	$pickResult['COMPANY_ID'] = $pick_company_id;
	$pickResult['ZONE'] = $pick_zone;
	$pickResult['SUB_TYPE'] = $pick_sub_type;
	$pickResult['ALTERNATE_COMPANYS'] = $pick_alternate_companys;
	$pick_restrict_zone  = "F";
	if ($pick_sub_type <> "")
	{
		$Query2 = "select pos_restrict_by_zone from pick_order_sub_type where pos_id  = '" . $pick_sub_type . "' "; 
		if (!($Result = ibase_query($Link, $Query2)))
		{
		}
		else
		{
			if ( ($Row = ibase_fetch_row($Result)) ) {
				$pick_restrict_zone  = $Row[0];
			}
		}
		ibase_free_result($Result);
	}
	$pickResult['RESTRICT_BY_ZONE'] = $pick_restrict_zone;
	return $pickResult;
}

/**
 * check a Query Matches the Location
 *
 * @param ibase_link $Link Connection to database
 * @param string $Query
 * @param string $location
 * @return integer
 */
function checkQuery ($Link, $Query , $location)
{
	//echo($Query);
	if (!($Result = ibase_query($Link, $Query)))
	{
		//echo("Unable to Read Picks!<BR>\n");
		header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
		exit();
	}

	//echo("location:" . $location);
	$location_found = 0;
	// Fetch the results from the database.
	while ( ($Row = ibase_fetch_row($Result)) ) {
		//echo("<br>row21:" . trim($Row[2].$Row[0]));
		//echo(":location:" . $location);
		//echo("<br>");
		if (trim($Row[2].$Row[0]) == $location)
		{
			// location is valid
			$location_found = 1;
			//echo "location found";
		}
		else 
		{
			//echo ("location mismatch:" . trim($Row[2].$Row[0]) . ":");
			//echo "location not found";
		}
	}

	//release memory
	ibase_free_result($Result);
	return $location_found;
}

/**
 * check ISSN scanned for an SSN Pick Line
 *
 * @param ibase_link $Link Connection to database
 * @param string $order
 * @param string $ssn
 * @param string $location
 * @param string $scanned_ssn
 * @return string
 */
function checkISSNforSSN ($Link, $order, $ssn, $location, $scanned_ssn  )
{
	$wk_reason = "";
	$wk_issn_found = 0;
	$wk_wh_found = 0;
	$wk_company_found = 0;
	$wk_qty_found = 0;
	$wk_status_found = 0;
	$allowed_status = getAllowedStatus ($Link );
	//list($pick_wh_id, $pick_company_id) =  getOrderWhCompany ($Link, $order );
	$pickOrder  =  getOrderWhCompany ($Link, $order );

	// can fail if no scanned  ssn reason done above in scan check
	// ssn mismatch or ssn not for the original
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and ( s1.ssn_id = '" . $ssn . "'";
	$Query .= " or    s1.original_ssn = '" . $ssn . "' )";
	$wk_issn_found = checkQuery ($Link, $Query, $location );
	if ($wk_issn_found == 0)
	{
		$wk_reason .= " ISSN not Found";
	}

	// wh
	$Query = "select first 1  s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$wk_wh_found = checkQuery ($Link, $Query, $location );
	if ($wk_wh_found == 0)
	{
		$wk_reason .= " Wrong WH";
	}

	// company
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$wk_company_found = checkQuery ($Link, $Query, $location );
	if ($wk_company_found == 0)
	{
		$wk_reason .= " Wrong Company";
	}

	// current_qty not >0
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$wk_qty_found = checkQuery ($Link, $Query, $location );
	if ($wk_qty_found == 0)
	{
		$wk_reason .= " Qty < 1";
	}

	// status
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	$wk_status_found = checkQuery ($Link, $Query, $location );
	if ($wk_status_found == 0)
	{
		$wk_reason .= " Wrong Status";
	}

	// location for issn does not have a locn seq
	return $wk_reason;

}

/**
 * check ISSNs scanned for a Product Line
 *
 * @param ibase_link $Link Connection to database
 * @param string $order
 * @param string $prod_no
 * @param string $location
 * @param string $scanned_ssn
 * @return string
 */
function checkISSNforProd ($Link, $order, $prod_no, $location, $scanned_ssn  )
{
	$wk_reason = "";
	$wk_issn_found = 0;
	$wk_wh_found = 0;
	$wk_company_found = 0;
	$wk_qty_found = 0;
	$wk_status_found = 0;
	$allowed_status = getAllowedStatus ($Link );
	//list($pick_wh_id, $pick_company_id) =  getOrderWhCompany ($Link, $order );
	$pickOrder  =  getOrderWhCompany ($Link, $order );

	// can fail if no scanned  ssn reason done above in scan check
	// ssn mismatch or ssn not for the original
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.prod_id = '" . $prod_no . "'";

	$wk_issn_found = checkQuery ($Link, $Query, $location );
	if ($wk_issn_found == 0)
	{
		$wk_reason .= " ISSN not Found for Product";
	}

	// wh
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$wk_wh_found = checkQuery ($Link, $Query, $location );
	if ($wk_wh_found == 0)
	{
		$wk_reason .= " Wrong WH";
	}

	// company
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$wk_company_found = checkQuery ($Link, $Query, $location );
	if ($wk_company_found == 0)
	{
		$wk_reason .= " Wrong Company";
	}

	// current_qty not >0
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$wk_qty_found = checkQuery ($Link, $Query, $location );
	if ($wk_qty_found == 0)
	{
		$wk_reason .= " Qty < 1";
	}

	// status
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	$wk_status_found = checkQuery ($Link, $Query, $location );
	if ($wk_status_found == 0)
	{
		$wk_reason .= " Wrong Status";
	}

	// zone  
	if (($pickOrder['RESTRICT_BY_ZONE']  == "T") and ($pickOrder['ZONE'] != ""))
	{
		$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
		$Query .= "from issn s1 ";
		$Query .= "join location l3  on s1.wh_id = l3.wh_id and s1.locn_id = l3.locn_id ";
		if (isset($scanned_ssn))
		{
			$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
		} else {
			$Query .= " where s1.ssn_id = ''";
			// failed ssn check so no data here
		}
		$Query .= " and   s1.prod_id = '" . $prod_no . "'";
		$Query .= " and   s1.wh_id = '".$pickOrder['WH_ID'] ."'";
		$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
		$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
		$Query .= " and s1.current_qty > 0 ";
		$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
        	$Query .= " and l3.zone_c = '" . $pickOrder['ZONE'] . "'"; 
		$wk_zone_found = checkQuery ($Link, $Query, $location );
		if ($wk_zone_found == 0)
		{
			$wk_reason .= " Wrong Zone";
		}
	}

	// location for issn does not have a locn seq
	return $wk_reason;
}

/**
 * check Location scanned for a Product Line
 *
 * @param ibase_link $Link Connection to database
 * @param string $order
 * @param string $prod_no
 * @param string $location
 * @param string $scanned_ssn
 * @return string
 */
function checkProdforProd ($Link, $order, $prod_no, $location )
{
	$wk_reason = "";
	$wk_issn_found = 0;
	$wk_wh_found = 0;
	$wk_company_found = 0;
	$wk_qty_found = 0;
	$wk_status_found = 0;
	$wk_location_found = 0;
	$allowed_status = getAllowedStatus ($Link );
	//list($pick_wh_id, $pick_company_id) =  getOrderWhCompany ($Link, $order );
	$pickOrder  =  getOrderWhCompany ($Link, $order );

	// can fail if no scanned  location reason done above in scan check
	// ssn mismatch or ssn not for the original
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	//$Query .= " and   s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	//$Query .= " and s1.current_qty > 0 ";
	//echo "checkProdforProd:" . $Query;

	$wk_issn_found = checkQuery ($Link, $Query, $location );
	if ($wk_issn_found == 0)
	{
		$wk_reason .= " ISSN not Found for Product in Location";
	}

	// location scanned doesn have any of product
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	$wk_location_found = checkQuery ($Link, $Query, $location );
	if ($wk_location_found == 0)
	{
		$wk_reason .= " None in Location";
	}

	// wh
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$wk_wh_found = checkQuery ($Link, $Query, $location );
	if ($wk_wh_found == 0)
	{
		$wk_reason .= " Wrong WH";
	}

	// company
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$wk_company_found = checkQuery ($Link, $Query, $location );
	if ($wk_company_found == 0)
	{
		$wk_reason .= " Wrong Company";
	}

	// current_qty not >0
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$wk_qty_found = checkQuery ($Link, $Query, $location );
	if ($wk_qty_found == 0)
	{
		$wk_reason .= " Qty < 1";
	}

	// status
	$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	$Query .= " where   s1.prod_id = '" . $prod_no . "'";
	$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
	$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
	$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
	$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	$wk_status_found = checkQuery ($Link, $Query, $location );
	if ($wk_status_found == 0)
	{
		$wk_reason .= " Wrong Status";
	}
	// zone  
	if (($pickOrder['RESTRICT_BY_ZONE']  == "T") and ($pickOrder['ZONE'] != ""))
	{
		$Query = "select first 1 s1.locn_id, s1.current_qty, s1.wh_id "; 
		$Query .= "from issn s1 ";
		$Query .= "join location l3  on s1.wh_id = l3.wh_id and s1.locn_id = l3.locn_id ";
		$Query .= " where   s1.prod_id = '" . $prod_no . "'";
		$Query .= " and s1.wh_id = '" . substr($location,0,2) . "' and s1.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
		$Query .= " and      s1.wh_id = '".$pickOrder['WH_ID'] ."'";
		$Query .= " and   (s1.company_id = '".$pickOrder['COMPANY_ID'] ."'";
		$Query .= "  or   (V4POS('".$pickOrder['ALTERNATE_COMPANYS'] ."',s1.company_id,0,1) > -1)) ";
		$Query .= " and s1.current_qty > 0 ";
		$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
        	$Query .= " and l3.zone_c = '" . $pickOrder['ZONE'] . "'"; 
		$wk_zone_found = checkQuery ($Link, $Query, $location );
		if ($wk_zone_found == 0)
		{
			$wk_reason .= " Wrong Zone";
		}
	}

	// location for issn does not have a locn seq
	return $wk_reason;
}

// =============================================================================================================

if (isset($_COOKIE['LoginUser']))
{
	list($tran_user, $tran_device) = explode("|", $_COOKIE["LoginUser"]);
}
	
$ssn = '';
$label_no = '';
$order = '';
$prod_no = '';
$description = '';
$uom = '';
$order_qty = 0;
$picked_qty = 0;
$required_qty = 0;

if (isset($_POST['label']))
{
	$label_no = $_POST['label'];
}
if (isset($_GET['label']))
{
	$label_no = $_GET['label'];
}
if (isset($_POST['order']))
{
	$order = $_POST['order'];
}
if (isset($_GET['order']))
{
	$order = $_GET['order'];
}
if (isset($_POST['ssn']))
{
	$ssn = $_POST['ssn'];
}
if (isset($_GET['ssn']))
{
	$ssn = $_GET['ssn'];
}
if (isset($_POST['prod']))
{
	$prod_no = $_POST['prod'];
}
if (isset($_GET['prod']))
{
	$prod_no = $_GET['prod'];
}
if (isset($_POST['uom']))
{
	$uom = $_POST['uom'];
}
if (isset($_GET['uom']))
{
	$uom = $_GET['uom'];
}
if (isset($_POST['desc']))
{
	$description = $_POST['desc'];
}
if (isset($_GET['desc']))
{
	$description = $_GET['desc'];
}
if (isset($_POST['required_qty']))
{
	$required_qty = $_POST['required_qty'];
}
if (isset($_GET['required_qty']))
{
	$required_qty = $_GET['required_qty'];
}
if (isset($_POST['location']))
{
	$location = $_POST['location'];
}
if (isset($_GET['location']))
{
	$location = $_GET['location'];
}

// want ssn label desc
$rcount = 0;

//echo "start check locn";
if (!($Link = ibase_pconnect($DBName2, $User, $Password)))
{
	//print("Unable to Connect!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Connect");
	exit();
}
$dbTran = ibase_trans(IBASE_DEFAULT, $Link);

//include 'logme.php';
require_once 'logme.php';
$wk_ok_reason = "";
// allow for param entry for location 

$wk_system_product_by = "";
//include "checkdata.php";
require_once "checkdata.php";
$Query = "select pick_trolley_product_by from control";
if (!($Result = ibase_query($Link, $Query)))
{
	echo("Unable to Read from Control!<BR>\n");
	exit();
}
if ( ($Row = ibase_fetch_row($Result)) ) {
	$wk_system_product_by = $Row[0];
}
//release memory
ibase_free_result($Result);
//echo("location:" . $location);
// check location for a location
if (isset($location))
{
        // trim it
	$location = trim($location);
}
if ($location <> "")
{
	if ($ssn == "")
	{
		// a product - so expect a Location
/*
		$field_type = checkForTypein($location, 'LOCATION' ); 
		if ($field_type != "none")
		{
			// a location
			if ($startposn > 0)
			{
				$wk_realdata = substr($location,$startposn);
				$location = $wk_realdata;
			}
		} else {
			$location = "";
			$wk_ok_reason .= "Invalid Location";
		}
*/
		if ( $wk_system_product_by == 'ISSN') {
			// pick by issn
			$field_type = checkForTypein($location, 'BARCODE' ); 
			//echo ('BARCODE:' . $field_type);
			if ($field_type != "none")
			{
				// an ssn 
				if ($startposn > 0)
				{
					$wk_realdata = substr($location,$startposn);
					$scanned_ssn = $wk_realdata;
				} else {
					$scanned_ssn = $location;
				}
			} else {
				$field_type = checkForTypein($location, 'ALTBARCODE' ); 
				//echo ('ALTBARCODE:' . $field_type);
				if ($field_type != "none")
				{
					// an alternate ssn form
					if ($startposn > 0)
					{
						$wk_realdata = substr($location,$startposn);
						$scanned_ssn = $wk_realdata;
					} else {
						$scanned_ssn = $location;
					}
				} else {
					$location = "";
					//$wk_ok_reason .= "Invalid ISSN";
					$wk_ok_reason .= "Not an ISSN Barcode";
				}
			}
		} else {
			// pick by location
			$field_type = checkForTypein($location, 'LOCATION' ); 
			if ($field_type != "none")
			{
				// a location
				if ($startposn > 0)
				{
					$wk_realdata = substr($location,$startposn);
					$location = $wk_realdata;
				}
			} else {
				$location = "";
				//$wk_ok_reason .= "Invalid Location";
				$wk_ok_reason .= "Not a Location Barcode";
			}
		}
	} else {
		// an ssn
		$field_type = checkForTypein($location, 'BARCODE' ); 
		//echo ('BARCODE:' . $field_type);
		if ($field_type != "none")
		{
			// an ssn 
			if ($startposn > 0)
			{
				$wk_realdata = substr($location,$startposn);
				$scanned_ssn = $wk_realdata;
			} else {
				$scanned_ssn = $location;
			}
		} else {
			$field_type = checkForTypein($location, 'ALTBARCODE' ); 
			//echo ('ALTBARCODE:' . $field_type);
			if ($field_type != "none")
			{
				// an alternate ssn form
				if ($startposn > 0)
				{
					$wk_realdata = substr($location,$startposn);
					$scanned_ssn = $wk_realdata;
				} else {
					$scanned_ssn = $location;
				}
			} else {
				$location = "";
				//$wk_ok_reason .= "Invalid ISSN";
				$wk_ok_reason .= "Not an ISSN Barcode";
			}
		}
	}
}

{
	// save original fields
	$cookiedata = "";
	{
		$cookiedata .= $label_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $order;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $ssn;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $prod_no;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $uom;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $description;
	}
	$cookiedata .= '|';
	{
		$cookiedata .= $required_qty;
	}
	$cookiedata .= '|';
	{
		// if an ssn then must calc the location
		if ($ssn <> "") 
		{
			if (isset($scanned_ssn))
			{
				$Query = "select s1.wh_id || s1.locn_id  "; 
				$Query .= "from issn s1 ";
				$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
				}
				else
				{
					if ( ($Row = ibase_fetch_row($Result)) ) {
						$location  = $Row[0];
					}
				}
				//release memory
				ibase_free_result($Result);
			}
		} else {
			if (isset($scanned_ssn))
			{
				$Query = "select s1.wh_id || s1.locn_id  "; 
				$Query .= "from issn s1 ";
				$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
				}
				else
				{
					if ( ($Row = ibase_fetch_row($Result)) ) {
						$location  = $Row[0];
					}
				}
				//release memory
				ibase_free_result($Result);
			}
		}
		$cookiedata .= $location;
	}
	$cookiedata .= '|';
		// if an ssn then the scanned ssn
		if (isset($scanned_ssn))
		{
			$cookiedata .= $scanned_ssn;
		}
	$cookiedata .= '|';
		// if an ssn then the qty is 1 
		if ($ssn <> "") 
		{
			if (isset($scanned_ssn))
			{
				$Query = "select s1.current_qty  "; 
				$Query .= "from issn s1 ";
				$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
				}
				else
				{
					if ( ($Row = ibase_fetch_row($Result)) ) {
						$wk_current_qty =  $Row[0];
						$cookiedata .= min($wk_current_qty, $required_qty);
					}
				}
				//release memory
				ibase_free_result($Result);
			}
			//$cookiedata .= "1";
		} else {
			if (isset($scanned_ssn))
			{
				$Query = "select s1.current_qty  "; 
				$Query .= "from issn s1 ";
				$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
				if (!($Result = ibase_query($Link, $Query)))
				{
				}
				else
				{
					if ( ($Row = ibase_fetch_row($Result)) ) {
						$wk_current_qty =  $Row[0];
						$cookiedata .= min($wk_current_qty, $required_qty);
					}
				}
				//release memory
				ibase_free_result($Result);
			}
		}
	$cookiedata .= '|';
	//setcookie("BDCSData","$cookiedata", time()+1186400, "/");
	setBDCScookie($Link, $tran_device, "BDCSData", $cookiedata);
}
$Query2 = "select pick_import_ssn_status from control "; 
if (!($Result = ibase_query($Link, $Query2)))
{
	//header("Location pick_Menu.php?message=Unable+to+Read+Control");
	$allowed_status = ",,";
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$allowed_status = $Row[0];
	}
	else
	{
		$allowed_status = ",,";
	}
}

//release memory
ibase_free_result($Result);

$pick_wh_id  = "";
$pick_company_id  = "";
$pick_alternate_companys  = "";
$Query2 = "select wh_id, company_id, alternate_companys from pick_order where pick_order = '" . $order . "' "; 
if (!($Result = ibase_query($Link, $Query2)))
{
}
else
{
	if ( ($Row = ibase_fetch_row($Result)) ) {
		$pick_wh_id  = $Row[0];
		$pick_company_id  = $Row[1];
		$pick_alternate_companys  = $Row[2];
	}
}

//release memory
ibase_free_result($Result);
if (is_null($pick_alternate_companys))
{
	$pick_alternate_companys = $pick_company_id;
}

$wk_my_reason = "";
if ($ssn <> '')
{
	$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
	$Query .= "from issn s1 ";
	if (isset($scanned_ssn))
	{
		$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
	} else {
		$Query .= " where s1.ssn_id = ''";
		// failed ssn check so no data here
	}
	$Query .= " and ( s1.ssn_id = '" . $ssn . "'";
	$Query .= " or    s1.original_ssn = '" . $ssn . "' )";
	$Query .= " and   s1.wh_id = '".$pick_wh_id."'";
	$Query .= " and   (s1.company_id = '".$pick_company_id."'";
	$Query .= "  or   (V4POS('".$pick_alternate_companys ."',s1.company_id,0,1) > -1)) ";
	$Query .= " and s1.current_qty > 0 ";
	$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
	$wk_my_reason =  checkISSNforSSN ($Link, $order, $ssn, $location, $scanned_ssn  );
}
else
{
	if ( $wk_system_product_by == 'ISSN') {
		$Query = "select s1.locn_id, s1.current_qty, s1.wh_id "; 
		$Query .= "from issn s1 ";
		if (isset($scanned_ssn))
		{
			$Query .= " where s1.ssn_id = '" . $scanned_ssn . "'";
		} else {
			$Query .= " where s1.ssn_id = ''";
			// failed ssn check so no data here
		}
		$Query .= " and   s1.prod_id = '" . $prod_no . "'";

		$Query .= " and   s1.wh_id = '".$pick_wh_id."'";
		$Query .= " and   (s1.company_id = '".$pick_company_id."'";
		$Query .= "  or   (V4POS('".$pick_alternate_companys ."',s1.company_id,0,1) > -1)) ";
		$Query .= " and s1.current_qty > 0 ";
		$Query .= " and pos('" . $allowed_status . ",AL," . "',s1.issn_status,0,1) > -1";
		$wk_my_reason =  checkISSNforProd ($Link, $order, $prod_no, $location, $scanned_ssn  );
	} else {
		$Query = "select s3.locn_id, s3.current_qty , s3.wh_id "; 
		$Query .= "from issn s3 ";
		$Query .= " where s3.prod_id = '" . $prod_no . "'";
		$Query .= " and s3.current_qty > 0 ";
		$Query .= " and   s3.wh_id = '".$pick_wh_id."'";
		$Query .= " and   (s3.company_id = '".$pick_company_id."'";
		$Query .= "  or   (V4POS('".$pick_alternate_companys ."',s1.company_id,0,1) > -1)) ";
		$Query .= " and s3.wh_id = '" . substr($location,0,2) . "' and s3.locn_id = '" . substr($location, 2,strlen($location) -2 ) . "'";
		$Query .= " and pos('" . $allowed_status . ",AL," . "',s3.issn_status,0,1) > -1";
		$wk_my_reason =  checkProdforProd ($Link, $order, $prod_no, $location  );
	}
}
/*
//echo($Query);
if (!($Result = ibase_query($Link, $Query)))
{
	//print("Unable to Read Picks!<BR>\n");
	header("Location: pick_Menu.php?message=Unable+to+Read+Picks");
	exit();
}
*/

//echo("location:" . $location);
$location_found = 0;
/*
// Fetch the results from the database.
while ( ($Row = ibase_fetch_row($Result)) ) {
	//echo("<br>row21:" . trim($Row[2].$Row[0]));
	//echo(":location:" . $location);
	//echo("<br>");
	if (trim($Row[2].$Row[0]) == $location)
	{
		// location is valid
		$location_found = 1;
	}
	else 
	{
		//echo ("location mismatch:" . trim($Row[2].$Row[0]) . ":");
	}
}

//release memory
ibase_free_result($Result);
*/
if ($wk_my_reason == "")
{
	$location_found = 1;
}

$wk_ok_reason .= $wk_my_reason;

//commit
ibase_commit($dbTran);

//close
//ibase_close($Link);

//echo "in checkfromlocn";
//echo "location found:" . $location_found;
//echo "ssn:" . $ssn;
{
	if ($location_found == 0)
	{
		header("Location: getfromlocn.php?locnfound=0&reason=" . urlencode($wk_ok_reason));
		//phpinfo();
	}
	else
	{
		if ($ssn <> "")
		{
			//header("Location: getfromssn.php");
			header("Location: transactionOL.php");
		}
		else
		{
			if ( $wk_system_product_by == 'ISSN') {
				header("Location: transactionOL.php");
			} else {
				//header("Location: getfromprod.php");
				header("Location: getfromqty.php");
			}
		}
	}
}
?>
