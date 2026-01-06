<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Microcode;

class GetPageComponents {
    public function execute(Microcode $pageMicrocode) {
        return $pageMicrocode->getPageComponents();
    }
}