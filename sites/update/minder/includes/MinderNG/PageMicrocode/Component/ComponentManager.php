<?php

namespace MinderNG\PageMicrocode\Component;

class ComponentManager {
    private $_screenManager;
    private $_formManager;
    private $_tabManager;
    private $_fieldManager;
    private $_pageValidator;
    private $_tableManager;
    private $_controlValues;
    private $_dataSetManager;
    private $_transactionFieldManager;
    private $_transactionManager;
    private $_pageManager;
    private $_companyManager;
    private $_warehouseManager;
    private $_deviceManager;
    /**
     * @var ButtonManager
     */
    private $_buttonManager;
    /**
     * @var FilterManager
     */
    private $_filterManager;
    /**
     * @var ValidatorManager
     */
    private $_validatorManager;

    function __construct(
        PageManager $pageManager,
        ScreenManager $screenManager,
        FormManager $formManager,
        TabManager $tabManager,
        FieldManager $fieldManager,
        TableManager $tableManager,
        DataSetManager $dataSetManager,
        DataSourceFieldManager $dataSourceFieldManager,
        TransactionManager $transactionManager,
        TransactionFieldManager $transactionFieldManager,
        DataIdentifierManager $dataIdentifierManager,
        CompanyManager $companyManager,
        WarehouseManager $warehouseManager,
        DeviceManager $deviceManager,
        ButtonManager $buttonManager,
        FilterManager $filterManager,
        ValidatorManager $validatorManager,
        Validator\Page $pageValidator,
        \Minder $minder
    )
    {
        $this->_pageManager = $pageManager;
        $this->_screenManager = $screenManager;
        $this->_formManager = $formManager;
        $this->_tabManager = $tabManager;
        $this->_fieldManager = $fieldManager;
        $this->_tableManager = $tableManager;
        $this->_dataSetManager = $dataSetManager;
        $this->_dataSourceFieldMAnager = $dataSourceFieldManager;
        $this->_transactionManager = $transactionManager;
        $this->_transactionFieldManager = $transactionFieldManager;
        $this->_dataIdentifierManager = $dataIdentifierManager;
        $this->_companyManager = $companyManager;
        $this->_warehouseManager = $warehouseManager;
        $this->_deviceManager = $deviceManager;
        $this->_pageValidator = $pageValidator;
        $this->_controlValues = $minder->defaultControlValues;
        $this->_buttonManager = $buttonManager;
        $this->_filterManager = $filterManager;
        $this->_validatorManager = $validatorManager;
    }

    public function getPageComponents(Page $page, \Minder2_Environment $environment) {
        $screens = $this->_screenManager->getUserAndDeviceScreens($page, $environment);

        $fields                     = $this->_fieldManager->getScreenFields($screens, $environment);
        $transactionFieldCollection = $this->_transactionFieldManager->getTransactionFields($screens);
        $tables                     = $this->_tableManager->getScreenTables($screens, $environment);
        $forms                      = $this->_formManager->getScreenForms($screens, $fields, $environment);

        $components = new Components(
            $this->_pageManager->getCurrentUserAndDevicePages($environment),
            $page,
            $screens,
            $this->_tabManager->getScreenTabs($screens, $forms, $tables, $fields, $environment),
            $forms,
            $fields,
            $tables,
            $this->_dataSetManager->getScreenDataSets($fields),
            $this->_dataSourceFieldMAnager->getDataSourceFieldCollection($fields),
            $this->_transactionManager->getTransactions($transactionFieldCollection),
            $transactionFieldCollection,
            $this->_dataIdentifierManager->getDataIdentifierCollection($environment),
            $this->_companyManager->getCompanyLimit($environment->getCompanyLimit()),
            $this->_companyManager->getUserCompanyLimitList($environment->getCurrentUser()),
            $this->_warehouseManager->getWarehouseLimit($environment->getWarehouseLimit()),
            $this->_warehouseManager->getUserWarehouseLimitList($environment->getCurrentUser()),
            $this->_deviceManager->getSelectedPrinter($environment->getCurrentPrinter()),
            $this->_deviceManager->getUserPrinterList($environment->getCurrentUser()),
            $this->_buttonManager->getScreenButtons($screens, $forms, $environment),
            $this->_filterManager,
            $this->_validatorManager
        );

        $components->controlValues->set($this->_controlValues, false, false, true);

        return $components;
    }
}