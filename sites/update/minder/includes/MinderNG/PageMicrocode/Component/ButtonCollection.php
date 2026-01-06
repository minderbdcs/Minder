<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\Collection;

/**
 * Class ButtonCollection
 * @package MinderNG\PageMicrocode\Component
 *
 * @method Button|null get($idOrAggregate)
 */
class ButtonCollection extends Collection {
    protected function _getDefaultModelClassName()
    {
        return __NAMESPACE__ . '\\Button';
    }

    /**
     * @param Button|string|array $idOrAggregate
     * @return Button
     * @throws Exception\ButtonNotFound
     */
    public function findButton($idOrAggregate) {
        $button = $idOrAggregate instanceof Button ? $idOrAggregate : $this->newButton($idOrAggregate);

        $foundButton = $this->get($button);

        if (empty($foundButton)) {
            throw new Exception\ButtonNotFound($button);
        }

        return $foundButton;
    }

    /**
     * @param array|string $idOrAggregate
     * @return Button
     */
    public function newButton($idOrAggregate) {
        $attributes = is_string($idOrAggregate) ? Button::parseId($idOrAggregate) : $idOrAggregate;
        return $this->newModelInstance($attributes);
    }
}