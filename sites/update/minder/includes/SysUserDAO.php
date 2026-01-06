<?php

class SysUserDAO extends Minder_DAO_CRUD
{
    public function __construct()
    {
        parent::__construct();
        $this->key = 'user_id';
        $this->createRequiresKey = true;
        $this->validators = array(
            'user_id' => array(array('StringLength', 1, 10)),
            'person_id' => array(array('StringLength', 1, 10), 'allowEmpty' => true),
            'company_id' => array(array('StringLength', 1, 20), 'allowEmpty' => true),
            'division_id' => array(array('StringLength', 1, 20), 'allowEmpty' => true),
            'device_id' => array(array('StringLength', 2, 2), 'allowEmpty' => true),
            'loc_perm_level' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'prod_perm_level' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'task_perm_level' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'login_date' => arrAY(array('DateTime'), 'allowEmpty' => true),
            'pass_word' => array(array('StringLength', 1, 10), 'allowEmpty' => true),
            'pass_word_date' => array(array('DateTime'), 'allowEmpty' => true),
            'pw_expiry_date' => array(array('DateTime'), 'allowEmpty' => true),
            'last_update_date' => array(array('DateTime'), 'allowEmpty' => true),
            'update_user_id' => array(array('StringLength', 1, 10), 'allowEmpty' => true),
            'sys_admin' => array(array('InArray', array('T', 'F')), 'allowEmpty' => true),
            'zone_c' => array(array('StringLength', 2, 2), 'allowEmpty' => true),
            'editable' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'user_type' => array(array('StringLength', 2, 2), 'allowEmpty' => true),
            'sale_manager' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'credit_manager' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'default_wh_id' => array(array('StringLength', 2, 2), 'allowEmpty' => true),
            'inventory_operator' => array(array('InArray', array('T', 'F')), 'allowEmpty' => true),
            'pick_sequence' => array(array('StringLength', 2, 2), 'allowEmpty' => true),
            'pick_direction' => array(array('StringLength', 1, 1), 'allowEmpty' => true),
            'default_order_sold_from' => array(array('StringLength', 1, 20), 'allowEmpty' => true),
            'default_order_sold_to' => array(array('StringLength', 1, 20), 'allowEmpty' => true),
            'stock_adjust' => array(array('InArray', array('T', 'F')), 'allowEmpty' => true));
        $this->autoCompletes = array(
            'company_id' => array('select company_id, company_id from company where lower(company_id) like ?', array('*%')));
    }

    public function init($dto)
    {
        $dto->userId = '';
        $dto->personId = null;
        $dto->companyId = null;
        $dto->divisionId = null;
        $dto->deviceId = null;
        $dto->locPermLevel = null;
        $dto->prodPermLevel = null;
        $dto->taskPermLevel = null;
        $dto->loginDate = null;
        $dto->passWord = null;
        $dto->passWordDate = null;
        $dto->pwExpiryDate = null;
        $dto->lastUpdateDate = null;
        $dto->updateUserId = null;
        $dto->sysAdmin = 'F';
        $dto->zoneC = 'AA';
        $dto->editable = null;
        $dto->userType = null;
        $dto->saleManager = null;
        $dto->creditManager = null;
        $dto->defaultWhId = null;
        $dto->inventoryOperator = null;
        $dto->pickSequence = null;
        $dto->pickDirection = null;
        $dto->defaultOrderSoldFrom = null;
        $dto->defaultOrderSoldTo = null;
        $dto->stockAdjust = 'F';
    }

    public function create($dto)
    {
        $dto->personId = ($dto->personId === '' ? null : $dto->personId);
        $dto->companyId = ($dto->companyId === '' ? null : $dto->companyId);
        $dto->divisionId = ($dto->divisionId === '' ? null : $dto->divisionId);
        $dto->deviceId = ($dto->deviceId === '' ? null : $dto->deviceId);
        $dto->locPermLevel = ($dto->locPermLevel === '' ? null : $dto->locPermLevel);
        $dto->prodPermLevel = ($dto->prodPermLevel === '' ? null : $dto->prodPermLevel);
        $dto->taskPermLevel = ($dto->taskPermLevel === '' ? null : $dto->taskPermLevel);
        $dto->loginDate = ($dto->loginDate === '' ? null : $dto->loginDate);
        $dto->passWord = ($dto->passWord === '' ? null : $dto->passWord);
        $dto->passWordDate = ($dto->passWordDate === '' ? null : $dto->passWordDate);
        $dto->pwExpiryDate = ($dto->pwExpiryDate === '' ? null : $dto->pwExpiryDate);
        $dto->lastUpdateDate = ($dto->lastUpdateDate === '' ? null : $dto->lastUpdateDate);
        $dto->updateUserId = ($dto->updateUserId === '' ? null : $dto->updateUserId);
        $dto->editable = ($dto->editable === '' ? null : $dto->editable);
        $dto->userType = ($dto->userType === '' ? null : $dto->userType);
        $dto->saleManager = ($dto->saleManager === '' ? null : $dto->saleManager);
        $dto->creditManager = ($dto->creditManager === '' ? null : $dto->creditManager);
        $dto->defaultWhId = ($dto->defaultWhId === '' ? null : $dto->defaultWhId);
        $dto->inventoryOperator = ($dto->inventoryOperator === '' ? null : $dto->inventoryOperator);
        $dto->pickSequence = ($dto->pickSequence === '' ? null : $dto->pickSequence);
        $dto->pickDirection = ($dto->pickDirection === '' ? null : $dto->pickDirection);
        $dto->defaultOrderSoldFrom = ($dto->defaultOrderSoldFrom === '' ? null : $dto->defaultOrderSoldFrom);
        $dto->defaultOrderSoldTo = ($dto->defaultOrderSoldTo === '' ? null : $dto->defaultOrderSoldTo);
        return parent::create($dto);
    }

    public function update($id, $dto)
    {
        $dto->personId = ($dto->personId === '' ? null : $dto->personId);
        $dto->companyId = ($dto->companyId === '' ? null : $dto->companyId);
        $dto->divisionId = ($dto->divisionId === '' ? null : $dto->divisionId);
        $dto->deviceId = ($dto->deviceId === '' ? null : $dto->deviceId);
        $dto->locPermLevel = ($dto->locPermLevel === '' ? null : $dto->locPermLevel);
        $dto->prodPermLevel = ($dto->prodPermLevel === '' ? null : $dto->prodPermLevel);
        $dto->taskPermLevel = ($dto->taskPermLevel === '' ? null : $dto->taskPermLevel);
        $dto->loginDate = ($dto->loginDate === '' ? null : $dto->loginDate);
        $dto->passWord = ($dto->passWord === '' ? null : $dto->passWord);
        $dto->passWordDate = ($dto->passWordDate === '' ? null : $dto->passWordDate);
        $dto->pwExpiryDate = ($dto->pwExpiryDate === '' ? null : $dto->pwExpiryDate);
        $dto->lastUpdateDate = ($dto->lastUpdateDate === '' ? null : $dto->lastUpdateDate);
        $dto->updateUserId = ($dto->updateUserId === '' ? null : $dto->updateUserId);
        $dto->editable = ($dto->editable === '' ? null : $dto->editable);
        $dto->userType = ($dto->userType === '' ? null : $dto->userType);
        $dto->saleManager = ($dto->saleManager === '' ? null : $dto->saleManager);
        $dto->creditManager = ($dto->creditManager === '' ? null : $dto->creditManager);
        $dto->defaultWhId = ($dto->defaultWhId === '' ? null : $dto->defaultWhId);
        $dto->inventoryOperator = ($dto->inventoryOperator === '' ? null : $dto->inventoryOperator);
        $dto->pickSequence = ($dto->pickSequence === '' ? null : $dto->pickSequence);
        $dto->pickDirection = ($dto->pickDirection === '' ? null : $dto->pickDirection);
        $dto->defaultOrderSoldFrom = ($dto->defaultOrderSoldFrom === '' ? null : $dto->defaultOrderSoldFrom);
        $dto->defaultOrderSoldTo = ($dto->defaultOrderSoldTo === '' ? null : $dto->defaultOrderSoldTo);
        return parent::update($id, $dto);
    }
}
