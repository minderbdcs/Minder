<?php

namespace MinderNG\PageMicrocode\Controller;

use MinderNG\Events;
use MinderNG\PageMicrocode\Command\ButtonClick;
use MinderNG\PageMicrocode\Component;
use MinderNG\PageMicrocode\Microcode;

class ScreenButton implements Events\SubscriberAggregateInterface {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\Controller\\ScreenButton';

    /** @var Events\Subscriber */
    private $_subscriber;

    /** @var Component\Components */
    private $_components;

    /** @var  Microcode */
    private $_microcode;

    public function init(Microcode $microcode) {
        $this->_microcode = $microcode;
        $this->_components = $microcode->getPageComponents();

        $this->getSubscriber()->subscribeTo($microcode, ButtonClick::COMMAND_NAME, 'onButtonClick');
    }

    public function onButtonClick(ButtonClick $command, Component\Form $form, Component\Button $button) {
        $this->_executeHandler(
            $this->_getHandler($this->_components->buttonCollection->findButton($button)),
            $form,
            $button
        );

        $command->setResponse(true);
    }

    /**
     * @return Events\SubscriberInterface
     */
    public function getSubscriber()
    {
        if (empty($this->_subscriber)) {
            $this->_subscriber = new Events\Subscriber($this);
        }

        return $this->_subscriber;

    }

    private function _getHandler(Component\Button $foundButton) {
        if (empty($foundButton->INTERNAL_HANDLER)) {
            return create_function('$pageComponents, $messageBus, $form, $button', $foundButton->SSB_ACTION);
        } else {
            return array(
                $this->_getInternalHandler($foundButton->INTERNAL_HANDLER),
                ScreenButton\HandlerInterface::METHOD_NAME
            );
        }
    }

    private function _executeHandler($handler, Component\Form $form, Component\Button $button) {
        return call_user_func_array($handler, array(
            $this->_components,
            $this->_microcode->getPublisher(),
            $form,
            $button
        ));
    }

    /**
     * @param $name
     * @return ScreenButton\HandlerInterface
     * @throws \Exception
     */
    private function _getInternalHandler($name) {
        return $this->_microcode->getDice()->create(__NAMESPACE__ . '\\ScreenButton\\' . $name);
    }
}