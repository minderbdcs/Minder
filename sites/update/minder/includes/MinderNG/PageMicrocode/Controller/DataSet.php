<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Event;

class DataSet implements Events\SubscriberAggregateInterface {
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
        $this->getSubscriber()->subscribeTo($messageBus, Event\EditDataSetRowRequest::EVENT_NAME, 'onEditDataSetRowRequest');
        $this->getSubscriber()->subscribeTo($messageBus, Event\PageLoad::EVENT_NAME, 'onPageLoad');
        $this->getSubscriber()->subscribeTo($messageBus, Event\DataSetRowChanged::EVENT_NAME, 'onDataSetRowChanged');
        $this->getSubscriber()->subscribeTo($messageBus, Event\ReloadRowError::EVENT_NAME, 'onReloadRowError');
    }

    public function onDataSetRowChanged(/** @noinspection PhpUnusedParameterInspection */
        Event\DataSetRowChanged $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow) {
        $foundDataSet = $this->_pageComponents->dataSetCollection->getDataSet($dataSet);

        if (!empty($foundDataSet)) {
            $foundRow = $foundDataSet->getDataSetRowCollection()->getDataSetRow($dataSetRow);

            if (!empty($foundRow)) {
                $foundRow->set($dataSetRow->getAttributes());
            }
        }
    }

    public function onPageLoad() {
        $screenCollection = new Component\ScreenCollection();
        $screenCollection->init();

        $mainDataSets = $this->_pageComponents->dataSetCollection->filterMainDataSets();

        foreach($mainDataSets as $dataSet) {
            if (!$dataSet->DATA_LOADED) {
                $this->_messageBus->getPublisher()->trigger(new Event\FetchDataSetRowsRequest($dataSet));
                $screenCollection->add(array($this->_pageComponents->screens->getDataSetScreen($dataSet)));
            }
        }

        foreach($screenCollection as $screen) {
            $this->_messageBus->getPublisher()->trigger(new Event\SearchCompleted($screen));
        }

    }

    public function onEditDataSetRowRequest(/** @noinspection PhpUnusedParameterInspection */
        Events\EventInterface $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, Component\Form $editForm) {
        $foundDataSet = $this->_pageComponents->dataSetCollection->findDataSet($dataSet);

        if ($dataSetRow->isNew()) {
            $foundDataSetRow = $foundDataSet->getDataSetRowCollection()->add(array(clone $dataSetRow));
            $foundDataSetRow = array_shift($foundDataSetRow);
        } else {
            $foundDataSetRow = $foundDataSet->getDataSetRowCollection()->findDataSetRow($dataSetRow);
        }

        $this->_messageBus->getPublisher()->trigger(new Event\StartEditDataSetRow($foundDataSet, clone $foundDataSetRow, $editForm));
    }

    public function onReloadRowError(/** @noinspection PhpUnusedParameterInspection */
        Event\ReloadRowError $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, $message) {
        $foundDataSet = $this->_pageComponents->dataSetCollection->findDataSet($dataSet);

        if ($dataSetRow->isNew()) {
            $foundRow = $foundDataSet->getDataSetRowCollection()->getDataSetRowByCID($dataSetRow->_CID_);

            if (!empty($foundRow)) {
                $foundDataSet->getDataSetRowCollection()->remove(array($foundRow));
            }

            $rowToEdit = $foundDataSet->firstRow();
            $rowToEdit = empty($rowToEdit) ? $foundDataSet->newRow() : $rowToEdit;

            $screen = $this->_pageComponents->screens->getDataSetScreen($foundDataSet);
            foreach($this->_pageComponents->forms->getAllScreenEditForms($screen) as $form) {
                $this->_messageBus->getPublisher()->trigger(new Event\StartEditDataSetRow($foundDataSet, $rowToEdit, $form));
            }
        }
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
}