<?php

class AustpostManifest_Console_Getopt extends Zend_Console_Getopt {
    protected $dateFormat = 'Y-m-d';
    
    public function __construct($rules = array(), $argv = null, $getoptConfig = array()) {
        $defaultRules = array(
            'help|h' => 'Show help message.',
            'create-manifest' => 'create manifest for given date (use current date if none given)',
            'carriers=s' => 'CARRIERS list to biuld manifests for, individual CARRIERS_IS should be separated by "|", example: "EPARCEL|POST"',
            'pick-manifest-id=s' => 'manifest id to build',
            'upload-manifest' => 'upload created manifests to server',
            'manifest-dir=s' => 'path to store manifest files'
        );
        
        $rules = array_merge($defaultRules, $rules);
        
        parent::__construct($rules, $argv, $getoptConfig);
    }
    
    public function getDateFormat() {
        return $this->dateFormat;
    }
}
