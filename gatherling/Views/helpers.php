<?php

namespace Gatherling\Views;

function request(): Request
{
    return new Request($_REQUEST);
}

function get(): Request
{
    return new Request($_GET);
}

function post(): Request
{
    return new Request($_POST);
}

function session(): Request
{
    return new Request($_SESSION);
}

function server(): Request
{
    return new Request($_SERVER);
}

function config(): Request
{
    global $CONFIG;
    return new Request($CONFIG);
}
