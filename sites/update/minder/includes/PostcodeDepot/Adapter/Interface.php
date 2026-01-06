<?php

interface PostcodeDepot_Adapter_Interface {
    /**
     * @param mixed $source
     * @return PostcodeDepot
     */
    public function convert($source);
}