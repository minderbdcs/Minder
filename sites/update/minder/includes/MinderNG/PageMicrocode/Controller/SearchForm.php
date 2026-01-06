<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Collection\AddOptions;
use MinderNG\Events;
use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Controller\Exception\FormNotFound;
use MinderNG\PageMicrocode\Event\PageLoad;
use MinderNG\PageMicrocode\Event\SearchCompleted;

class SearchForm implements Events\SubscriberAggregateInterface {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Controller\\SearchForm';

    /**
     * @var Events\Subscriber
     */
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

        $this->getSubscriber()->subscribeTo($messageBus, PageLoad::EVENT_NAME, 'onPageLoad');
        $this->getSubscriber()->subscribeTo($messageBus, Command\Search::COMMAND_NAME, 'onSearchCommand');
        $this->getSubscriber()->subscribeTo($messageBus, Command\ClearSearch::COMMAND_NAME, 'onClearSearchCommand');
    }

    public function onPageLoad(PageLoad $event) {
        $addOptions = new AddOptions(false, true);

        /** @var Component\Form $form */
        foreach ($this->_pageComponents->forms as $form) {
            if ($form->isStatusOk() && $form->isSearchForm()) {
                $screen = $this->_pageComponents->screens->getFormScreen($form);

                foreach($this->_pageComponents->dataSetCollection->getAllScreenDataSets($screen) as $dataSet) {
                    if ($dataSet->isMain()) {
                        $foundDataSet = $form->getWorkingDataSetCollection()->add(array($dataSet->getArrayCopy()), $addOptions);
                        $foundDataSet = array_shift($foundDataSet);
                        /** @var Component\DataSet $foundDataSet */

                        if (count($foundDataSet->getDataSetRowCollection()) < 1) {
                            $foundDataSet->getDataSetRowCollection()->reset(array(array()), $addOptions);
                        }
                    }
                }
            }
        }
    }

    public function onClearSearchCommand(Command\ClearSearch $command, Component\Form $form) {
        $foundForm = $this->_pageComponents->forms->findForm($form);

        foreach($foundForm->getWorkingDataSetCollection() as $dataSet) {
            $dataSet->getDataSetRowCollection()->reset(array(array()), new AddOptions(false, true));
        }

        if ($foundForm->EXECUTE_SEARCH_ON_CLEAR) {
            $this->_messageBus->getPublisher()->send(new Command\Search($foundForm));
        }

        $command->setResponse(true);
    }

    public function onSearchCommand(Command\Search $command, Component\Form $searchForm) {
        $foundForm = $this->_pageComponents->forms->findForm($searchForm);

        $foundForm->getWorkingDataSetCollection()->reset(iterator_to_array($searchForm->getWorkingDataSetCollection()->getArrayCopy(true)), new AddOptions(false, true));

        foreach ($foundForm->getWorkingDataSetCollection() as $dataSet) {
            $this->_messageBus->getPublisher()->send(new Command\SearchDataSet($dataSet, $this->_createSearchSpecification($dataSet)));
        }

        $this->_messageBus->getPublisher()->trigger(new SearchCompleted($this->_pageComponents->screens->getFormScreen($foundForm)));

        $command->setResponse(true);
    }

    private function _createSearchSpecification(Component\DataSet $formDataSet) {
        $searchSpecification = $this->_pageComponents->getDataSetSearchSpecifications()->newDataSetSearchSpecification($formDataSet);

        $searchFields = iterator_to_array($this->_pageComponents->fields->filterDataSetSearchFields($formDataSet));

        if (count($searchFields) < 1) {
            return $searchSpecification;
        }

        foreach ($formDataSet->getDataSetRowCollection() as $row) {
            $searchSpecification->addRow($this->_createRowSearchSpecification($row, $searchFields));
        }

        return $searchSpecification;
    }

    /**
     * @param Component\DataSetRow $row
     * @param Component\Field[] $searchFields
     * @return array
     */
    private function _createRowSearchSpecification(Component\DataSetRow $row, $searchFields) {
        $result = array();

        foreach($searchFields as $searchField) {
            if (!empty($row[$searchField->SSV_ALIAS])) {
                $result[] = new Component\SearchSpecification\EqualTo($searchField->SSV_EXPRESSION, $row[$searchField->SSV_ALIAS]);
            }
        }

        return $result;
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