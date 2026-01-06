<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Microcode;

class SelectPrinter {
    public function execute(Microcode $microcode, $printer) {
        $printer = $microcode->getPageComponents()->printerList->findDevice($printer);
        $microcode->getPageComponents()->selectedPrinter->set($printer->getAttributes());
        return $microcode->getPageComponents()->getArrayCopy();
    }
}