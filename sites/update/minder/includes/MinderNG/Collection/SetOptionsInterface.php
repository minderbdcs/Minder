<?php

namespace MinderNG\Collection;

interface SetOptionsInterface {
    const SILENT = 'silent';
    const PARSE = 'parse';
    const ADD = 'add';
    const REMOVE = 'remove';
    const MERGE = 'merge';

    /**
     * @return bool
     */
    public function silent();

    /**
     * @return bool
     */
    public function parse();

    /**
     * @return bool
     */
    public function add();

    /**
     * @return bool
     */
    public function merge();

    /**
     * @return bool
     */
    public function remove();
}