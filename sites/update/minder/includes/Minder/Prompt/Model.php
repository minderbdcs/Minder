<?php

class Minder_Prompt_Model {
    public $code;
    public $prompt = '';
    public $type = '';

    function __construct($code, $prompt, $type)
    {
        $this->code = $code;
        $this->prompt = $prompt;
        $this->type = $type;
    }


}