<?php

namespace MinderNG\Collection;

use MinderNG\Events;

interface ModelAggregateInterface extends Events\PublisherAggregateInterface {

    /**
     * @return ModelInterface
     */
    public function getModel();

    /**
     * @param $attributes
     * @return array
     */
    public function parse($attributes);
}