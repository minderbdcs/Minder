<?php
/**
 * @property
 * @property $recordId;
 * @property $postCode;
 * @property $locality;
 * @property $state;
 * @property $comments;
 * @property $deliveryOffice;
 * @property $preSortIndicator;
 * @property $parcelZone;
 * @property $bspNumber;
 * @property $bspName;
 * @property $category;
 * @property $country;
 * @property $description;
 * @property $depot01;
 * @property $depot02;
 * @property $depot03;
 * @property $depot04;
 * @property $depot05;
 * @property $depot06;
 * @property $depot07;
 * @property $depot08;
 * @property $depot09;
 * @property $depot10;
 */
class PostcodeDepot extends AbstractModel {
    protected $_recordId;
    protected $_postCode;
    protected $_locality;
    protected $_state;
    protected $_comments;
    protected $_deliveryOffice;
    protected $_preSortIndicator;
    protected $_parcelZone;
    protected $_bspNumber;
    protected $_bspName;
    protected $_category;
    protected $_country;
    protected $_description;
    protected $_depot01;
    protected $_depot02;
    protected $_depot03;
    protected $_depot04;
    protected $_depot05;
    protected $_depot06;
    protected $_depot07;
    protected $_depot08;
    protected $_depot09;
    protected $_depot10;

    /**
     * @return boolean
     */
    public function existedRecord()
    {
        return !empty($this->_recordId);
    }

    /**
     * @return PostcodeDepot_NullRecord
     */
    public function getNullObject()
    {
        return new PostcodeDepot_NullRecord();
    }

    /**
     * @param mixed $source
     * @return PostcodeDepot
     */
    public function getNewObject($source)
    {
        return new PostcodeDepot($source);
    }


}