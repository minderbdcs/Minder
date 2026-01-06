<?php

namespace MinderNG\PageMicrocode\Error;

interface ErrorInterface {
    const FORM_NOT_FOUND = 'FORM_NOT_FOUND';

    public function getMessage();

    public function getCode();
}