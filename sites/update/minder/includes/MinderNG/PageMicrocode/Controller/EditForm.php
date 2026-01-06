<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\DataSetRowUpdateError;
use MinderNG\PageMicrocode\Controller\Exception\TransactionError;
use MinderNG\PageMicrocode\Event;

class EditForm implements Events\SubscriberAggregateInterface {
    private $_subscriber;
    /**
     * @var Component\Components
     */
    private $_pageComponents;

    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    public function init(Component\Components $pageComponents, Events\PublisherAggregateInterface $messageBus) {
        $this->_pageComponents = $pageComponents;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, Command\InitEditForm::COMMAND_NAME, 'onInitEditForm');
        $this->getSubscriber()->subscribeTo($messageBus, Event\StartEditDataSetRow::EVENT_NAME, 'onStartEditDataSetRow');
        $this->getSubscriber()->subscribeTo($messageBus, Command\SaveChanges::COMMAND_NAME, 'onSaveChanges');
        $this->getSubscriber()->subscribeTo($messageBus, Event\DataSetRowChanged::EVENT_NAME, 'onDataSetRowChanged');
        $this->getSubscriber()->subscribeTo($messageBus, Event\DataSetRowAdded::EVENT_NAME, 'onDataSetRowAdded');
        $this->getSubscriber()->subscribeTo($messageBus, Event\SearchCompleted::EVENT_NAME, 'onSearchCompleted');
    }

    public function onInitEditForm(Command\InitEditForm $command, Component\Form $form) {
        foreach ($form->getWorkingDataSetCollection() as $dataSet) {
            foreach($dataSet->getDataSetRowCollection() as $dataSetRow) {
                if ($dataSetRow->isNew()) {
                    $newRow = $dataSet->getDataSetRowCollection()->newDataSetRow($this->_getFieldDefaults($form, $dataSet));
                    $newRow->_CID_ = $dataSetRow->_CID_;
                    $this->_messageBus->getPublisher()->trigger(new Event\EditDataSetRowRequest($dataSet, $newRow, $form));
                } else {
                    $this->_messageBus->getPublisher()->trigger(new Event\EditDataSetRowRequest($dataSet, $dataSetRow, $form));
                }
            }
        }

        $command->setResponse(true);
    }

    public function onSearchCompleted(Event\SearchCompleted $event, Component\Screen $screen) {

        $editForms = $this->_pageComponents->forms->getAllScreenEditForms($screen);

        foreach ($this->_pageComponents->dataSetCollection->getAllScreenDataSets($screen) as $dataSet) {
            if ($dataSet->isMain()) {
                $dataSetRow = $dataSet->firstRow();

                foreach ($editForms as $form) {
                    $form->getWorkingDataSetCollection()->reset(array());

                    if ($dataSetRow) {
                        $form->startEditDataSetRow($dataSet, $dataSetRow);
                    }
                }
            }
        }
    }

    public function onDataSetRowChanged(Event\DataSetRowChanged $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow) {
        foreach ($this->_filterDataSetForms($dataSet) as $form) {
            $form->syncDataSetRow($dataSet, $dataSetRow);
        }
    }

    public function onDataSetRowAdded(Event\DataSetRowAdded $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow) {
        foreach ($this->_filterDataSetForms($dataSet) as $form) {
            $form->syncDataSetRow($dataSet, $dataSetRow);
        }
    }

    public function onSaveChanges(Command\SaveChanges $command, Component\Form $form) {
        $foundForm = $this->_pageComponents->forms->findForm($form);
        $validateCommand = new Command\ValidateForm($foundForm);
        $this->_messageBus->getPublisher()->send($validateCommand);

        if ($validateCommand->isValid()) {
            $this->_saveFormChanges($foundForm);
        } else {
            //todo: notify validation errors
        }

        $command->setResponse($foundForm);
    }

    public function onStartEditDataSetRow(Event\StartEditDataSetRow $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, Component\Form $form) {
        $this->_pageComponents->forms->findForm($form)->startEditDataSetRow($dataSet, $dataSetRow);
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

    /**
     * @param Component\DataSet $dataSet
     * @return \Iterator|Component\Form[]
     */
    private function _filterDataSetForms(Component\DataSet $dataSet) {
        return $this->_pageComponents->forms->where(array(
            'SS_NAME' => $dataSet->SS_NAME
        ));
    }

    /**
     * @param $dataSet
     * @param $dataSetRow
     * @throws DataSetRowUpdateError
     */
    protected function _runUpdateTransaction($dataSet, $dataSetRow)
    {
        $command = new Command\RunDataSetTransaction($dataSet, clone $dataSetRow, Component\Transaction::ACTION_UPDATE, Command\RunDataSetTransaction::MODE_CHANGED);

        try {
            $this->_messageBus->getPublisher()->send($command);
        } catch (TransactionError $exception) {
            $this->_pageComponents->getMessages()->addError($exception->getMessage());
            $this->_messageBus->getPublisher()->trigger(new Event\TransactionExecuted($dataSet, $exception->getDataSetRow()));
            return;
        }

        $this->_pageComponents->getMessages()->addMessage('Record updated.');
        $this->_messageBus->getPublisher()->trigger(new Event\TransactionExecuted($dataSet, $command->getResultDataSetRow()));
    }

    /**
     * @param Component\Form $foundForm
     * @throws DataSetRowUpdateError
     */
    private function _saveFormChanges(Component\Form $foundForm)
    {
        foreach ($foundForm->getWorkingDataSetCollection() as $dataSet) {
            foreach ($dataSet->getDataSetRowCollection() as $dataSetRow) {
                if ($dataSetRow->isNew()) {
                    $this->_runAddTransaction($dataSet, $dataSetRow);
                } elseif ($dataSetRow->hasChanged()) {
                    $this->_runUpdateTransaction($dataSet, $dataSetRow);
                }
            }
        }
    }

    private function _getFieldDefaults(Component\Form $form, Component\DataSet $dataSet) {
        return $this->_pageComponents->fields->getFormDataSetFieldDefaults($form, $dataSet);
    }

    private function _runAddTransaction($dataSet, $dataSetRow) {
        $command = new Command\RunDataSetTransaction($dataSet, clone $dataSetRow, Component\Transaction::ACTION_ADD, Command\RunDataSetTransaction::MODE_ALL);

        try {
            $this->_messageBus->getPublisher()->send($command);
        } catch (TransactionError $exception) {
            $this->_pageComponents->getMessages()->addError($exception->getMessage());
            $this->_messageBus->getPublisher()->trigger(new Event\TransactionExecuted($dataSet, $exception->getDataSetRow()));
            return;
        }

        $this->_pageComponents->getMessages()->addMessage('Record created.');
        $this->_messageBus->getPublisher()->trigger(new Event\TransactionExecuted($dataSet, $command->getResultDataSetRow()));
    }
}