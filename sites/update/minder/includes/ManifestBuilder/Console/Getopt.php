<?php

class ManifestBuilder_Console_Getopt extends Zend_Console_Getopt {
    public function __construct($rules = array(), $argv = null, $getoptConfig = array()) {
        $defaultRules = array(
            'help|h' => 'Show help message.',
            'create-manifest' => 'create manifest for given date (use current date if none given)',
            'carriers=s' => 'CARRIERS list to build manifests for, individual CARRIER_ID\'s should be separated by "|", example: "EPARCEL|POST"',
            'pick-manifest-id=s' => 'manifest id to build',
            'upload-manifest' => 'upload created manifests to server',
            'manifest-dir=s' => 'path to store manifest files',
        );

        $rules = array_merge($defaultRules, $rules);

        parent::__construct($rules, $argv, $getoptConfig);
    }

    public function getUsageMessage()
    {
        return parent::getUsageMessage() . PHP_EOL . 'Supported manifest output formats: AUSTPOST, COURIERP.' . PHP_EOL;
    }


}