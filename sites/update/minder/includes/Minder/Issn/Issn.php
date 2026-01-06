<?php

/**
 * Class Minder_Issn_Issn
 * @property string ISSN
 * @property string INTO_DATE
 */
class Minder_Issn_Issn extends ArrayObject {
    public function __construct($data = array())
    {
        $defaults = array(
            'ISSN' => '',
            'INTO_DATE' => ''
        );

        parent::__construct(array_merge($defaults, $data), ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function isOverdue($loanPeriod) {
        if (empty($loanPeriod) || empty($this->INTO_DATE)) {
            return false;
        }

        if (!Zend_Date::isDate($this->INTO_DATE, "y-MM-dd HH:mm:ss")) {
            return false;
        }

        $loanExpireDate = new Zend_Date($this->INTO_DATE, "y-MM-dd HH:mm:ss");
        $loanExpireDate->addDay($loanPeriod);

        return $loanExpireDate->isEarlier(new Zend_Date());
    }
}