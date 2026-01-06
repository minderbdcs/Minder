<?php

namespace MinderNG\Filter;

class ParseInt implements FilterInterface {

    public function filter($value)
    {
        return is_numeric($value) ? intval($value, 10) : null;
    }
}