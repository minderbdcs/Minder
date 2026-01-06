<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\JsonSerializableInterface;

class Components implements JsonSerializableInterface {
    public $pageCollection;
    public $page;
    public $screens;
    public $tabs;
    public $forms;
    public $fields;
    public $tables;
    public $dataSetCollection;
    public $user;
    public $device;
    public $warehouse;
    public $company;
    public $companyLimit;
    public $companyLimitList;
    public $warehouseLimit;
    public $warehouseLimitList;
    public $selectedPrinter;
    public $printerList;
    public $pageSettings;
    public $controlValues;
    public $dataSourceFieldCollection;
    public $transactionCollection;
    public $transactionFieldCollection;
    public $dataIdentifierCollection;
    private $_dataSetSearchSpecifications;
    public $buttonCollection;

    /** @var FilterManager */
    private $_filterManager;

    /** @var ValidatorManager */
    private $_validatorManager;

    /** @var MessageCollection */
    private $_messages;

    /**
     * Components constructor.
     * @param PageCollection $pageCollection
     * @param Page $page
     * @param ScreenCollection $screens
     * @param TabCollection $tabs
     * @param FormCollection $forms
     * @param FieldCollection $fields
     * @param TableCollection $tables
     * @param DataSetCollection $dataSetCollection
     * @param DataSourceFieldCollection $dataSourceFieldCollection
     * @param TransactionCollection $transactionCollection
     * @param TransactionFieldCollection $transactionFieldCollection
     * @param DataIdentifierCollection $dataIdentifierCollection
     * @param Company $companyLimit
     * @param CompanyCollection $companyLimitList
     * @param Warehouse $warehouseLimit
     * @param WarehouseCollection $warehouseLimitList
     * @param Device $selectedPrinter
     * @param DeviceCollection $printerList
     * @param ButtonCollection $buttonCollection
     * @param FilterManager $filterManager
     * @param ValidatorManager $validatorManager
     */
    function __construct(
        PageCollection $pageCollection,
        Page $page,
        ScreenCollection $screens,
        TabCollection $tabs,
        FormCollection $forms,
        FieldCollection $fields,
        TableCollection $tables,
        DataSetCollection $dataSetCollection,
        DataSourceFieldCollection $dataSourceFieldCollection,
        TransactionCollection $transactionCollection,
        TransactionFieldCollection $transactionFieldCollection,
        DataIdentifierCollection $dataIdentifierCollection,
        Company $companyLimit,
        CompanyCollection $companyLimitList,
        Warehouse $warehouseLimit,
        WarehouseCollection $warehouseLimitList,
        Device $selectedPrinter,
        DeviceCollection $printerList,
        ButtonCollection $buttonCollection,
        FilterManager $filterManager,
        ValidatorManager $validatorManager
    )
    {
        $this->pageCollection = $pageCollection;
        $this->page = $page;
        $this->screens = $screens;
        $this->tabs = $tabs;
        $this->forms = $forms;
        $this->fields = $fields;
        $this->tables = $tables;
        $this->dataSetCollection = $dataSetCollection;
        $this->dataSourceFieldCollection = $dataSourceFieldCollection;
        $this->transactionCollection = $transactionCollection;
        $this->transactionFieldCollection = $transactionFieldCollection;
        $this->dataIdentifierCollection = $dataIdentifierCollection;
        $this->companyLimit = $companyLimit;
        $this->companyLimitList = $companyLimitList;
        $this->warehouseLimit = $warehouseLimit;
        $this->warehouseLimitList = $warehouseLimitList;
        $this->selectedPrinter = $selectedPrinter;
        $this->printerList = $printerList;
        $this->buttonCollection = $buttonCollection;

        $this->user = new User();
        $this->device = new Device();
        $this->warehouse = new Warehouse();
        $this->company = new Company();
        $this->pageSettings = new PageSettings();
        $this->controlValues = new ControlValues();

        $this->user->init();
        $this->device->init();
        $this->warehouse->init();
        $this->company->init();
        $this->pageSettings->init();
        $this->controlValues->init();
        $this->_filterManager = $filterManager;
        $this->_validatorManager = $validatorManager;
    }

    public function getArrayCopy($full = true) {
        return array(
            'pageCollection' => iterator_to_array($this->pageCollection->getArrayCopy($full)),
            'page' => $this->page->getArrayCopy($full),
            'screenCollection' => iterator_to_array($this->screens->getArrayCopy($full)),
            'tabCollection' => iterator_to_array($this->tabs->getArrayCopy($full)),
            'formCollection' => iterator_to_array($this->forms->getArrayCopy($full)),
            'fieldCollection' => iterator_to_array($this->fields->getArrayCopy($full)),
            'buttonCollection' => iterator_to_array($this->buttonCollection->getArrayCopy($full)),
            'dataSetCollection' => iterator_to_array($this->dataSetCollection->getArrayCopy($full)),
            'dataIdentifierCollection' => iterator_to_array($this->dataIdentifierCollection->getArrayCopy($full)),
            'companyLimit' => $this->companyLimit->getArrayCopy($full),
            'companyLimitList' => iterator_to_array($this->companyLimitList->getArrayCopy($full)),
            'warehouseLimit' => $this->warehouseLimit->getArrayCopy($full),
            'warehouseLimitList' => iterator_to_array($this->warehouseLimitList->getArrayCopy($full)),
            'selectedPrinter' => $this->selectedPrinter->getArrayCopy($full),
            'printerList' => iterator_to_array($this->printerList->getArrayCopy($full)),
            'user' => $this->user->getArrayCopy($full),
            'device' => $this->device->getArrayCopy($full),
            'warehouse' => $this->warehouse->getArrayCopy($full),
            'messages' => iterator_to_array($this->getMessages()->getArrayCopy($full)),
        );
    }

    /**
     * @return SearchSpecification\DataSetCollection
     */
    public function getDataSetSearchSpecifications()
    {
        if (empty($this->_dataSetSearchSpecifications)) {
            $this->_dataSetSearchSpecifications = new SearchSpecification\DataSetCollection();
            $this->_dataSetSearchSpecifications->init();
        }

        return $this->_dataSetSearchSpecifications;
    }

    public function getFieldFilter(Field $field, $isNew) {
        return $isNew ? $this->_filterManager->getFieldFilterNew($field) : $this->_filterManager->getFieldFilter($field);
    }

    public function getFieldValidator(Field $field, $isNew) {
        return $isNew ? $this->_validatorManager->getFieldValidatorNew($field) : $this->_validatorManager->getFieldValidator($field);
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy(true);
    }

    /**
     * @return MessageCollection
     */
    public function getMessages()
    {
        if (empty($this->_messages)) {
            $this->_messages = new MessageCollection();
            $this->_messages->init();
        }

        return $this->_messages;
    }
}