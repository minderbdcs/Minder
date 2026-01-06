<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class DeviceCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Device';
    }

    /**
     * @param string|array|Device $idOrAggregate
     * @return Device
     * @throws Exception\DeviceNotFound
     */
    public function findDevice($idOrAggregate) {
        $device = ($idOrAggregate instanceof Device) ? $idOrAggregate : $this->newDevice($idOrAggregate);
        $foundDevice = $this->get($device);

        if (empty($foundDevice)) {
            throw new Exception\DeviceNotFound($device);
        }

        return $foundDevice;

    }

    /**
     * @param $idOrAggregate
     * @return Device
     */
    public function newDevice($idOrAggregate) {
        return $this->newModelInstance(is_string($idOrAggregate)  ? array('DEVICE_ID' => $idOrAggregate) : $idOrAggregate);
    }
}