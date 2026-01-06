<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Collection;
use MinderNG\Events;
use MinderNG\PageMicrocode\Command\SearchDataSet;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\DataSetHasNoPrimaryKEy;
use MinderNG\PageMicrocode\Controller\Exception\DataSetNotFound;
use MinderNG\PageMicrocode\Controller\Exception\ReloadRowError;
use MinderNG\PageMicrocode\Event\DataSetRowAdded;
use MinderNG\PageMicrocode\Event\DataSetRowChanged;
use MinderNG\PageMicrocode\Event\FetchDataSetRowsRequest;
use MinderNG\PageMicrocode\Event\TransactionExecuted;
use MinderNG\PageMicrocode\QueryBuilder\Builder;

class DataSetRow implements Events\SubscriberAggregateInterface {
    /**
     * @var Component\Components
     */
    private $_pageComponents;

    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;
    private $_subscriber;

    /**
     * @var Builder
     */
    private $_queryBuilder;

    function __construct(Builder $queryBuilder)
    {
        $this->_queryBuilder = $queryBuilder;
    }

    public function init(Component\Components $pageComponents, Events\PublisherAggregateInterface $messageBus) {
        $this->_pageComponents = $pageComponents;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, FetchDataSetRowsRequest::EVENT_NAME, 'onFetchDataSetRowsRequest');
        $this->getSubscriber()->subscribeTo($messageBus, SearchDataSet::COMMAND_NAME, 'onSearchDataSet');
        $this->getSubscriber()->subscribeTo($messageBus, TransactionExecuted::EVENT_NAME, 'onTransactionExecuted');
    }

    public function onTransactionExecuted(TransactionExecuted $event, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow) {
        $foundDataSet = $this->_pageComponents->dataSetCollection->findDataSet($dataSet);

        try {
            $reloadedRow = $this->_reloadDataSetRow($foundDataSet, $dataSetRow);
            if ($dataSetRow->isNew()) {
                $this->_messageBus->getPublisher()->trigger(new DataSetRowAdded($foundDataSet, $reloadedRow));
            } else {
                $this->_messageBus->getPublisher()->trigger(new DataSetRowChanged($foundDataSet, $reloadedRow));
            }
        } catch (ReloadRowError $e) {
            $this->_pageComponents->getMessages()->addWarning($e->getMessage());
            $this->_messageBus->getPublisher()->trigger(new \MinderNG\PageMicrocode\Event\ReloadRowError($foundDataSet, $dataSetRow, $e->getMessage()));
        }
    }

    public function onSearchDataSet(SearchDataSet $command, Component\DataSet $dataSet, Component\SearchSpecification\DataSet $searchSpecification) {
        $foundDataSet = $this->_pageComponents->dataSetCollection->getDataSet($dataSet);

        if (empty($foundDataSet)) {
            throw new DataSetNotFound($dataSet);
        }

        if (!$foundDataSet->isMain()) {
            return;
        }

        $this->_pageComponents->getDataSetSearchSpecifications()->add(array($searchSpecification), new Collection\AddOptions(true, false, true));

        $this->_doSearch($foundDataSet, $searchSpecification);
        $command->setResponse(true);
    }

    /**
     * @param Events\EventInterface $event
     * @param Component\DataSet $dataSet
     */
    public function onFetchDataSetRowsRequest(/** @noinspection PhpUnusedParameterInspection */ Events\EventInterface $event, Component\DataSet $dataSet) {
        if (!$dataSet->isMain()) {
            return;
        }

        $this->_doSearch($dataSet, $this->_pageComponents->getDataSetSearchSpecifications()->getDataSetSearchSpecification($dataSet));
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
     * @param Component\SearchSpecification\DataSet $searchSpecification
     * @throws DataSetHasNoPrimaryKEy
     */
    protected function _doSearch(Component\DataSet $dataSet, Component\SearchSpecification\DataSet $searchSpecification = null)
    {
        $dataSet->getDataSetRowCollection()->reset(array());
        $dataSet->set(array(
            'fetchedRows' => count($dataSet->getDataSetRowCollection()),
            'hasMore' => false,
            'DATA_LOADED' => false,
        ));

        $query = $this->_getQueryBuilder()->buildQuery($this->_pageComponents, $dataSet, $searchSpecification);
        $result = $this->_getDbAdapter()->query($query)->fetchAll();

        $fetchedRowsAmount = count($result);
        $dataSet->getDataSetRowCollection()->reset($result, new Collection\AddOptions(true, true));
        $dataSet->getDataSetRowCollection()->updateRowIndexes();

        $dataSet->set(array(
            'fetchedRows' => count($dataSet->getDataSetRowCollection()),
            'hasMore' => ($fetchedRowsAmount >= $dataSet->LIMIT),
            'DATA_LOADED' => true,
        ));

        if ($fetchedRowsAmount > count($dataSet->getDataSetRowCollection())) {
            user_error('DataSet ' . $dataSet->getId() . ' primary key has not unique values.', E_USER_WARNING);
        }
    }

    /**
     * @return \Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return \Zend_Db_Table::getDefaultAdapter();
    }

    /**
     * @return Builder
     */
    private function _getQueryBuilder()
    {
        return $this->_queryBuilder;
    }

    private function _reloadDataSetRow(Component\DataSet $dataSet, Component\DataSetRow $dataSetRow) {
        $searchSpecification = $this->_addPrimaryIdSpecification(
            $dataSet,
            $dataSetRow,
            $this->_pageComponents->getDataSetSearchSpecifications()->getDataSetSearchSpecification($dataSet)
        );

        $query = $this->_getQueryBuilder()->buildQuery($this->_pageComponents, $dataSet, $searchSpecification);

        try {
            $queryResult = $this->_getDbAdapter()->query($query)->fetch();
        } catch (\Exception $e) {
            throw new ReloadRowError($dataSetRow, 0, $e);
        }

        if (empty($queryResult)) {
            throw new ReloadRowError($dataSetRow);
        }

        $result = $dataSet->newRow($queryResult);
        $result->_CID_ = $dataSetRow->_CID_;

        return $result;
    }

    private function _addPrimaryIdSpecification(Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, Component\SearchSpecification\DataSet $searchSpecification = null) {
        $result = is_null($searchSpecification) ? new Component\SearchSpecification\DataSet() : clone $searchSpecification;
        $parts = array();

        foreach ($dataSet->filterPrimaryKeys($this->_pageComponents->dataSourceFieldCollection) as $field) {
            $parts[] = new Component\SearchSpecification\EqualTo($field->SSV_EXPRESSION, $dataSetRow[$field->SSV_ALIAS]);
        }

        $result->addRow($parts);

        return $result;
    }
}