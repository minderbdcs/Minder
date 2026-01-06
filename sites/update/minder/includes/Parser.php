<?php

class Parser{
    
    
    public $outStr      =   null;
    
    
    private $inStr      =   null;
    
    private $tableName  =   null;
    
    private $fieldName  =   null;
    
    private $error      =   null;
    
    private $endCommand =   null;
    
    
    public function __construct($inStr, $fieldName, $tableName  =   null, $endCommand = 'AND'){
        
        $this->inStr        =   trim($inStr);
        $this->fieldName    =   trim($fieldName);
        $this->endCommand   =   trim($endCommand);
        
        if(!is_null($tableName) && !empty($tableName)){
            $this->tableName    =   trim($tableName);
        }
        
            
    }
    
    public function parse(){
        
    	$outputString = $this->inStr;
    	
        $tableName 	  = trim($this->tableName);
    	$fieldName 	  = trim($this->fieldName);
    	
    	
    	if ($tableName!='' && $tableName != null && $fieldName!='' && $fieldName!= null) {
    		$insertString = $tableName.'.'.$fieldName;
    	} else {
    		$insertString = $tableName.$fieldName;
    	}
    	
    	$outputString = addslashes(trim($outputString));
    	
        // look for AND|OR at the begin  => exception
    	if (preg_match('/^\s*(AND|OR)\s+/i', $outputString)!=0) {
    		throw new Exception('expression must be started by NOT or string , not AND|OR');	
    	}
    	
		// look for AND|NOT|OR at the end	=> exception
    	if (preg_match('/\s+(AND|NOT|OR)\s*$/i', $outputString)!=0) {
    		throw new Exception('expression must be finished by string , not AND|OR|NOT');	
    	}
    	
    	// look for double-operators like AND OR => exception
    	if (preg_match('/\s+(AND|OR|NOT)(\s+)(AND|OR)\s+/i', $outputString, $matches, PREG_OFFSET_CAPTURE)!=0) {
    		$pos = $matches[0][1];
    		throw new Exception("parsing has broken at position $pos");	
    	}
    	
    	// add ' quotes around OPERATORS and add LIKE before values
    	$outputString = preg_replace('/(^|\s+)(OR\s+NOT|AND\s+NOT|OR|AND|NOT)(\s+|$)+/i', "' \$2 LIKE '", $outputString);
    	
    	// if string starts with ' and operator - remove ' 
    	if(preg_match("/^'\s(OR\s+NOT|AND\s+NOT|OR|AND|NOT)\s/i", $outputString )) {
    		$outputString = substr($outputString, 2);
    	}
    	
		//look for NOT at the begin if it isn't  add LIKE ' at the begin		
    	if(!preg_match("/^NOT\s/i", $outputString ) && strlen($outputString)>0) {
    		$outputString = "LIKE '".$outputString;
    	}
    	
    	//add table and filed names  
    	$outputString = preg_replace('/(^|\s)*(NOT\sLIKE|LIKE)\s+/i', "\$1$insertString \$2 ", $outputString);
    	
		// if Input length > 0 add ' at the end     	
    	if(strlen($outputString)>0) {
    		$outputString = $outputString."' ".$this->endCommand . ' ';
    	}
    	
    	return $outputString;

    }

}
?>
