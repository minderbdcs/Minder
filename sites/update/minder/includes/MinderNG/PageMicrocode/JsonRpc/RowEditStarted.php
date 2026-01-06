<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Microcode;

class RowEditStarted {
    public function execute(Microcode $microcode, $formData, $dataSetData, $dataSetRowData) {
        $form = $microcode->getPageComponents()->forms->findForm($formData);
        $dataSet = $microcode->getPageComponents()->dataSetCollection->findDataSet($dataSetData);
        $dataSetRow = $dataSet->getDataSetRowCollection()->findDataSetRow($dataSetRowData);

        $form->startEditDataSetRow($dataSet, $dataSetRow);
    }
}