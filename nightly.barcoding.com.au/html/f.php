<?php

function isCommandLineInterface()
{
    return (php_sapi_name() === 'cli');
}

echo php_sapi_name();
?>
