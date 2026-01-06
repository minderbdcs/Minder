<?php

namespace MinderNG\PageMicrocode;

use MinderNG\Di\DiContainer;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller;

class MicrocodeManager {
    private $_componentManager;
    private $_dice;

    function __construct(Component\ComponentManager $componentManager, DiContainer $dice)
    {
        $this->_componentManager = $componentManager;
        $this->_dice = $dice;
    }

    public function getPageMicrocode(Component\Page $page, \Minder2_Environment $environment) {
        $pageComponents = $this->_componentManager->getPageComponents($page, $environment);
        $microcode = new Microcode($pageComponents);

        $pageController = new Controller\Page();
        $pageController->init($pageComponents, $microcode);
        $screenController = new Controller\Screen();
        $screenController->init($pageComponents, $microcode);
        $editFormController = new Controller\EditForm();
        $editFormController->init($pageComponents, $microcode);
        $dataSetController = new Controller\DataSet();
        $dataSetController->init($pageComponents, $microcode);
        $pageSettingsController = new Controller\PageSettings();
        $pageSettingsController->init($pageComponents, $microcode);

        /**
         * @var Controller\DataSetRow $dataSetRowController
         */
        $dataSetRowController = $this->_dice->create(__NAMESPACE__ . '\\Controller\\DataSetRow');
        $dataSetRowController->init($pageComponents, $microcode);
        /**
         * @var Controller\Transaction $transactionController
         */
        $transactionController = $this->_dice->create(__NAMESPACE__ . '\\Controller\\Transaction');
        $transactionController->init($pageComponents, $microcode);

        /** @var Controller\SearchForm $searchFormController */
        $searchFormController = $this->_dice->create(Controller\SearchForm::CLASS_NAME);
        $searchFormController->init($pageComponents, $microcode);

        /** @var Controller\FieldDataSet $fieldDataSetController */
        $fieldDataSetController = $this->_dice->create(Controller\FieldDataSet::CLASS_NAME);
        $fieldDataSetController->init($pageComponents, $microcode);

        /** @var Controller\Tab $tabController */
        $tabController = $this->_dice->create(Controller\Tab::CLASS_NAME);
        $tabController->init($pageComponents, $microcode);

        /** @var Controller\ScreenButton $buttonController */
        $buttonController = $this->_dice->create(Controller\ScreenButton::CLASS_NAME);
        $buttonController->init($microcode);

        /** @var Controller\Form $formController */
        $formController = $this->_dice->create(Controller\Form::CLASS_NAME);
        $formController->init($pageComponents, $microcode);

        return $microcode;
    }
}