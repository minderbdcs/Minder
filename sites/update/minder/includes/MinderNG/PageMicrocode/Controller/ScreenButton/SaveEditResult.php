<?php

namespace MinderNG\PageMicrocode\Controller\ScreenButton;

use MinderNG\Events\PublisherInterface;
use MinderNG\PageMicrocode\Command;
use MinderNG\PageMicrocode\Component;

class SaveEditResult implements HandlerInterface {
    const HANDLER_NAME = 'SaveEditResult';

    public function execute(Component\Components $components, PublisherInterface $messageBus, Component\Form $form, Component\Button $button)
    {
        $messageBus->send(new Command\UpdateForm($form));
        $messageBus->send(new Command\SaveChanges($form));
    }
}