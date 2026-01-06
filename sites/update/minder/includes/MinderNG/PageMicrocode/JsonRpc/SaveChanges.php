<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Microcode;

class SaveChanges {
    public function execute(Microcode $microcode, $form) {
        $form = $microcode->getPageComponents()->forms->newForm($form);

        $microcode->getPublisher()->send(new Command\InitEditForm($form));
        $microcode->getPublisher()->send(new Command\UpdateForm($form));
        $microcode->getPublisher()->send(new Command\SaveChanges($form));

        return $microcode->getPageComponents();
    }
}