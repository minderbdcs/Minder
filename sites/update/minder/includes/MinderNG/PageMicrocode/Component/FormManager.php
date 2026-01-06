<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenForm;

class FormManager {
    private $_formProvider;

    function __construct(SysScreenForm $formProvider)
    {
        $this->_formProvider = $formProvider;
    }

    public function getScreenForms(ScreenCollection $screens, FieldCollection $fields, \Minder2_Environment $environment) {
        $fieldForms = $this->_populateFieldFormCollection($fields);
        $screenForms = $this->_fetchScreenForms($screens, $environment);

        return $this->_mergeFormCollections($screenForms, $fieldForms);
    }

    /**
     * @param ScreenCollection $screens
     * @param \Minder2_Environment $environment
     * @return FormCollection
     * @throws \Minder_Exception
     */
    private function _fetchScreenForms(ScreenCollection $screens, \Minder2_Environment $environment)
    {
        $forms = $this->_formProvider->getScreenForms(
            array_unique(iterator_to_array($screens->pluck('SS_NAME'))),
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );

        $result = new FormCollection();
        $result->init($forms, new AddOptions(false, true));

        return $result;
    }

    private function _populateFieldFormCollection(FieldCollection $fields) {
        $result = new FormCollection();
        $result->init();

        foreach ($fields as $field) {
            /** @var Field $field */

            if ($field->isStatusOk()) {
                $result->add(array(array(
                    Form::FIELD_SCREEN_NAME => $field->SS_NAME,
                    Form::FIELD_FORM_NAME => $field->SSF_NAME,
                    Form::FIELD_FORM_TYPE => $field->SSV_FIELD_TYPE,
                    Form::FIELD_STATUS => Form::STATUS_OK,
                )), new AddOptions(true, true, true));
            }
        }

        return $result;
    }

    private function _mergeFormCollections(FormCollection $screenForms, FormCollection $fieldForms) {
        foreach ($fieldForms as $form) {
            /** @var Form $form */

            $foundForm = $screenForms->findWhere(array(
                Form::FIELD_SCREEN_NAME => $form->SS_NAME,
                Form::FIELD_FORM_NAME => $form->SSF_NAME,
                Form::FIELD_FORM_TYPE => $form->SSF_TYPE,
            ));

            if (empty($foundForm)) {
                $screenForms->add(array($form));
            }
        }

        return $screenForms;
    }

}