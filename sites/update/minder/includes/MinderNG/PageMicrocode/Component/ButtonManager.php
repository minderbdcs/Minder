<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenButton;
use MinderNG\PageMicrocode\Controller\ScreenButton;

class ButtonManager {
    /**
     * @var SysScreenButton
     */
    private $_buttonManager;

    function __construct(SysScreenButton $buttonManager)
    {
        $this->_buttonManager = $buttonManager;
    }


    public function getScreenButtons(ScreenCollection $screens, FormCollection $forms, \Minder2_Environment $environment) {
        $buttons = $this->_buttonManager->getScreenButtonCollection(
            iterator_to_array($screens->pluck('SS_NAME')),
            $environment->getCurrentUser()
        );

        $result = new ButtonCollection();
        $result->init($buttons, new AddOptions(true, true));

        return $this->_addDefaultButtons($result, $forms);
    }

    private function _addDefaultButtons(ButtonCollection $buttons, FormCollection $forms) {
        /** @var Form $form */
        foreach($forms as $form) {
            if ($form->isStatusOk()) {
                if ($form->isEditResultForm()) {
                    $buttons = $this->_addResultFormButtons($buttons, $form);
                } elseif ($form->isSearchForm()) {
                    $buttons = $this->_addSearchFormButtons($buttons, $form);
                }
            }

        }

        return $buttons;
    }

    private function _addSearchFormButtons(ButtonCollection $buttons, Form $form) {
        $buttons->add(array(
            $this->_newButtonConfig($form, Button::BUTTON_SAVE, ScreenButton\Search::HANDLER_NAME, 'Search', -20),
            $this->_newButtonConfig($form, Button::BUTTON_RESET, ScreenButton\ClearSearch::HANDLER_NAME, 'Clear', -10),
        ), new AddOptions(true, true));

        return $buttons;
    }

    private function _addResultFormButtons(ButtonCollection $buttons, Form $form) {
        $buttons->add(array(
            $this->_newButtonConfig($form, Button::BUTTON_SAVE, ScreenButton\SaveEditResult::HANDLER_NAME, 'Save', -20),
            $this->_newButtonConfig($form, Button::BUTTON_RESET, ScreenButton\CancelChanges::HANDLER_NAME, 'Cancel Changes', -10),
        ), new AddOptions(true, true));

        return $buttons;
    }

    /**
     * @param Form $form
     * @param string $name
     * @param string $handler
     * @param string $title
     * @param $sequence
     * @return array
     */
    private function _newButtonConfig(Form $form, $name, $handler, $title, $sequence)
    {
        return array(
            Button::FIELD_SCREEN_NAME => $form->SS_NAME,
            Button::FIELD_FORM_NAME => $form->SSF_NAME,
            Button::FIELD_TYPE => $form->SSF_TYPE,
            Button::FIELD_STATUS => Button::STATUS_OK,
            Button::FIELD_NAME => $name,
            Button::FIELD_INTERNAL_HANDLER => $handler,
            Button::FIELD_TITLE => $title,
            Button::FIELD_SEQUENCE => $sequence,
        );
    }
}