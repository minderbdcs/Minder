<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;

class DeviceManager {
    public function getUserPrinterList(\Minder2_Model_SysUser $user) {
        $printers = array();

        foreach ($user->getAccessPrinterList() as $printer) {
            $printers[] = $printer->getFields();
        }

        $result = new DeviceCollection();
        $result->init($printers, new AddOptions(false, true));

        return $result;
    }

    public function getSelectedPrinter(\Minder2_Model_SysEquip $legacyPrinterModel) {
        $result = new Device();
        $result->init($legacyPrinterModel->getFields(), true);

        return $result;
    }
}