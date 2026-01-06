<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Command\Search;
use MinderNG\PageMicrocode\Microcode;

class ExecuteSearch {
    public function execute(Microcode $microcode, $searchFormData) {
        $form = $microcode->getPageComponents()->forms->newForm($searchFormData);

        $microcode->getPublisher()->send(new Search($form));

        return $microcode->getPageComponents()->getArrayCopy();
    }
}