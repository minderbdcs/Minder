<?php

class PostcodeDepot_Collection extends AbstractModel_Collection {
    public function __construct()
    {
        parent::__construct(new PostcodeDepot());
    }

}