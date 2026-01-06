<?php

namespace MinderNG\PageMicrocode\JsonRpc;

use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Microcode;

class ClickFormButton {
    public function execute(Microcode $microcode, $formData, $button) {
        $form = $this->_prepareForm($microcode, $formData);
        $button = $microcode->getPageComponents()->buttonCollection->newButton($button);

        $microcode->getPublisher()->send(new Command\ButtonClick($form, $button));

        return $microcode->getPageComponents();
    }

    private function _prepareForm(Microcode $microcode, $formData) {
        $form = $microcode->getPageComponents()->forms->newForm($formData);

        if ($form->isEditResultForm()) {
            $microcode->getPublisher()->send(new Command\InitEditForm($form));
        }

        return $form;
    }
}