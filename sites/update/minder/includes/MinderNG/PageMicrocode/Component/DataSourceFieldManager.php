<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class DataSourceFieldManager {
    public function getDataSourceFieldCollection(FieldCollection $fields) {
        $tmpFieldArray = array();
        foreach ($fields->filterDataSourceFields() as $field) {
            $tmpFieldArray[] = $field->getAttributes();
        }

        $result = new DataSourceFieldCollection();
        $result->init($tmpFieldArray, new AddOptions(false, true));

        return $result;
    }
}