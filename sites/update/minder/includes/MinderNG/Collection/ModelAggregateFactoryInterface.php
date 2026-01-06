<?php

namespace MinderNG\Collection;

interface ModelAggregateFactoryInterface {
    public function getIdAttribute();

    /**
     * @param $attributes
     * @return mixed|null
     */
    public function calculateId(array $attributes = array());

    public function newInstance($aggregateData, $collection, $parse = false, $silent = false);
}