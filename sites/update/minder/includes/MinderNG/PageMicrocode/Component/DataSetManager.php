<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class DataSetManager {
    /**
     * @param FieldCollection $fieldCollection
     * @return DataSetCollection
     */
    public function getScreenDataSets(FieldCollection $fieldCollection) {
        $result = new DataSetCollection();
        $result->init();
        $addOptions = new AddOptions(false, true);

        foreach ($fieldCollection as $field) {
            /** @var Field $field */
            $result->add(array(
                array(
                    'SS_NAME' => $field->SS_NAME,
                    'SS_VARIANCE' => $field->SS_VARIANCE,
                )
            ), $addOptions);

            if (!empty($field->SSV_DROPDOWN_SQL)) {
                $result->add(array(
                    array(
                        'SS_NAME' => $field->SS_NAME,
                        'SS_VARIANCE' => $field->SS_VARIANCE,
                        'FIELD' => $field->getId(),
                    )
                ), $addOptions);
            }
        }

        return $result;
    }
}