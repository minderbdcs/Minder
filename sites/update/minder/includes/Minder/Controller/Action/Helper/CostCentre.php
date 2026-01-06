<?php

class Minder_Controller_Action_Helper_CostCentre extends Zend_Controller_Action_Helper_Abstract {
    const CODE = "CODE";
    const DESCRIPTION = "DESCRIPTION";

    /**
     * @param array $data
     * @throws Exception
     */
    public function createCostCentre($data) {
        if (empty($data)) {
            return;
        }


        $userid_is=Minder_Controller_Action_Helper_CostCentre::_getMinder()->userId;

        $user_whId = Minder_Controller_Action_Helper_CostCentre::_getMinder()->getSysEquipData($userid_is);
        $user_data = Minder_Controller_Action_Helper_CostCentre::_getMinder()->getSysUserData($userid_is);
        $user_companyId=$user_data['COMPANY_ID'];

        if($user_companyId == ""){
            $arrCompanyDetail = Minder_Controller_Action_Helper_CostCentre::_getMinder()->getUserCompany(null, false);
            $user_companyId = $arrCompanyDetail["COMPANY_ID"];
        }
        
        $data['WH_ID']=$user_whId;
        $data['COMPANY_ID']=$user_companyId;

        $code=$data['CODE'];

        $return=Minder_Controller_Action_Helper_CostCentre::_getMinder()->checkCostCentreExist($code,$user_whId,$user_companyId);

        if(!isset($return)){


                            $sql = "
                                INSERT INTO COST_CENTRE (" . implode(', ', array_keys($data)) . ")
                                VALUES (" . implode(', ', array_fill(0, count($data), '?') ) .")
                            ";

                            if (false === $this->_getMinder()->execSQL($sql, $data)) {
                                throw new Exception('Error creating Cost Center: ' . $this->_getMinder()->lastError);
                            }  
                            else{

                                //throw new Exception('Cost Centre Saved Successfully');
                                    return true;

                            }

        }

        else{

                throw new Exception('Cost Centre Already Exists');

        }


    }

    public function prepareCreateData(Zend_Controller_Request_Abstract $httpRequest) {
        return $httpRequest->getParam('costCentre', array(
            static::CODE            => "",
            static::DESCRIPTION     => ""
        ));
    }

    public function validateCreateData($data, Minder_JSResponse $result = null) {
        $result = is_null($result) ? new Minder_JSResponse() : $result;

        if (empty($data[static::CODE])) {
            $result->addErrors("CODE is empty.");
        } else {
            if (strlen($data[static::CODE]) > 40) {
                $result->addErrors("CODE length should be less then 40.");
            }
        }

        if (empty($data[static::DESCRIPTION])) {
            $result->addErrors("DESCRIPTION is empty.");
        } else {
            if (strlen($data[static::DESCRIPTION]) > 50) {
                $result->addErrors("DESCRIPTION length should be less then 50.");
            }
        }

        return $result;
    }

    protected function _getMinder() {
        return Minder::getInstance();
    }
}