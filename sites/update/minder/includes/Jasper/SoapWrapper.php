<?php

//require_once('SOAP/Client.php');


/* ================================= Client Defines ======================================================================= */
	
	define("TYPE_FOLDER","folder");
	define("TYPE_REPORTUNIT","reportUnit");
	define("TYPE_DATASOURCE","datasource");
	define("TYPE_DATASOURCE_JDBC","jdbc");
	define("TYPE_DATASOURCE_JNDI","jndi");
	define("TYPE_DATASOURCE_BEAN","bean");
	define("TYPE_IMAGE","img");
	define("TYPE_FONT","font");
	define("TYPE_JRXML","jrxml");
	define("TYPE_CLASS_JAR","jar");
	define("TYPE_RESOURCE_BUNDLE","prop");
	define("TYPE_REFERENCE","reference");
	define("TYPE_INPUT_CONTROL","inputControl");
	define("TYPE_DATA_TYPE","dataType");
	define("TYPE_OLAP_MONDRIAN_CONNECTION","olapMondrianCon");
	define("TYPE_OLAP_XMLA_CONNECTION","olapXmlaCon");
	define("TYPE_MONDRIAN_SCHEMA","olapMondrianSchema");
	define("TYPE_XMLA_CONNTCTION","xmlaConntction");
	define("TYPE_UNKNOW","unknow");
	define("TYPE_LOV","lov"); // List of values...
	define("TYPE_QUERY","query"); // List of values...
	
	/**
	 * These constants are copied here from DataType for facility
	 */
	define("DT_TYPE_TEXT",1);
	define("DT_TYPE_NUMBER",2);
	define("DT_TYPE_DATE",3);
	define("DT_TYPE_DATE_TIME",4); 
	
	/**
	 * These constants are copied here from InputControl for facility
	 */
	define("IC_TYPE_BOOLEAN",1);
	define("IC_TYPE_SINGLE_VALUE",2);
	define("IC_TYPE_SINGLE_SELECT_LIST_OF_VALUES",3);
	define("IC_TYPE_SINGLE_SELECT_QUERY",4);
	define("IC_TYPE_MULTI_VALUE",5);
	define("IC_TYPE_MULTI_SELECT_LIST_OF_VALUES",6);
	define("IC_TYPE_MULTI_SELECT_QUERY",7);
	
	
	define("PROP_VERSION","PROP_VERSION");
	define("PROP_PARENT_FOLDER","PROP_PARENT_FOLDER");
	define("PROP_RESOURCE_TYPE","PROP_RESOURCE_TYPE");
	define("PROP_CREATION_DATE","PROP_CREATION_DATE");
	
	// File resource properties
	define("PROP_FILERESOURCE_HAS_DATA","PROP_HAS_DATA");
	define("PROP_FILERESOURCE_IS_REFERENCE","PROP_IS_REFERENCE");
	define("PROP_FILERESOURCE_REFERENCE_URI","PROP_REFERENCE_URI");
	define("PROP_FILERESOURCE_WSTYPE","PROP_WSTYPE");
	
	// Datasource properties
	define("PROP_DATASOURCE_DRIVER_CLASS","PROP_DATASOURCE_DRIVER_CLASS");
	define("PROP_DATASOURCE_CONNECTION_URL","PROP_DATASOURCE_CONNECTION_URL");
	define("PROP_DATASOURCE_USERNAME","PROP_DATASOURCE_USERNAME");
	define("PROP_DATASOURCE_PASSWORD","PROP_DATASOURCE_PASSWORD");
	define("PROP_DATASOURCE_JNDI_NAME","PROP_DATASOURCE_JNDI_NAME");
	define("PROP_DATASOURCE_BEAN_NAME","PROP_DATASOURCE_BEAN_NAME");
	define("PROP_DATASOURCE_BEAN_METHOD","PROP_DATASOURCE_BEAN_METHOD");
	
	
	// ReportUnit resource properties
	define("PROP_RU_DATASOURCE_TYPE","PROP_RU_DATASOURCE_TYPE");
	define("PROP_RU_IS_MAIN_REPORT","PROP_RU_IS_MAIN_REPORT");
	
	// DataType resource properties
	define("PROP_DATATYPE_STRICT_MAX","PROP_DATATYPE_STRICT_MAX");
	define("PROP_DATATYPE_STRICT_MIN","PROP_DATATYPE_STRICT_MIN");
	define("PROP_DATATYPE_MIN_VALUE","PROP_DATATYPE_MIN_VALUE");
	define("PROP_DATATYPE_MAX_VALUE","PROP_DATATYPE_MAX_VALUE");
	define("PROP_DATATYPE_PATTERN","PROP_DATATYPE_PATTERN");
	define("PROP_DATATYPE_TYPE","PROP_DATATYPE_TYPE");
	
	 // ListOfValues resource properties
	define("PROP_LOV","PROP_LOV");
	define("PROP_LOV_LABEL","PROP_LOV_LABEL");
	define("PROP_LOV_VALUE","PROP_LOV_VALUE");
	
	
	// InputControl resource properties
	define("PROP_INPUTCONTROL_TYPE","PROP_INPUTCONTROL_TYPE");
	define("PROP_INPUTCONTROL_IS_MANDATORY","PROP_INPUTCONTROL_IS_MANDATORY");
	define("PROP_INPUTCONTROL_IS_READONLY","PROP_INPUTCONTROL_IS_READONLY");
	
	// SQL resource properties
	define("PROP_QUERY","PROP_QUERY");
	define("PROP_QUERY_VISIBLE_COLUMNS","PROP_QUERY_VISIBLE_COLUMNS");
	define("PROP_QUERY_VISIBLE_COLUMN_NAME","PROP_QUERY_VISIBLE_COLUMN_NAME");
	define("PROP_QUERY_VALUE_COLUMN","PROP_QUERY_VALUE_COLUMN");
	define("PROP_QUERY_LANGUAGE","PROP_QUERY_LANGUAGE");
	
	
	// SQL resource properties
	define("PROP_QUERY_DATA","PROP_QUERY_DATA");
	define("PROP_QUERY_DATA_ROW","PROP_QUERY_DATA_ROW");
	define("PROP_QUERY_DATA_ROW_COLUMN","PROP_QUERY_DATA_ROW_COLUMN");
	
	
	define("MODIFY_REPORTUNIT","MODIFY_REPORTUNIT_URI");
	define("CREATE_REPORTUNIT","CREATE_REPORTUNIT_BOOLEAN");
	define("LIST_DATASOURCES","LIST_DATASOURCES");
	define("IC_GET_QUERY_DATA","IC_GET_QUERY_DATA");
	    
	define("VALUE_TRUE","true");
	define("VALUE_FALSE","false");
	    
	define("RUN_OUTPUT_FORMAT","RUN_OUTPUT_FORMAT");
	define("RUN_OUTPUT_FORMAT_PDF","PDF");
	define("RUN_OUTPUT_FORMAT_JRPRINT","JRPRINT");
	define("RUN_OUTPUT_FORMAT_HTML","HTML");
	define("RUN_OUTPUT_FORMAT_XLS","XLS");
	define("RUN_OUTPUT_FORMAT_XML","XML");
	define("RUN_OUTPUT_FORMAT_CSV","CSV");
	define("RUN_OUTPUT_FORMAT_RTF","RTF");
	define("RUN_OUTPUT_IMAGES_URI","IMAGES_URI");
	define("RUN_OUTPUT_PAGE","PAGE");

/* ================================= End of Client Defines  ======================================================================= */

class Jasper_SoapWrapper
{

    private $_minder;

    /**
     * Allow console output
     *
     * @var boolean
     */
    private $_mode;

    private $_userid;
    private $_deviceid;
    private $_ip;

    public $lastError;
    public $lastErrorCode;
    public $_soapClient;
    public static $client;
    private $webservices_uri;
    private $namespace;
    private $jasperUserId;
    private $jasperPassword;
    private $jasperLoggedIn;

    //public function __construct( $userid, $deviceid, $ip = '127.0.0.1', $silentmode = false)
    public function __construct( $userid, $deviceid, $uri = "http://127.0.0.1:8080/jasperserver/services/repository" , $ip = '127.0.0.1', $silentmode = false)
    {
        $this->_minder      = Minder::getInstance();
        $this->_mode        = $silentmode;

        $this->_userid      = $this->_minder->userId   = $userid;
        $this->_deviceid    = $this->_minder->deviceId = $deviceid;
        $this->_ip          = $this->_minder->ip       = $ip;
        $this->_minder->silent  = $silentmode;

	//$this->webservices_uri = "http://127.0.0.1:8080/jasperserver/services/repository";
	if ($uri == '') {
		$this->webservices_uri = "http://127.0.0.1:8080/jasperserver/services/repository";
	} else {
		$this->webservices_uri = $uri;
	}
	// want to read the value from the options table for group_code JASPER code URI
	$this->namespace = "http://www.jaspersoft.com/namespaces/php";

	$this->jasperUserId = "jasperadmin" ;
	// want to read the value from the options table for group_code JASPER code USER
	$this->jasperPassword = "jasperadmin" ;
	// want to read the value from the options table for group_code JASPER code PASSWORD
	$this->jasperLoggedIn = False;

    }

    private function msg($output)
    {
        $log = Zend_Registry::get('logger');
        $log->info(__FUNCTION__);
        if (!$this->_mode) {
            echo date('Y-m-d H:i:s', time()) . ': ' . $output . PHP_EOL;
        }
    }

    public function __destruct()
    {
    }

    /**
     * convert date from Netsuite to Minder
     *
     * @param string $date
     * @return string|false
     */
    private function __dateConvert($date, $format = 'Y-m-d H:i:s') {
        return ($date ? date($format, strtotime($date)) : false);
    }


/* ================================= Client Functions  ======================================================================= */
	// ws_checkUsername try to list a void URL. If no WS error occurs, the credentials are fine
	function ws_checkUsername($username, $password)
	{
		$connection_params = array("user" => $username, "pass" => $password, 'timeout' => 30);
		//$info = new SOAP_client($GLOBALS["webservices_uri"], false, false, $connection_params);
		$info = new SOAP_client($this->webservices_uri, false, false, $connection_params);
		
		$op_xml = "<request operationName=\"list\"><resourceDescriptor name=\"\" wsType=\"folder\" uriString=\"\" isNew=\"false\">".
		"<label></label></resourceDescriptor></request>";
		
		$params = array("request" => $op_xml );
		//$response = $info->call("list",$params,array('namespace' => $GLOBALS["namespace"]));
		$response = $info->call("list",$params,array('namespace' => $this->namespace));
		
		return $response;
	}
	
	function ws_list($uri, $args = array())
	{
		//global $_SESSION;
		
		//$connection_params = array("user" => $_SESSION["username"], "pass" => $_SESSION["password"]);
		$connection_params = array("user" => $this->jasperUserId, "pass" => $this->jasperPassword);
		//$info = new SOAP_client($GLOBALS["webservices_uri"], false, false, $connection_params);
		$info = new SOAP_client($this->webservices_uri, false, false, $connection_params);
		
		$op_xml = "<request operationName=\"list\">";
		
		if (is_array ($args))
		{
			$keys = array_keys($args);
			foreach ($keys AS $key)
			{
				$op_xml .="<argument name=\"$key\">".$args[$key]."</argument>"; 	
			}
		}
		
		$op_xml .="<resourceDescriptor name=\"$uri\" wsType=\"folder\" uriString=\"$uri\" isNew=\"false\">".
		"<label></label></resourceDescriptor></request>";
		
		$params = array("request" => $op_xml );
		//$response = $info->call("list",$params,array('namespace' => $GLOBALS["namespace"]));
		$response = $info->call("list",$params,array('namespace' => $this->namespace));
		
		return $response;
	}
	
	function ws_get($uri, $args = array())
	{
		//global $_SESSION;
		
		//$connection_params = array("user" => $_SESSION["username"], "pass" => $_SESSION["password"]);
		$connection_params = array("user" => $this->jasperUserId, "pass" => $this->jasperPassword);
		//$info = new SOAP_client($GLOBALS["webservices_uri"], false, false, $connection_params);
		$info = new SOAP_client($this->webservices_uri, false, false, $connection_params);
		
		$op_xml = "<request operationName=\"get\">";
		
		if (is_array ($args))
		{
			$keys = array_keys($args);
			foreach ($keys AS $key)
			{
				$op_xml .="<argument name=\"$key\">".$args[$key]."</argument>"; 	
			}
		}
		
		$op_xml .= "<resourceDescriptor name=\"$uri\" wsType=\"reportUnit\" uriString=\"$uri\" isNew=\"false\">".
		"<label></label></resourceDescriptor></request>";
		
		$params = array("request" => $op_xml );
		//$response = $info->call("get",$params,array('namespace' => $GLOBALS["namespace"]));
		$response = $info->call("get",$params,array('namespace' => $this->namespace));
		
		return $response;
	}
	
	
	
	
	function ws_runReport($uri, $report_params, $output_params, &$attachments )
	{
		//global $_SESSION;
		$max_execution_time = 120; // 2 mins.
		
		//$connection_params = array("user" => $_SESSION["username"], "pass" => $_SESSION["password"], "timeout" => $max_execution_time);
		$connection_params = array("user" => $this->jasperUserId, "pass" => $this->jasperPassword, "timeout" => $max_execution_time);
		//$info = new SOAP_client($GLOBALS["webservices_uri"], false, false, $connection_params);
		$info = new SOAP_client($this->webservices_uri, false, false, $connection_params);
	
	
	/*
	//$v =  new SOAP_Attachment('test','application/octet',"c:\client_file.png"); 
	//$methodValue = new SOAP_Value('request', 'this is my request', array($v)); 
	//$av=array($v); 
	*/

		$op_xml = "<request operationName=\"runReport\">";
		
		if (is_array ($output_params))
		{
			$keys = array_keys($output_params);
			foreach ($keys AS $key)
			{
				$op_xml .="<argument name=\"$key\">".$output_params[$key]."</argument>\n"; 	
			}
		}
		
		$op_xml .="<argument name=\"USE_DIME_ATTACHMENTS\"><![CDATA[1]]></argument>";
		
		$op_xml .="<resourceDescriptor name=\"\" wsType=\"reportUnit\" uriString=\"$uri\" isNew=\"false\">".
		"<label></label>";
		
		
		// Add parameters...
		if (is_array ($report_params))
		{
			$keys = array_keys($report_params);
			foreach ($keys AS $key)
			{
				$op_xml .="<parameter name=\"$key\"><![CDATA[".$report_params[$key]."]]></parameter>"; 	
			}
		}
		
		$op_xml .="</resourceDescriptor></request>";
		
		
		$params = array("request" => $op_xml );
	
		$response = $info->call("runReport",$params,array('namespace' => "http://www.jaspersoft.com/client")); 

		$attachments = $info->_soap_transport->attachments;
		
		return $response;
	}
	
	
	
	// ********** XML related functions *******************
	function getOperationResult($operationResult)
	{
		$domDocument = new DOMDocument();
	 	$domDocument->loadXML($operationResult);
	 	
	 	$operationResultValues = array();
	 	
	 	foreach( $domDocument->childNodes AS $ChildNode )
	   	{
	       		if ( $ChildNode->nodeName != '#text' )
	       		{
	           		
	           		if ($ChildNode->nodeName == "operationResult")
	           		{
	           			foreach( $ChildNode->childNodes AS $ChildChildNode )
	   				{
	   					
	   					if ( $ChildChildNode->nodeName == 'returnCode' )
	       					{	
	       						$operationResultValues['returnCode'] = $ChildChildNode->nodeValue;
	           				}
	           				else if ( $ChildChildNode->nodeName == 'returnMessage' )
	       					{	
	       						$operationResultValues['returnMessage'] = $ChildChildNode->nodeValue;
	           				}
	           			}
	           		}
	           	}
	        }
	        
	        return $operationResultValues;
	}
	
	
	function getResourceDescriptors($operationResult)
	{
		$domDocument = new DOMDocument();
	 	$domDocument->loadXML($operationResult);
	 	
	 	$folders = array();
	 	$count = 0;
	 	
	 	foreach( $domDocument->childNodes AS $ChildNode )
	   	{
	       		if ( $ChildNode->nodeName != '#text' )
	       		{
	           		
	           		if ($ChildNode->nodeName == "operationResult")
	           		{
	           			foreach( $ChildNode->childNodes AS $ChildChildNode )
	   				{
	   					
	   					if ( $ChildChildNode->nodeName == 'resourceDescriptor' )
	       					{	
	       						$resourceDescriptor = $this->readResourceDescriptor($ChildChildNode);
	   						$folders[ $count ] = $resourceDescriptor;
	           					$count++;
	           				}
	           			}
	           		}
	           		
	       		}
	   	}
	   	
	   	return $folders;
	}
	
	function readResourceDescriptor($node)
	{
		$resourceDescriptor = array();
		
		$resourceDescriptor['name'] = $node->getAttributeNode("name")->value;
	        $resourceDescriptor['uri'] =  $node->getAttributeNode("uriString")->value;
	        $resourceDescriptor['type'] = $node->getAttributeNode("wsType")->value;
		
		$resourceProperties = array();
		$subResources = array();
		$parameters = array();
		
		// Read subelements...
		foreach( $node->childNodes AS $ChildNode )
	   	{
	   		if ( $ChildNode->nodeName == 'label' )
			{
				$resourceDescriptor['label'] = 	$ChildNode->nodeValue;
			}
			else if ( $ChildNode->nodeName == 'description' )
			{
				$resourceDescriptor['description'] = 	$ChildNode->nodeValue;
			}
			else if ( $ChildNode->nodeName == 'creationDate' )
			{
				$resourceDescriptor['creationDate'] = 	$ChildNode->nodeValue / 1000;
			}
			else if ( $ChildNode->nodeName == 'resourceProperty' )
			{
				//$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
				// read properties...
				$resourceProperty = $this->addReadResourceProperty($ChildNode );
				$resourceProperties[ $resourceProperty["name"] ] = $resourceProperty;
			}
			else if ( $ChildNode->nodeName == 'resourceDescriptor' )
			{
				array_push( $subResources, $this->readResourceDescriptor($ChildNode));
			}
			else if ( $ChildNode->nodeName == 'parameter' )
			{
				$parameters[ $ChildNode->getAttributeNode("name")->value ] =  $ChildNode->nodeValue;
			}
		}
		
		$resourceDescriptor['properties'] = $resourceProperties;
		$resourceDescriptor['resources'] = $subResources;
		$resourceDescriptor['parameters'] = $parameters;
		
		
		return $resourceDescriptor;
	}
	
	function addReadResourceProperty($node)
	{
		$resourceProperty = array();
		
		$resourceProperty['name'] = $node->getAttributeNode("name")->value;
	        
		$resourceProperties = array();
		
		// Read subelements...
		foreach( $node->childNodes AS $ChildNode )
	   	{
	   		if ( $ChildNode->nodeName == 'value' )
			{
				$resourceProperty['value'] = $ChildNode->nodeValue;
			}
			else if ( $ChildNode->nodeName == 'resourceProperty' )
			{
				//$resourceDescriptor['resourceProperty'] = $ChildChildNode->nodeValue;
				// read properties...
				array_push( $resourceProperties, $this->addReadResourceProperty($ChildNode ) );
			}
		}
		
		$resourceProperty['properties'] = $resourceProperties;
		
		return $resourceProperty;
	}
	
	
	// Sample to put something on the server
	function ws_put()
	{
		//global $_SESSION;
		
		//$connection_params = array("user" => $_SESSION["username"], "pass" => $_SESSION["password"]);
		$connection_params = array("user" => $this->jasperUserId, "pass" => $this->jasperPassword);
		
		$fh = fopen("c:\\myimage.gif", "rb");
		$data = fread($fh, filesize("c:\\myimage.gif"));
		fclose($fh);

		$attachment = array("body"=>$data, "content_type"=>"application/octet-stream", "cid"=>"123456");
		
		//$info = new SOAP_client($GLOBALS["webservices_uri"], false, false, $connection_params);
		$info = new SOAP_client($this->webservices_uri, false, false, $connection_params);
		$info->_options['attachments'] = 'Dime';
		$info->_attachments = array( $attachment );
		
		$op_xml = "<request operationName=\"put\">";
		$op_xml .="<resourceDescriptor name=\"JRLogo3\" wsType=\"img\" uriString=\"/images/JRLogo3\" isNew=\"true\"><label>JR logo PHP</label>";
		$op_xml .="<description>JR logo</description><resourceProperty name=\"PROP_HAS_DATA\"> <value><![CDATA[true]]></value></resourceProperty>";
		$op_xml .="<resourceProperty name=\"PROP_PARENT_FOLDER\"><value><![CDATA[/images]]></value></resourceProperty></resourceDescriptor></request>";
		
		$params = array("request" => $op_xml);
		//$response = $info->call("put",$params,array('namespace' => $GLOBALS["namespace"]));
		$response = $info->call("put",$params,array('namespace' => $this->namespace));
		
		return $response;

	}
	
/* ============================================= End of Client ============================================================ */
/* ============================================= Login ============================================================ */

	// login to Jasper Soap Server
public	function jasperLogin()
	{
		$loginResult = False;
                $this->lastError = '';
		// get the user and password from options records
  		//$username = $_POST['username'];
  		$username = $this->jasperUserId;
		//$password = $_POST['password'];
  		$password = $this->jasperPassword;

   		if ($username != '')
   		{
        		//$result = ws_checkUsername($username, $password);
        		$result = $this->ws_checkUsername($username, $password);
        		if (get_class($result) == 'SOAP_Fault')
        		{
            			$errorMessage = $result->getFault()->faultstring;
				$loginResult = False;
				$this->jasperLoggedIn = False;
                    		//$this->addError($errorMessage);
                                $this->lastError = $errorMessage;
        		}
        		else
        		{
				$loginResult = True;
				$this->jasperLoggedIn = True;
                    		//$this->addMessage('Logged in to Jasper');
        		}
   		}
		return $loginResult;
	}
/* ============================================= End of Login ============================================================ */
/* ============================================= Execute Report ========================================================== */

	// run a report  with paramets passed via an array
public	function jasperExecuteReport( $reportParams = array())
	{
                $this->lastError = '';
		// pass the reports main uri
		// the list of parameters and values in an array
		// the format for output
		// all these come from the reports table for the report
		// and possibly the page
		 //if ($_SESSION["username"] == '')
		 if (!$this->jasperLoggedIn)
		 {
		 	$this->jasperLogin();
		 }
		 
		 // 1 Get the ReoportUnit ResourceDescriptor...
		 $currentUri = "/";
		 
		 //if ($_GET['uri'] != '')
		 //{
		 //	$currentUri = $_GET['uri'];
		 //}
		 if ($reportParams['uri'] != '')
		 {
		 	$currentUri = urldecode($reportParams['uri']);
		 }
		 
		 //$result = ws_get($currentUri);
		 $result = $this->ws_get($currentUri);
		 if (get_class($result) == 'SOAP_Fault')
		 {
		 	$errorMessage = $result->getFault()->faultstring;
		 	
		 	//echo $errorMessage;
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
		 	//exit();
		 	return False;
		 }
		 else
		 {
		 	$folders = $this->getResourceDescriptors($result);
		 }
		 
		 if (count($folders) != 1 || $folders[0]['type'] != 'reportUnit')
		 {
		 	// echo "<H1>Invalid RU ($currentUri)</H1>";
		 	// echo "<pre>$result</pre>";
		 	$errorMessage = 'Invalid RU (' . $currentUri . ')';
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
		 	// exit(); 
		 	return False;
		 }
		
		 $reportUnit = $folders[0];
		 
		 // 2. Prepare the parameters array looking in the $_GET for params
		 // starting with PARAM_ ...
		 //
		 
		 $report_params = array();
		 
		 //$moveToPage = "executeReport.php?uri=$currentUri";
		 
		 //foreach (array_keys($_GET) AS $param_name)
		 foreach (array_keys($reportParams) AS $param_name)
		 {
		 	if (strncmp("PARAM_", $param_name,6) == 0)
		 	{
		 		//$report_params[substr($param_name,6)] = $_GET[$param_name];
		 		$report_params[substr($param_name,6)] = $reportParams[$param_name];
		 		
		 	}
		 	
		 	/* 
                        if ($param_name != "page" && $param_name != "uri")
		 	{
		 		//$moveToPage .= "&".urlencode($param_name)."=". urlencode($_GET[$param_name]);	
		 		$moveToPage .= "&".urlencode($param_name)."=". urlencode($reportParams[$param_name]);	
		 	}
                        */
		}
		 
		//$moveToPage .="&page=";
		 
		 // 3. Execute the report
		 $output_params = array();
		 //$output_params[RUN_OUTPUT_FORMAT] = $_GET['format'];
		 $output_params[RUN_OUTPUT_FORMAT] = $reportParams['format'];
		 //if ( $_GET['format'] == RUN_OUTPUT_FORMAT_HTML)
		 if ( $reportParams['format'] == RUN_OUTPUT_FORMAT_HTML)
		 {
		     $page = 0;
		     //if ($_GET['page'] != '') $page = $_GET['page'];
		     if ($reportParams['page'] != '') $page = $reportParams['page'];
		     $output_params[RUN_OUTPUT_PAGE] = $page;
		 }
		 
		 //$result = ws_runReport($currentUri, $report_params,  $output_params, $attachments);
		 $result = $this->ws_runReport($currentUri, $report_params,  $output_params, $attachments);
		 
		 
		 
		// 4. 
		if (get_class($result) == 'SOAP_Fault')
		 {
		 	$errorMessage = $result->getFault()->faultstring;
		 	
		 	//echo $errorMessage;
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
		 	//exit();
		 	return False;
		 }
		 
		 
		$operationResult = $this->getOperationResult($result);
		 
		 if ($operationResult['returnCode'] != '0')
		 {
		 	//echo "Error executing the report:<br><font color=\"red\">".$operationResult['returnMessage']."</font>";		
		 	$errorMessage =  "Error executing the report:".$operationResult['returnMessage'];		
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
		 	//exit();
		 	return False;
		 }
		 
		 if (is_array($attachments))
		 {
		 	//if ($_GET['format'] == RUN_OUTPUT_FORMAT_PDF)
		 	if ($reportParams['format'] == RUN_OUTPUT_FORMAT_PDF)
		 	{
		 		//header ( "Content-type: application/pdf" );
		 		//echo( $attachments["cid:report"]);
		 		return ($attachments["cid:report"]);
		 	}
		 	//else if ($_GET['format'] == RUN_OUTPUT_FORMAT_HTML)
/*
		 	else if ($reportParams['format'] == RUN_OUTPUT_FORMAT_HTML)
		 	{
		 		// 1. Save attachments....
		 		
		 		// 2. Print the report....
		 		
		 		
		 		//header ( "Content-type: text/html");
		 		
		 		foreach (array_keys($attachments) as $key)
		 		{
		 			if ($key != "cid:report")
		 			{
		 				$f = fopen("images/".substr($key,4),"w");
		 				fwrite($f, $attachments[$key]);
		 				fclose($f);
		 			}
		 		}
		 		
		 		echo "<center>";
		 		
		 		$prevpage = ($page > 0) ? $page-1 : 0;
		 		$nextpage = $page+1;
		 		
		 		echo "<a href=\"".$moveToPage.$prevpage."\">Prev page</a> | <a href=\"".$moveToPage.$nextpage."\">Next page</a>";
		 		echo "</center><hr>";
		 		
		 		echo $attachments["cid:report"];
		 		
		 		//print_r(array_keys($attachments));
		 		
		 	}
*/
		 	//else if ($_GET['format'] == RUN_OUTPUT_FORMAT_XLS)
		 	else if ($reportParams['format'] == RUN_OUTPUT_FORMAT_XLS)
		 	{
		  		//header ( 'Content-type: application/xls' );
		  		//header ( 'Content-Disposition: attachment; filename="report.xls"');
		  		//echo( $attachments["cid:report"]);
		 		return ($attachments["cid:report"]);
		  		
			}  	
		
			//exit(); 	
			$errorMessage = "Unknown Format!";
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
			return False;
		 } else {
			//echo "No attachment found!";
			$errorMessage = "No attachment found!";
                   	//$this->addError($errorMessage);
                        $this->lastError = $errorMessage;
			return False;
		 }

	} 
/* ============================================= End of Execute Report =================================================== */


}
