<?php

class Minder_LocationGenerator_Sequence extends Minder_LocationGenerator {
    /**
     * @param $locnId
     * @return Location|null
     */
    protected function _findLocation($locnId) {
        $result = Minder::getInstance()->fetchAssoc('SELECT * FROM LOCATION WHERE WH_ID = ? AND LOCN_ID = ?', $this->whId, $locnId);
        return empty($result) ? null : new Location($result);
    }

    protected function _formatAlternativeId($values, $format) {
        $replacements = array();
        foreach ($values as $name => $value) {
            $replacements['%' . strtoupper($name) . '%'] = $value;
        }

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    public function doGenerate($settings, $format, $field, &$locnIdStartsFrom, $locnIdEndsWith, $updateOtherInputs, $locnNameGenerator)
    {
        $result = array();
        $field = (strtolower($field) == 'alt') ? 'LOCN_ID2' : 'LOCN_SEQ';

        $altGenerator = Minder_SequenceGenerator_Factory::createComplexGenerator($settings);

        $altGenerator->rewind();
        if (!$altGenerator->valid()) {
            return $result; //sequence has no more elements
        }

        $generator = new Minder_SequenceGenerator_Composite(array(
            $this->sequenceArray[Minder_LocationGenerator::POSITION],
            $this->sequenceArray[Minder_LocationGenerator::SHELF],
            $this->sequenceArray[Minder_LocationGenerator::BAY],
            $this->sequenceArray[Minder_LocationGenerator::AISLE]
        ), $locnIdStartsFrom, $locnIdEndsWith);

        $params                     = $this->_prepareParams($this->items);
        $params['%WH_ID%']          = $this->whId;

        foreach ($generator as $element) {
            if ($locnNameGenerator == 'LOCN') {
                $params['%AISLE%']                      = $element[Minder_LocationGenerator::AISLE];
                $params['%BAY%']                        = $element[Minder_LocationGenerator::BAY];
                $params['%SH%']                         = $element[Minder_LocationGenerator::SHELF];
                $params['%POS%']                        = $element[Minder_LocationGenerator::POSITION];
            } else {
                $tmpElement                             = $altGenerator->current();
                $params['%AISLE%']                      = $tmpElement['aisle'];
                $params['%BAY%']                        = $tmpElement['bay'];
                $params['%SH%']                         = $tmpElement['sh'];
                $params['%POS%']                        = $tmpElement['pos'];
                $params['%SUB%']                        = $tmpElement['sub'];
            }

            $locnIdStartsFrom = $locnId = $element[Minder_LocationGenerator::AISLE] . $element[Minder_LocationGenerator::BAY] . $element[Minder_LocationGenerator::SHELF] . $element[Minder_LocationGenerator::POSITION];

            $location = $this->_findLocation($locnId);

            if (empty($location)) {
                continue;
            }

            if ($updateOtherInputs) {
                $location->items = array_merge($location->items, $this->items);
                $otherInputsParams = array_merge($this->_prepareParams($location->items), $params);
                $location->items['LOCN_NAME'] = $this->_fillParameters($location->items['LOCN_NAME'], $otherInputsParams);
            }

            $location->items[$field] = $this->_formatAlternativeId($altGenerator->current(), $format);
            $result[] = $location;

            $altGenerator->next();
            if (!$altGenerator->valid()) {

                $generator->next();

                if ($generator->valid()) {
                    $element = $generator->current();
                    $locnIdStartsFrom  = $element[Minder_LocationGenerator::AISLE] . $element[Minder_LocationGenerator::BAY] . $element[Minder_LocationGenerator::SHELF] . $element[Minder_LocationGenerator::POSITION];
                } else {
                    $locnIdStartsFrom = '';
                }
                return $result; //sequence has no more elements
            }
        }

        $locnIdStartsFrom = '';
        if (!empty($locnIdEndsWith)) {
            $generator = new Minder_SequenceGenerator_Composite(array(
                $this->sequenceArray[Minder_LocationGenerator::POSITION],
                $this->sequenceArray[Minder_LocationGenerator::SHELF],
                $this->sequenceArray[Minder_LocationGenerator::BAY],
                $this->sequenceArray[Minder_LocationGenerator::AISLE]
            ), $locnIdEndsWith);
            $generator->rewind();
            $generator->next();
            if ($generator->valid()) {
                $element = $generator->current();
                $locnIdStartsFrom  = $element[Minder_LocationGenerator::AISLE] . $element[Minder_LocationGenerator::BAY] . $element[Minder_LocationGenerator::SHELF] . $element[Minder_LocationGenerator::POSITION];
            }
        }

        return $result;
    }

}