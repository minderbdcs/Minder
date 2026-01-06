<?php

class Minder_LocationGenerator_CheckDigit extends Minder_LocationGenerator {
    const MIN_DISTANCE = 5;

    /**
     * @param $locnId
     * @return Location|null
     */
    protected function _findLocation($locnId) {
        $result = Minder::getInstance()->fetchAssoc('SELECT * FROM LOCATION WHERE WH_ID = ? AND LOCN_ID = ?', $this->whId, $locnId);
        return empty($result) ? null : new Location($result);
    }

    protected function _nextDigit($upperBound, $checkDigitLength) {
        return str_pad(rand(0, $upperBound - 1), $checkDigitLength, '0', STR_PAD_LEFT);
    }

    public function doGenerate($checkDigitLength)
    {
        $this->checkSequenceInitialization();

        $result = array();

        $upperBound = (int)(str_pad('1', $checkDigitLength+1, '0', STR_PAD_RIGHT));

        foreach ($this->sequenceArray[Minder_LocationGenerator::AISLE] as $aisle) {
            foreach ($this->sequenceArray[Minder_LocationGenerator::BAY] as $bay) {
                foreach ($this->sequenceArray[Minder_LocationGenerator::SHELF] as $shelf) {
                    foreach ($this->sequenceArray[Minder_LocationGenerator::POSITION] as $position) {
                        $locnId = $aisle . $bay . $shelf . $position;

                        $location = $this->_findLocation($locnId);

                        if (empty($location)) {
                            continue;
                        }

                        $result[] = $location;
                    }
                }
            }
        }

        $fakeDigit = -self::MIN_DISTANCE * 100;
        foreach ($result as $key => $location) {
            /**
             * @var Location $location
             */
            $location->hadCheckDigit = $location->hasCheckDigit();

            if ($location->hasCheckDigit()) {
                continue;
            }

            $leftCheckDigit = isset($result[$key - 1]) ? $result[$key - 1]->items['LOCN_CHECK_DIGITS'] : $fakeDigit;
            $rightCheckDigit = isset($result[$key + 1]) ? $result[$key + 1]->items['LOCN_CHECK_DIGITS'] : $fakeDigit;

            $leftCheckDigit = (int)((!is_numeric($leftCheckDigit) && empty($leftCheckDigit)) ? $fakeDigit : $leftCheckDigit);
            $rightCheckDigit = (int)((!is_numeric($rightCheckDigit) && empty($rightCheckDigit)) ? $fakeDigit : $rightCheckDigit);

            $nextDigit = $this->_nextDigit($upperBound, $checkDigitLength);
            while (abs($nextDigit - $leftCheckDigit) < self::MIN_DISTANCE || abs($nextDigit - $rightCheckDigit) < self::MIN_DISTANCE) {
                $nextDigit = $this->_nextDigit($upperBound, $checkDigitLength);
            }

            $location->items['LOCN_CHECK_DIGITS'] = $nextDigit;
        }

        return $result;
    }


}

