<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Collection\Helper\Helper;
use MinderNG\Events;
use MinderNG\PageMicrocode\Command\RunDataSetTransaction;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\TransactionError;
use MinderNG\PageMicrocode\Controller\Transaction\ExecuteStrategyAll;
use MinderNG\PageMicrocode\Controller\Transaction\ExecuteStrategyChanged;
use MinderNG\PageMicrocode\Controller\Transaction\ExecuteStrategyInterface;
use MinderNG\PageMicrocode\Transaction\Builder;

class Transaction implements Events\SubscriberAggregateInterface {
    private $_subscriber;
    /**
     * @var Component\Components
     */
    private $_pageComponents;

    /**
     * @var Events\PublisherAggregateInterface
     */
    private $_messageBus;

    /**
     * @var Helper
     */
    private $_collectionHelper;

    /**
     * @var Builder
     */
    private $_transactionBuilder;

    public function init(Component\Components $pageComponents, Events\PublisherAggregateInterface $messageBus) {
        $this->_pageComponents = $pageComponents;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, RunDataSetTransaction::COMMAND_NAME, 'onRunDataSetTransaction');
    }

    public function onRunDataSetTransaction(RunDataSetTransaction $command, Component\DataSet $dataSet, Component\DataSetRow $dataSetRow, $action, $mode) {
        $originalValues = ($dataSetRow->hasChanged() && !$dataSetRow->isNew()) ? $dataSetRow->getPreviousAttributes() : $dataSetRow->getAttributes();
        $sharedValues = $dataSetRow->getAttributes();
        $executeStrategy = $this->_createExecuteStrategy($mode);

        try {
            foreach($this->_pageComponents->transactionCollection->getDataSetTransactionListByAction($dataSet, $action) as $transaction) {
                if ($executeStrategy->shouldExecute($transaction, $dataSetRow)) {
                    $fields = $this->_pageComponents->transactionFieldCollection->getTransactionFieldList($transaction);
                    $dbTransaction = $this->_getTransactionBuilder()->buildTransaction($transaction, $fields, $sharedValues, $originalValues);
                    $response = $this->_getMinder()->doTransactionResponseV6($dbTransaction);
                    $dbTransaction->setResponseText($response);
                    $sharedValues = $this->_getTransactionBuilder()->updateSharedValues($dbTransaction, $fields, $sharedValues);
                }
            }
        } catch (\Exception $e) {
            throw new TransactionError($e->getMessage(), $this->_fillChangedValues($dataSetRow, $sharedValues), 0, $e);
        }

        $command->setResponse($this->_fillChangedValues($dataSetRow, $sharedValues));
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
     * @param Component\DataSetRow $dataSetRow
     * @param Component\TransactionField[]|\Traversable $fields
     * @return bool
     */
    private function _shouldExecuteTransaction(Component\DataSetRow $dataSetRow, $fields) {
        $fieldAliases = iterator_to_array($this->_getCollectionHelper()->pluck($fields, 'SST_COLUMN'));
        $changedColumns = array_keys($dataSetRow->changedAttributes());

        return count(array_intersect($fieldAliases, $changedColumns)) > 0;
    }

    /**
     * @return Helper
     */
    private function _getCollectionHelper() {
        if (empty($this->_collectionHelper)) {
            $this->_collectionHelper = new Helper();
        }

        return $this->_collectionHelper;
    }

    /**
     * @return Builder
     */
    private function _getTransactionBuilder() {
        if (empty($this->_transactionBuilder)) {
            $this->_transactionBuilder = new Builder();
        }

        return $this->_transactionBuilder;
    }

    private function _getMinder() {
        return \Minder::getInstance();
    }

    private function _fillChangedValues(Component\DataSetRow $dataSetRow, array $sharedValues) {
        $changedDataSetValues = array_intersect_key($sharedValues, $dataSetRow->getAttributes());
        $dataSetRow->set($changedDataSetValues);

        return $dataSetRow;
    }

    /**
     * @param $mode
     * @return ExecuteStrategyInterface
     * @throws \Exception
     */
    private function _createExecuteStrategy($mode) {
        switch ($mode) {
            case RunDataSetTransaction::MODE_ALL:
                return new ExecuteStrategyAll();
            case RunDataSetTransaction::MODE_CHANGED:
                return new ExecuteStrategyChanged($this->_pageComponents);
            default:
                throw new \Exception('Unsupported execute transaction mode "' . $mode . '"');
        }
    }
}