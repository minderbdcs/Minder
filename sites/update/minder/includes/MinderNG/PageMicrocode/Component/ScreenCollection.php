<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;
use MinderNG\Collection\ModelAggregateInterface;
use MinderNG\PageMicrocode\Component\Exception\ScreenNotFound;

class ScreenCollection extends Collection {
    /**
     * @param Form $foundForm
     * @return Screen|null
     */
    public function getFormScreen(Form $foundForm) {
        return $this->findWhere(array(
            'SS_NAME' => $foundForm->SS_NAME
        ));
    }

    /**
     * @param DataSet $dataSet
     * @return Screen|null
     */
    public function getDataSetScreen(DataSet $dataSet) {
        return $this->findWhere(array(
            'SS_NAME' => $dataSet->SS_NAME
        ));
    }

    /**
     * @param $idOrAggregate
     * @return Screen|null
     */
    public function getScreen($idOrAggregate) {
        return $this->get($idOrAggregate);
    }

    public function findPageScreen(Page $page, $screenName) {
        return $this->findScreen(array(
            'SS_MENU_ID' => $page->SM_SUBMENU_ID,
            'SS_NAME' => $screenName,
        ));
    }

    /**
     * @param $idOrAggregate
     * @return Screen
     * @throws ScreenNotFound
     */
    public function findScreen($idOrAggregate) {
        $foundScreen = $this->getScreen($idOrAggregate);

        if (empty($foundScreen)) {
            throw new ScreenNotFound($this->newScreen($idOrAggregate));
        }

        return $foundScreen;
    }

    /**
     * @param $idOrAggregate
     * @return Screen|null
     */
    private function newScreen($idOrAggregate) {
        if (is_string($idOrAggregate)) {
            return $this->newModelInstance(array('SS_NAME' => $idOrAggregate));
        } elseif (is_array($idOrAggregate)) {
            return $this->newModelInstance($idOrAggregate);
        } elseif ($idOrAggregate instanceof ModelAggregateInterface) {
            return $idOrAggregate;
        }

        return null;
    }

    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Screen';
    }

}