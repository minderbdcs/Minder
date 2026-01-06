<?php

namespace MinderNG\PageMicrocode\Controller\ScreenButton;

use MinderNG\Events\PublisherInterface;
use MinderNG\PageMicrocode\Component;

class ClearSearch implements HandlerInterface {
    const HANDLER_NAME = 'ClearSearch';

    public function execute(Component\Components $components, PublisherInterface $messageBus, Component\Form $form, Component\Button $button)
    {
        $messageBus->send(new \MinderNG\PageMicrocode\Command\ClearSearch($form));
    }
}