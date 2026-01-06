<?php

namespace MinderNG\PageMicrocode\Controller;

use Minder;
use MinderNG\Collection\AddOptions;
use MinderNG\Collection\Helper\Helper;
use MinderNG\Events;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Event\PageLoad;

class FieldDataSet implements Events\SubscriberAggregateInterface {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Controller\\FieldDataSet';

    /**
     * @var Events\SubscriberInterface
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

    /**
     * @var Helper
     */
    private $_collectionHelper;

    function __construct(Helper $collectionHelper)
    {
        $this->_collectionHelper = $collectionHelper;
    }


    public function init(Component\Components $pageComponents, Events\PublisherAggregateInterface $messageBus) {
        $this->_pageComponents = $pageComponents;
        $this->_messageBus = $messageBus;

        $this->getSubscriber()->subscribeTo($messageBus, PageLoad::EVENT_NAME, 'onPageLoad');
    }

    public function onPageLoad() {
        foreach($this->_pageComponents->dataSetCollection->filterFieldDataSets() as $fieldDataSet) {
            $fieldDataSet->getDataSetRowCollection()->reset($this->_fetchDataSetRows($fieldDataSet), new AddOptions(true, true));
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

    private function _fetchDataSetRows(Component\DataSet $fieldDataSet) {
        $field = $this->_pageComponents->fields->getField($fieldDataSet->FIELD);
        $result = array();

        try {
            $result = $this->_getMinder()->fetchAllAssoc($field->SSV_DROPDOWN_SQL);
        } catch (\Exception $exception) {
            //todo: log exception
        }

        return iterator_to_array($this->_getCollectionHelper()->map(new \ArrayIterator($result), function($rowData){
            $rowValues = array_values($rowData);

            if (isset($rowData['PRIMARY_ID'])) {
                $value = $rowData['PRIMARY_ID'];
            } elseif (isset($rowData['KEY'])) {
                $value = $rowData['KEY'];
            } else {
                $value = isset($rowValues[0]) ? $rowValues[0] : '';
            }

            if (isset($rowData['LABEL'])) {
                $label = $rowData['LABEL'];
            } else {
                $label = isset($rowValues[1]) ? $rowValues[1] : $value;
            }

            $rowData['PRIMARY_ID'] = $value;
            $rowData['LABEL'] = $label;

            return $rowData;
        }));
    }

    private function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @return Helper
     */
    private function _getCollectionHelper()
    {
        return $this->_collectionHelper;
    }
}