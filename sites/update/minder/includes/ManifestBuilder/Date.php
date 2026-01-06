<?php

class ManifestBuilder_Date extends Zend_Date {
    public function toAustPostDateTime() {
        //return $this->toString('y-MM-dTHH:mm:ss') . '.' . $this->getMilliSecond();
        return $this->toString('y-MM-ddTHH:mm:ss') . '.' . $this->getMilliSecond();
    }
}
