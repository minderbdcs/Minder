<?php

/**
* Minder_SysScreen_Model_AddressLabelProvider_Interface provide interface to fetch data needed for Address Label printing.
*/
interface Minder_SysScreen_Model_AddressLabelProvider_Interface
{
    
    /**
    * Returns Address Label data to pass into print request
    * 
    * @param int $rowOffset
    * @param int $itemCountPerPage
    * 
    * @return array - each row should contain data for one Address Label. 
    *                 Important: each row should contain _ADDR_TYPE_ field to find out label format!
    */
    public function selectAddressLabelData($rowOffset, $itemCountPerPage);
}
