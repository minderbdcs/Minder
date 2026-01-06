<?php

namespace MinderNG\PageMicrocode\Controller\ScreenButton;

use MinderNG\Events\PublisherInterface;
use MinderNG\PageMicrocode\Component;

class CancelChanges implements HandlerInterface {
    const HANDLER_NAME = 'CancelChanges';

    public function execute(Component\Components $components, PublisherInterface $messageBus, Component\Form $form, Component\Button $button)
    {
        $foundForm = $components->forms->findForm($form);

        foreach ($foundForm->getWorkingDataSetCollection() as $dataSet) {
            $foundDataSet = $components->dataSetCollection->findDataSet($dataSet);

            foreach ($dataSet->getDataSetRowCollection() as $dataSetRow) {
                $foundRow = $foundDataSet->getDataSetRowCollection()->findDataSetRow($dataSetRow);
                $foundForm->startEditDataSetRow($foundDataSet, $foundRow);
            }
        }
    }
}