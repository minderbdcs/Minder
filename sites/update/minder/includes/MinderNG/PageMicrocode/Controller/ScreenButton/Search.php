<?php

namespace MinderNG\PageMicrocode\Controller\ScreenButton;

use MinderNG\Events\PublisherInterface;
use MinderNG\PageMicrocode\Component;

class Search implements HandlerInterface {
    const HANDLER_NAME = 'Search';

    public function execute(Component\Components $components, PublisherInterface $messageBus, Component\Form $form, Component\Button $button)
    {
        $messageBus->send(new \MinderNG\PageMicrocode\Command\Search($form));
    }
}