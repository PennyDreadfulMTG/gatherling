<?php

namespace Gatherling\Views;

function request(): Request
{
    static $instance = null;
    if ($instance === null) {
        $instance = new Request($_REQUEST);
    }
    return $instance;
}

function get(): Request
{
    static $instance = null;
    if ($instance === null) {
        $instance = new Request($_GET);
    }
    return $instance;
}

function post(): Request
{
    static $instance = null;
    if ($instance === null) {
        $instance = new Request($_POST);
    }
    return $instance;
}
