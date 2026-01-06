<?php

namespace MinderNG\Collection;

interface IdAttributeProviderInterface {

    /**
     * @param array $attributes
     * @return mixed|null
     */
    public static function calculateId(array $attributes = array());

    /**
     * @return string
     */
    public static function getIdAttribute();
}