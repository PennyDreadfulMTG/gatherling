<?php

namespace Gatherling\Views;

function request(): Request
{
    static $instance = null;
    if ($instance === null) {
        $instance = new Request($_REQUEST, $_GET, $_POST);
    }
    return $instance;
}
