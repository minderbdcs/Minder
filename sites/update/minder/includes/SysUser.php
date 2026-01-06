<?php

class SysUser
{
    public $userId;
    public $personId;
    public $companyId;
    public $divisionId;
    public $deviceId;
    public $locPermLevel;
    public $prodPermLevel;
    public $taskPermLevel;
    public $loginDate;
    public $passWord;
    public $passWordDate;
    public $pwExpiryDate;
    public $lastUpdateDate;
    public $updateUserId;
    public $sysAdmin;
    public $zoneC;
    public $editable;
    public $userType;
    public $saleManager;
    public $creditManager;
    public $defaultWhId;
    public $inventoryOperator;
    public $pickSequence;
    public $pickDirection;
    public $defaultOrderSoldFrom;
    public $defaultOrderSoldTo;
    public $stockAdjust;
    public $statusAdjustIssn;
    public $statusAdjustPickItem;
    public $statusAdjustPickOrder;
    public $statusAdjustPurchaseOrder;
    public $statusAdjustPoLine;
    public $userCategory;
    public $whmStartMenu;
    public $minderStartMenu;
    public $whManager;
    
    /**
    * @param string $userId
    * @return SysUser
    */
    public static function getSysUser($userId) {
        $sysUsers = array();
        if (Zend_Registry::isRegistered('SysUsers')) {
            Zend_Registry::get('SysUsers');
        }
        
        $sysUserObject = null;
        if (isset($sysUsers[$userId])) {
            $sysUserObject = $sysUsers[$userId];
        }
        else {
            $sysUserObject = new SysUser();
            $sysUserObject->userId = $userId;
            $sysUsers[$userId] = $sysUserObject;
        }
        
        Zend_Registry::set('SysUsers', $sysUsers);
        
        return $sysUserObject;
    }
    
    public function readFields() {
        $minder = Minder::getInstance();
        
        $sql = '
            SELECT 
                PERSON_ID,
                COMPANY_ID,
                DIVISION_ID,
                DEVICE_ID,
                LOC_PERM_LEVEL,
                PROD_PERM_LEVEL,
                TASK_PERM_LEVEL,
                LOGIN_DATE,
                PASS_WORD,
                PASS_WORD_DATE,
                PW_EXPIRY_DATE,
                LAST_UPDATE_DATE,
                UPDATE_USER_ID,
                SYS_ADMIN,
                ZONE_C,
                EDITABLE,
                USER_TYPE,
                SALE_MANAGER,
                CREDIT_MANAGER,
                DEFAULT_WH_ID,
                INVENTORY_OPERATOR,
                PICK_SEQUENCE,
                PICK_DIRECTION,
                DEFAULT_ORDER_SOLD_FROM,
                DEFAULT_ORDER_SOLD_TO,
                STOCK_ADJUST,
                STATUS_ADJUST_ISSN,
                STATUS_ADJUST_PICK_ITEM,
                STATUS_ADJUST_PICK_ORDER,
                STATUS_ADJUST_PURCHASE_ORDER,
                STATUS_ADJUST_PO_LINE,
                USER_CATEGORY,
                WHM_START_MENU,
                MINDER_START_MENU,
                WH_MANAGER
            FROM 
                SYS_USER 
            WHERE 
                USER_ID = ?
        ';
        
        if (false === ($result = $minder->fetchAssoc($sql, $this->userId)))
            throw new Minder_Exception('User "' . $this->userId . '" not found.');
            
        $this->personId                     = $result['PERSON_ID'];
        $this->companyId                    = $result['COMPANY_ID'];
        $this->divisionId                   = $result['DIVISION_ID'];
        $this->deviceId                     = $result['DEVICE_ID'];
        $this->locPermLevel                 = $result['LOC_PERM_LEVEL'];
        $this->prodPermLevel                = $result['PROD_PERM_LEVEL'];
        $this->taskPermLevel                = $result['TASK_PERM_LEVEL'];
        $this->loginDate                    = $result['LOGIN_DATE'];
        $this->passWord                     = $result['PASS_WORD'];
        $this->passWordDate                 = $result['PASS_WORD_DATE'];
        $this->pwExpiryDate                 = $result['PW_EXPIRY_DATE'];
        $this->lastUpdateDate               = $result['LAST_UPDATE_DATE'];
        $this->updateUserId                 = $result['UPDATE_USER_ID'];
        $this->sysAdmin                     = $result['SYS_ADMIN'];
        $this->zoneC                        = $result['ZONE_C'];
        $this->editable                     = $result['EDITABLE'];
        $this->userType                     = $result['USER_TYPE'];
        $this->saleManager                  = $result['SALE_MANAGER'];
        $this->creditManager                = $result['CREDIT_MANAGER'];
        $this->defaultWhId                  = $result['DEFAULT_WH_ID'];
        $this->inventoryOperator            = $result['INVENTORY_OPERATOR'];
        $this->pickSequence                 = $result['PICK_SEQUENCE'];
        $this->pickDirection                = $result['PICK_DIRECTION'];
        $this->defaultOrderSoldFrom         = $result['DEFAULT_ORDER_SOLD_FROM'];
        $this->defaultOrderSoldTo           = $result['DEFAULT_ORDER_SOLD_TO'];
        $this->stockAdjust                  = $result['STOCK_ADJUST'];
        $this->statusAdjustIssn             = $result['STATUS_ADJUST_ISSN'];
        $this->statusAdjustPickItem         = $result['STATUS_ADJUST_PICK_ITEM'];
        $this->statusAdjustPickOrder        = $result['STATUS_ADJUST_PICK_ORDER'];
        $this->statusAdjustPurchaseOrder    = $result['STATUS_ADJUST_PURCHASE_ORDER'];
        $this->statusAdjustPoLine           = $result['STATUS_ADJUST_PO_LINE'];
        $this->userCategory                 = $result['USER_CATEGORY'];
        $this->whmStartMenu                 = $result['WHM_START_MENU'];
        $this->minderStartMenu              = $result['MINDER_START_MENU'];
        $this->whManager                    = $result['WH_MANAGER'];
    }                                                       
                                                            
    public function isAdmin() {                             
        if (is_null($this->sysAdmin))                       
            $this->readFields();                            
        
        if ($this->sysAdmin == 'T' || $this->isSysAdmin())
            return true;
            
        return false;
    }                                                       
                                                            
    public function isSysAdmin() {                          
        if ($this->userId == 'Admin')
            return true;
            
        return false;
    }
    
    public function getCompanyAccessList() {
        $sqlArgs = array();
        if ($this->isAdmin()) {
            $sql = "
                SELECT 
                    COMPANY_ID, 
                    COMPANY_ID 
                FROM 
                    COMPANY 
                ORDER BY 
                    COMPANY.COMPANY_ID";
        } else {
            $sql = "
                SELECT 
                    COMPANY.COMPANY_ID, 
                    COMPANY.COMPANY_ID 
                FROM 
                    ACCESS_COMPANY 
                    JOIN COMPANY ON ACCESS_COMPANY.COMPANY_ID = COMPANY.COMPANY_ID 
                WHERE 
                    ACCESS_COMPANY.USER_ID = ? 
                ORDER BY 
                    COMPANY.COMPANY_ID";
            $sqlArgs[] = $this->userId;
        }
        
        array_unshift($sqlArgs, $sql);
        $minder = Minder::getInstance();

        if (false !== ($companyAccessList = call_user_func_array(array($minder, 'findList'), $sqlArgs))) {
            return $companyAccessList;
        }
        
        return array();
    }
    
    public function getWarehouseAccessList() {
        $sqlArgs = array();
        if ($this->isSysAdmin()) {
            $sql = "
                SELECT 
                    WH_ID, 
                    DESCRIPTION 
                FROM 
                    WAREHOUSE";
        } elseif ($this->isAdmin()) {
            $sql = "
                SELECT 
                    WH_ID, 
                    DESCRIPTION 
                FROM 
                    WAREHOUSE 
                WHERE 
                    NOT ((WH_ID  = 'XA') OR (WH_ID >= 'XC' AND WH_ID <= 'XX'))";
        } else {
            $sql = "
                SELECT 
                    WAREHOUSE.WH_ID, 
                    DESCRIPTION 
                FROM 
                    WAREHOUSE 
                    JOIN ACCESS_USER ON WAREHOUSE.WH_ID = ACCESS_USER.WH_ID 
                WHERE
                    USER_ID = ?";
            $sqlArgs[] = $this->userId;
        }
        
        array_unshift($sqlArgs, $sql);
        $minder = Minder::getInstance();

        if (false !== ($warehouseAccessList = call_user_func_array(array($minder, 'findList'), $sqlArgs))) {
            return $warehouseAccessList;
        }
        
        return array();
    }
}

