<?php

namespace MinderNG\Collection;

interface ModelInterface extends \ArrayAccess {
    const ID_ATTRIBUTE = 'id';

    public function init($attributes = null, $parse = false, $silent = false, $collection = null);

    public function isInCollection();

    public function clear($silent = false);

    public function hasChanged($field = null);

    public function changedAttributes(array $diff = null);

    /**
     * @return null|Collection
     */
    public function getCollection();

    /**
     * @param null|Collection $collection
     * @return $this
     */
    public function setCollection(Collection $collection = null);

    public function previous($fieldName);

    public function previousId();

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getCid();

    /**
     * @return array
     */
    public function getAttributes();

    public function set(array $values, $silent = false, $unset = false, $parse = false);

    /**
     * @param bool $full
     * @return array
     */
    public function getArrayCopy($full = false);
}