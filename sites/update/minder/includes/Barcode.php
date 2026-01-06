<?php

class Barcode
{
	private $__raw_code = null;
	private $__code = null;
	private $__id = null;
	private $__id_length = null;
	private $__type = null; // PROD_ID |
	private $__max_length = null;
	private $__fixed_length = null;
	private $__redirect_url = null;
	
	private $__minder = null;
	
	private $__matchs = null;
    private $__length = null;
    private $__data_id = null;
    private $__rest = null;  

	public function __construct($raw_code, $minder = null, $force_type = null)
	{
		
		
		if (!($minder instanceof Minder)) {
			$minder = new Minder();
		}
		$this->__minder = $minder;
		
		$this->__raw_code = trim($raw_code);
		$this->__code = substr($raw_code, 0, 3);
		$this->__id = substr($raw_code, 3);
		$this->__id_length = strlen($this->__id);

		$params = $minder->getBarcodeScannerParamsByCode($this->__code, $force_type);

		if ($params!==false) {
			if (preg_match('/'.$params['DATA_EXPRESSION'].'/', $this->__id, $matches)) {
				$this->__id = $matches[0];
				$this->__type = $force_type==null?$params['DATA_ID']:$force_type;
				$this->__max_length = $params['MAX_LENGTH'];
				$this->__fixed_length = $params['FIXED_LENGTH']=='T'?true:false;
				$this->__redirect_url = $params['MENU_URL'];
			}
		}

		$this->__findMatches();
		
	}

	public function getType()
	{
		return $this->__type;
	}

	public function getId()
	{
		return $this->__id;
	}

	public function getLength()
	{
		return $this->__id_length;
	}

	public function getRedirectUrl()
	{
		return $this->__redirect_url;
	}

	public function  getCode()
	{
		return $this->__code();
	}
	
	private function __findMatches()
	{
		//getting param table matches
		
		$raw = $this->__raw_code;
		$params = $this->__minder->getPramsTable();
    	
    	
    	$matchs = array();
    	$i=0;

    	foreach ($params as $param)
    	{
			$sym_pref = $param['SYMBOLOGY_PREFIX'];
			$regexps = explode(';', $sym_pref);
			
			foreach ($regexps as $regexp)
			{
				if ($regexp!='')
				{
					if (ereg($regexp, $raw)==1) {

						$code = ereg_replace($regexp, '', $raw);
						$code_length = strlen($code);
						
						if (  ($code_length==$param['MAX_LENGTH'] && $param['FIXED_LENGTH']=='T') 
							|| ($code_length<=$param['MAX_LENGTH'] && $param['FIXED_LENGTH']=='F')
							|| ($param['MAX_LENGTH']==0 && $param['FIXED_LENGTH']=='F') )
						{
							$matchs[$i]['DATA_ID'] = $param['DATA_ID'];
							$matchs[$i]['length'] = strlen($regexp);
							$matchs[$i]['value'] = ereg_replace($regexp, '', $raw);
							$i++;
						}
						
					}
					
				}
				
			}
			
    	}
    	
    	// getting most long

    	$length = 0;
    	
    	foreach ($matchs as $match) {
    		if ($match['length']>$length)
    		{
    			$length = $match['length'];
    			
    			$data_id = $match['DATA_ID'];
    			$rest = $match['value'];
    		}
    	}
    	
    	if ($length==0) {
    			$length = null;
    			$data_id = null;
    			$rest = null;    		
    	}	

		$this->__matchs = $matchs;
    	$this->__length = $length;
    	$this->__data_id = $data_id;
    	$this->__rest = $rest;      	
    	
	}
	
	public function getMatches()
	{
		return $this->__matchs;
	}
	
	public function getData_id()
	{
		return $this->__data_id;
	}

	public function getRest()
	{
		return $this->__rest;
	}
}

?>