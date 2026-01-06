<?php

namespace MinderNG;

interface JsonSerializableInterface {
    /**
     * @return mixed
     */
    public function jsonSerialize();
}