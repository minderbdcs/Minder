<?php

namespace MinderNG\PageMicrocode\Component\Exception;

use Exception;
use MinderNG\PageMicrocode\Component\Device;

class DeviceNotFound extends Exception {
    public function __construct(Device $device, $code = 0, Exception $previous = null)
    {
        parent::__construct('Device #' . $device->getId() . ' not found.', $code, $previous);
    }

}