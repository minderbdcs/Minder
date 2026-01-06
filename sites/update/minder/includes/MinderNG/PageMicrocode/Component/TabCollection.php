<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;
use MinderNG\PageMicrocode\Component\Exception\TabNotFound;

/**
 * Class TabCollection
 * @package MinderNG\PageMicrocode\Component
 *
 * @method Tab get($idOrAggregate)
 */
class TabCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Tab';
    }

    public function getFormActiveTab(Form $form) {
        return $this->findWhere(array(
            Tab::FIELD_SCREEN_NAME => $form->SS_NAME,
            Tab::FIELD_FORM_NAME => $form->SSF_NAME,
            Tab::FIELD_TYPE => $form->SSF_TYPE,
            Tab::FIELD_STATUS => Tab::STATUS_OK,
            Tab::FIELD_ACTIVE => function($active) {return !!$active;},
        ));
    }

    /**
     * @param Form $form
     * @return \Iterator|Tab[]
     */
    public function getFormTabList(Form $form) {
        return $this->where(array(
            Tab::FIELD_SCREEN_NAME => $form->SS_NAME,
            Tab::FIELD_FORM_NAME => $form->SSF_NAME,
            Tab::FIELD_TYPE => $form->SSF_TYPE,
            Tab::FIELD_STATUS => Tab::STATUS_OK,
        ));
    }

    /**
     * @param Tab|string|array $idOrAggregate
     * @return Tab
     * @throws TabNotFound
     */
    public function findTab($idOrAggregate) {
        $tab = ($idOrAggregate instanceof Tab) ? $idOrAggregate : $this->newTab($idOrAggregate);

        $foundTab = $this->get($tab);

        if (empty($foundTab)) {
            throw new TabNotFound($tab);
        }

        return $foundTab;
    }

    /**
     * @param $idOrAggregate
     * @return Tab
     */
    public function newTab($idOrAggregate) {
        return is_array($idOrAggregate) ? $this->newModelInstance($idOrAggregate) : $this->newModelInstance(Tab::parseId($idOrAggregate));
    }
}