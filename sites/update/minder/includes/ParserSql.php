<?php


class ParserSql {

    private $_settledCommands = array('LIKE', 'AND', 'OR', 'NOT');

    private $_parseStr;

    private $_sqlCommands = array();

    private $_sqlValues = array();

    private $_explodeStr = array();

    private $_pos = 0;

    public $errorMsg = null;

    public $lastError = false;

    public $errorStr;

    public $parsedStr;

    private $_fieldName;

    /**
    * @desc constructor
    * @param string $fieldname
    */
    public function __construct($fieldName) {
        $this->_fieldName = $fieldName;
    }
    /**
    * @desc string $parseStr - string to parse
    */
    public function setupStr($parseStr) {
       $this->_parseStr = $parseStr;
       $this->_explodeStr = explode(' ', trim($parseStr));
    }
    /**
    * @desc parse string
    * @param void
    * @return string $parsedStr
    */
    public function parse() {
        $parsedStr = null;

        try {
            // check if first param - command && param = NOT
            if($this->isCommand($this->_explodeStr[$this->_pos]) && $this->eqlCommand($this->_explodeStr[$this->_pos], 'NOT')) {
                $this->_sqlCommands[] =  $this->_fieldName . ' ' . $this->_explodeStr[$this->_pos];
                $this->_sqlValues[]   = '';
                $this->_pos++;

                // check next param - command && param = LIKE
                if($this->isCommand($this->_explodeStr[$this->_pos]) && $this->eqlCommand($this->_explodeStr[$this->_pos], 'LIKE')
                   && ($this->isValue($this->_explodeStr[$this->_pos + 1]))) {
                    $this->_sqlCommands[] =  $this->_explodeStr[$this->_pos];
                    $this->_sqlValues[]   = $this->collectValue($this->_pos+1);

                    $this->_pos++;
                } else {
                    throw new Exception('After NOT must be (LIKE value) expression.');
                }
             // check if first param - command && param = LIKE
            } else {
                    if($this->_pos < count($this->_explodeStr) - 1 && $this->isCommand($this->_explodeStr[$this->_pos]) && 
                       $this->eqlCommand($this->_explodeStr[$this->_pos], 'LIKE') && ($this->isValue($this->_explodeStr[$this->_pos + 1]))) {

                        $this->_sqlCommands[] =  $this->_fieldName . ' ' . $this->_explodeStr[$this->_pos];
                        $this->_sqlValues[]   = $this->collectValue($this->_pos+1);

                        $this->_pos++;
                    } else {
                        throw new Exception("First expression must be LIKE (value) or NOT");
                    }
            }

        } catch(Exception $ex) {
            $this->makeError($ex->getMessage());
        }

        while($this->_pos < count($this->_explodeStr) && (!$this->lastError)) {

            try {
                     // if next command AND or OR
                     if($this->isCommand($this->_explodeStr[$this->_pos]) &&
                        $this->eqlCommand($this->_explodeStr[$this->_pos], array('AND', 'OR')) &&
                        $this->_pos < count($this->_explodeStr)) {

                        $this->_sqlCommands[] = $this->_explodeStr[$this->_pos];
                        $this->_sqlValues[]   = '';
                        $this->_pos++;

                        // if next command NOT
                        if($this->_pos < count($this->_explodeStr) && $this->isCommand($this->_explodeStr[$this->_pos]) && $this->eqlCommand($this->_explodeStr[$this->_pos], 'NOT'))
                        {
                            $this->_sqlCommands[] = $this->_explodeStr[$this->_pos];
                            $this->_sqlValues[]   = '';
                            $this->_pos++;

                             if($this->isCommand($this->_explodeStr[$this->_pos]) && $this->eqlCommand($this->_explodeStr[$this->_pos], 'LIKE') &&
                                     $this->isValue($this->_explodeStr[$this->_pos+1]) && $this->_pos < count($this->_explodeStr)) {

                                     $this->_sqlCommands[] = $this->_fieldName . ' ' . $this->_explodeStr[$this->_pos];
                                     $this->_sqlValues[]   = $this->collectValue($this->_pos+1);

                                     $this->_pos++;
                              } else {
                                throw new Exception('After NOT must be (LIKE value) expression');
                              }

                        // if next command LIKE
                        } elseif($this->_pos < count($this->_explodeStr) && $this->isCommand($this->_explodeStr[$this->_pos]) &&
                                 $this->eqlCommand($this->_explodeStr[$this->_pos], 'LIKE') && $this->isValue($this->_explodeStr[$this->_pos+1])) {

                                 $this->_sqlCommands[] =  $this->_fieldName . ' ' . $this->_explodeStr[$this->_pos];
                                 $this->_sqlValues[]   = $this->collectValue($this->_pos+1);

                                 $this->_pos++;
                        }
                        else {

                            throw new Exception('After AND or OR or NOT must be (LIKE value) expression');
                        }
                     } else {
                        throw new Exception('Next command after expression (value) must be AND or OR');
                     }

            } catch(Exception $ex) {
                $this->makeError($ex->getMessage());
            }

        }


        $this->prepareSqlLine();
    }
    /**
    * @desc method collect value
    * @param int $pos
    * @return $value
    */
    private function collectValue($pos) {
      $value = null;
      $j=0;
      for($i=$pos; $i<count($this->_explodeStr); $i++) {
        if($this->isValue($this->_explodeStr[$i])) {
            $j++;
            $value .= ' ' . $this->_explodeStr[$i];
        } else {
            $this->_pos += $j;
            return sprintf("%s", $this->quateStr(trim($value)));
        }
      }

     $this->_pos += $j;
     return sprintf("%s", $this->quateStr(trim($value)));
    }
    /**
    * @desc added doublequates
    * @param string $inStr
    * @return quated string $outStr
    */
    private function quateStr($inStr) {
        
    	$outStr = $inStr;
        
        if(stripos($inStr, '%') !== false && stripos($inStr, '\'') === false){
        	$outStr = '\'' . $inStr . '\'';
        }
        
        return $outStr;
    }
    /**
    * @desc check is param value or not
    * @param string $value
    * @return boolean
    */
    private function isValue($value) {
        if(isset($value) && !in_array($value, $this->_settledCommands)) {
            return true;
        }
            return false;
    }
    /**
    * @desc check is param command or not
    * @param string $command
    * @return boolean
    */
    private function isCommand($command) {
        if(isset($command) && in_array($command, $this->_settledCommands)) {
            return true;
        }
            return false;
    }
    /**
    * @desc compare to command
    * @param string $command
    * @param string or array $eqlCommand
    * @return boolean
    */
    private function eqlCommand($command, $eqlCommand) {
        $command    = (string)$command;

        if(is_array($eqlCommand)) {
            if(in_array($command, $eqlCommand)) {
                return true;
            }
                return false;
        } else {
            $sqlCommand = (string)$eqlCommand;
            if($command === $sqlCommand) {
                return true;
            }
                return false;
        }
    }
    /**
    * @desc prepare sql line from $sqlCommands and $sqlValues;
    * @param void
    * @return string $sqlCommand
    */
    private function prepareSqlLine() {
        $strSqlCommand = null;
		
        for($i=0; $i<count($this->_sqlCommands); $i++) {
            if(empty($this->_sqlValues[$i])) {
                $strSqlCommand .= $this->_sqlCommands[$i] . ' ';
            } else {
                $strSqlCommand .= $this->_sqlCommands[$i] . ' ' . $this->_sqlValues[$i] . ' ';
            }
        }

        $this->parsedStr =  $strSqlCommand . 'AND ';
    }
    /**
    * @desc show all errors which arose up at parse
    * @param void
    * @return void
    */
    private function makeError($msg) {

        //$this->_explodeStr[$this->_pos-1] = sprintf('<font style="color:red">%s</font>', $this->_explodeStr[$this->_pos-1]);
        $parseStr = implode(' ', $this->_explodeStr);

        $this->errorStr = $parseStr;
        $this->errorMsg = sprintf('<b>Error: <font style="color:red">%s</font></b>', $msg);

        $this->lastError = true;

    }
}

   /*
     $parseStr = 'NOT LIKE value AND NOT LIKE asddd Le AND NOT LIKE %dd ddd ddd%';


     $parseObj = new ParserSql('TEST FILED');
     $parseObj->setupStr($parseStr);
     $parsedStr = $parseObj->parse();

     if(!$parseObj->errorMsg) {
        echo $parseObj->parsedStr;
     } else {
        echo '<br/>' . $parseObj->errorMsg;
        echo '<br/>' . $parseObj->errorStr;
     }
   */
?>