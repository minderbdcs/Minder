<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

class PageCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Page';
    }

    /**
     * @param \MinderNG\Collection\ModelAggregateInterface|null|string $idOrAggregate
     * @return \MinderNG\PageMicrocode\Component\Page|null
     */
    public function getPage($idOrAggregate) {
        return parent::get($idOrAggregate);
    }
}