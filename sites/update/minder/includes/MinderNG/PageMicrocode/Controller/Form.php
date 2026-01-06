<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\Filter;
use MinderNG\Validator;
use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Component;

class Form implements Events\SubscriberAggregateInterface {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Controller\\Form';

    /**
     * @var Events\Subscriber
     */
    private $_subscriber;

    /**
     * @var Component\Components
     */
    private $_components;

    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    /**
     * @var Filter\Factory
     */
    private $_filterFactory;
    /**
     * @var Validator\Factory
     */
    private $_validatorFactory;

    /**
     * Form constructor.
     * @param Filter\Factory $filterFactory
     * @param Validator\Factory $validatorFactory
     */
    public function __construct(Filter\Factory $filterFactory, Validator\Factory $validatorFactory) {
        $this->_filterFactory = $filterFactory;
        $this->_validatorFactory = $validatorFactory;
    }


    public function init(Component\Components $components, Events\PublisherAggregateInterface $messageBus) {
        $this->_components = $components;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, Command\UpdateForm::COMMAND_NAME, 'onUpdateForm');
        $this->getSubscriber()->subscribeTo($messageBus, Command\ValidateForm::COMMAND_NAME, 'onValidateForm');
    }

    public function onUpdateForm(Command\UpdateForm $command, Component\Form $form) {
        $foundForm = $this->_components->forms->findForm($form);

        $foundForm->syncFormDataSet($this->_filterFormData($form));

        $command->setResponse(true);
    }

    public function onValidateForm(Command\ValidateForm $command, Component\Form $form) {
        $command->setValidationErrors($this->_validateForm($this->_components->forms->findForm($form)));
    }

    /**
     * @return Events\SubscriberInterface
     */
    public function getSubscriber()
    {
        if (empty($this->_subscriber)) {
            $this->_subscriber = new Events\Subscriber($this);
        }

        return $this->_subscriber;
    }

    private function _filterFormData(Component\Form $form) {
        foreach($form->getWorkingDataSetCollection() as $dataSet) {
            foreach ($dataSet->getDataSetRowCollection() as $dataSetRow) {
                $filter = $this->_getFormDataSetFilter($form, $dataSet, $dataSetRow->isNew());
                $dataSetRow->set($filter->filter($dataSetRow->getAttributes()));
            }
        }

        return $form;
    }

    private function _getFormDataSetFilter($form, $dataSet, $isNew = false) {
        $pageComponents = $this->_components;
        $fieldFilters = array_map(
            function(Component\Field $field) use($pageComponents, $isNew) { return $pageComponents->getFieldFilter($field, $isNew); },
            iterator_to_array($this->_components->fields->getFormDataSetFields($form, $dataSet))
        );

        return $this->_filterFactory->fieldSet($fieldFilters);
    }

    private function _validateForm(Component\Form $form) {
        $result = array();
        foreach ($form->getWorkingDataSetCollection() as $dataSet) {

            foreach ($dataSet->getDataSetRowCollection() as $dataSetRow) {
                $validator = $this->_getFormDataSetValidator($form, $dataSet, $dataSetRow->isNew());
                $result += $validator->validate($dataSetRow->getAttributes(), $dataSetRow->getAttributes());
            }
        }

        return $result;
    }

    private function _getFormDataSetValidator(Component\Form $form, Component\DataSet $dataSet, $isNew) {
        $pageComponents = $this->_components;
        $fieldValidators = array_map(
            function(Component\Field $field) use($pageComponents, $isNew) { return $pageComponents->getFieldValidator($field, $isNew); },
            iterator_to_array($this->_components->fields->getFormDataSetFields($form, $dataSet))
        );

        return $this->_validatorFactory->fieldSet($fieldValidators);
    }

}