<?php

interface Transaction_V6Interface {
    /**
     * @return string
     */
    public function getWhId();

    /**
     * @return string
     */
    public function getLocnId();

    /**
     * @return string
     */
    public function getObjectId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getDate();

    /**
     * @return string
     */
    public function getReference();

    /**
     * @return string
     */
    public function getQuantity();

    /**
     * @return string
     */
    public function getComplete();

    /**
     * @return string
     */
    public function getErrorText();

    /**
     * @return string
     */
    public function getInstanceId();

    /**
     * @return string
     */
    public function getExported();

    /**
     * @return string
     */
    public function getSubLocation();

    /**
     * @return string
     */
    public function getInputSource();

    /**
     * @return string
     */
    public function getPersonId();

    /**
     * @return string
     */
    public function getDeviceId();

    /**
     * @return string
     */
    public function getProdId();

    /**
     * @return string
     */
    public function getCompanyId();

    /**
     * @return string
     */
    public function getOrderNo();

    /**
     * @return string
     */
    public function getOrderType();

    /**
     * @return string
     */
    public function getOrderSubType();

    /**
     * @return string
     */
    public function getFamily();

}