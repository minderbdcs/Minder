<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenTab;

class TabManager {
    private $_tabProvider;

    function __construct(SysScreenTab $tabProvider)
    {
        $this->_tabProvider = $tabProvider;
    }

    public function getScreenTabs(ScreenCollection $screens, FormCollection $forms, TableCollection $tables, FieldCollection $fields, \Minder2_Environment $environment) {
        $fieldTabs = $this->_populateFieldTabs($fields);
        $screenTabs = $this->_fetchScreenTabs($screens, $environment);
        $mergedTabs = $this->_mergeTabCollections($screenTabs, $fieldTabs);
        return $this->_addDefaultTabs($mergedTabs, $forms, $tables) ;
    }

    /**
     * @param ScreenCollection $screens
     * @param \Minder2_Environment $environment
     * @return TabCollection
     * @throws \Minder_Exception
     */
    private function _fetchScreenTabs(ScreenCollection $screens, \Minder2_Environment $environment)
    {
        $tabs = $this->_tabProvider->getScreenTabs(
            iterator_to_array($screens->pluck('SS_NAME')),
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );

        $result = new TabCollection();
        $result->init($tabs, new AddOptions(false, true));

        return $result;
    }

    private function _populateFieldTabs(FieldCollection $fields) {
        $result = new TabCollection();
        $result->init();

        foreach ($fields as $field) {
            /** @var Field $field */

            if ($field->isStatusOk()) {
                if (!$field->belongsToAllTabs()) {
                    $result->add(array(array(
                        Tab::FIELD_SCREEN_NAME => $field->SS_NAME,
                        Tab::FIELD_VARIANCE => $field->SS_VARIANCE,
                        Tab::FIELD_FORM_NAME => $field->SSF_NAME,
                        Tab::FIELD_TYPE => $field->SSV_FIELD_TYPE,
                        Tab::FIELD_TAB_NAME => $field->SSV_TAB,
                        Tab::FIELD_STATUS => Tab::STATUS_OK,
                    )));
                }
            }
        }

        return $result;
    }

    private function _mergeTabCollections(TabCollection $screenTabs, TabCollection $fieldTabs) {

        foreach ($fieldTabs as $fieldTab) {
            /** @var Tab $fieldTab */

            $foundScreenTab = $screenTabs->findWhere(array(
                Tab::FIELD_SCREEN_NAME => $fieldTab->SS_NAME,
                Tab::FIELD_VARIANCE => $fieldTab->SS_VARIANCE,
                Tab::FIELD_FORM_NAME => $fieldTab->SSF_NAME,
                Tab::FIELD_TYPE => $fieldTab->SST_FIELD_TYPE,
                Tab::FIELD_TAB_NAME => $fieldTab->SST_TAB_NAME,
            ));

            if (empty($foundScreenTab)) {
                $screenTabs->add(array($fieldTab));
            }
        }

        return $screenTabs;
    }

    private function _addDefaultTabs(TabCollection $screenTabs, FormCollection $forms, TableCollection $tables) {
        foreach ($forms as $form) {
            /** @var Form $form */

            if ($form->isStatusOk()) {
                foreach ($tables as $table) {
                    /** @var Table $table */

                    if ($table->isStatusOk()) {
                        $foundScreenTab = $screenTabs->findWhere(array(
                            Tab::FIELD_SCREEN_NAME => $form->SS_NAME,
                            Tab::FIELD_VARIANCE => $table->SS_VARIANCE,
                            Tab::FIELD_FORM_NAME => $form->SSF_NAME,
                            Tab::FIELD_TYPE => $form->SSF_TYPE,
                        ));

                        if (empty($foundScreenTab)) {
                            $screenTabs->add(array(array(
                                Tab::FIELD_SCREEN_NAME => $form->SS_NAME,
                                Tab::FIELD_VARIANCE => $table->SS_VARIANCE,
                                Tab::FIELD_FORM_NAME => $form->SSF_NAME,
                                Tab::FIELD_TYPE => $form->SSF_TYPE,
                                Tab::FIELD_TAB_NAME => Tab::DEFAULT_NAME,
                                Tab::FIELD_STATUS => Tab::STATUS_OK,
                            )));
                        }
                    }
                }
            }
        }

        return $screenTabs;
    }
}