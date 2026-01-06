<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Command\SearchDataSet;
use MinderNG\PageMicrocode\Component\Components;
use MinderNG\PageMicrocode\Component\SearchSpecification\EqualTo;
use MinderNG\PageMicrocode\Event\StartEditDataSetRow;
use MinderNG\PageMicrocode\Microcode;

class EditRow {
    public function execute(Microcode $microcode, $screenName, $formName, $searchCriteria) {
        $components = $microcode->getPageComponents();

        $screen = $components->screens->findPageScreen($microcode->getPageComponents()->page, $screenName);
        $form = $components->forms->findScreenEditForm($screen, $formName);

        $searchSpecificationRow = $this->_createSearchSpecificationRow($searchCriteria);

        foreach ($components->dataSetCollection->getAllScreenDataSets($screen) as $dataSet) {
            if ($dataSet->isMain()) {
                $microcode->getPublisher()->send(new SearchDataSet($dataSet, $this->_createDataSetSearchSpecification($components, $dataSet, $searchSpecificationRow)));

                if (count($dataSet->getDataSetRowCollection()) > 0) {
                    $microcode->getPublisher()->trigger(new StartEditDataSetRow(
                        $dataSet,
                        $dataSet->getDataSetRowCollection()->first(),
                        $form
                    ));
                }
            }
        }

        return $components;
    }

    private function _createDataSetSearchSpecification(Components $pageComponents, $dataSet, $searchSpecificationRow) {
        $result = $pageComponents->getDataSetSearchSpecifications()->newDataSetSearchSpecification($dataSet);
        $result->addRow($searchSpecificationRow);

        return $result;
    }

    private function _createSearchSpecificationRow($searchCriteria) {
        $result = array();

        foreach ($searchCriteria as $expression => $value) {
            $result[] = new EqualTo($expression, $value);
        }

        return $result;
    }
}