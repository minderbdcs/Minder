<?php

namespace MinderNG\PageMicrocode\Controller\ScreenButton;

use MinderNG\Events\PublisherInterface;
use MinderNG\PageMicrocode\Component;

interface HandlerInterface {
    const METHOD_NAME = 'execute';

    public function execute(Component\Components $components, PublisherInterface $messageBus, Component\Form $form, Component\Button $button);
}