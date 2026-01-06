<?php

class Minder_Page_FormBuilder_InputMethod {
    const CHECK_BOX = 'CB';
    const COMBO_BOX = 'DDAJAX';
    const DATE_PICKER = 'DP';
    const DROP_DOWN = 'DD';
    const INPUT = 'IN';
    const NONE = 'NONE';
    const RADIO_GROUP = 'RB';
    const READ_ONLY = 'RO';
    const ELEMENT_GROUP = 'GP';
    const GLOBAL_INPUT = 'GI';
    const COMPLEX_DROPDOWN_NAME = 'DL';
    const COMPLEX_DROPDOWN_VALUESLIST = 'dl';

    const WILDCARD_ON = 'ON';
    const WILDCARD_OFF = 'OFF';
    const WILDCARD_RIGHT = 'RIGHT';
    const WILDCARD_LEFT = 'LEFT';
    const WILDCARD_BOTH = 'BOTH';

    const DATE_FROM = 'FROM';
    const DATE_TILL = 'TILL';
    const DATE_DAY  = 'DAY';
    const DATE_TYPE = 'DATE';

    public $inputMethod = 'NONE';
    public $required = false;
    public $cells = 1;
    public $columns = null;
    public $rows = 1;
    public $checkedValue = '';
    public $uncheckedValue = '';
    public $groupName = null;
    public $filters = array();
    public $wildcardType = '';
    public $groupNo = null;
    public $dateType = 'DAY';


    public function isElementGroup() {
        return $this->inputMethod == self::ELEMENT_GROUP;
    }
}