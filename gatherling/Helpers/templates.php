<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

use function Safe\iconv;
use function Safe\preg_replace;
use function Safe\preg_split;

// Our standard template variable naming is camelCase.
// Some of our objects have properties named in snake_case.
// So when we grab the values from an object to pass into
// a template with get_object_vars let's also preserve the
// naming standard by transforming the case.
// When it came to it this was less useful and more obsfucating
// than I hoped. Try not to use it anymore, I will remove it
// at some point. Just pass what the template needs explicitly,
// not the whole object.
/** @return array<int|string, mixed> */
function getObjectVarsCamelCase(object $obj): array
{
    $vars = get_object_vars($obj);
    return arrayMapRecursive(fn($key) => is_string($key) ? toCamel($key) : $key, $vars);
}

// https://stackoverflow.com/a/45440841/375262
function toCamel(string $string): string
{
    // Convert to ASCII, remove apostrophes, and split into words
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = str_replace("'", "", $string);
    $words = preg_split('/[^a-zA-Z0-9]+/', $string);

    // Convert each word to camel case
    $camelCase = array_map(function ($word) {
        // Split words that are already in camel case
        $word = preg_replace('/(?<=\p{Ll})(?=\p{Lu})/u', ' ', $word);
        /** @var string $word */
        $word = preg_replace('/(?<=\p{Lu})(?=\p{Lu}\p{Ll})/u', ' ', $word);
        $subWords = explode(' ', $word);

        // Lowercase each subword
        $subWords = array_map('strtolower', $subWords);
        // Capitalize each subword
        $subWords = array_map('ucfirst', $subWords);

        return implode('', $subWords);
    }, $words);

    // Join words and lowercase the first character
    $result = implode('', $camelCase);
    $result = lcfirst($result);
    return $result;
}

/**
 * @param array<int|string, mixed> $arr
 * @return array<int|string, mixed>
 */
function arrayMapRecursive(callable $func, array $arr): array
{
    $result = [];

    foreach ($arr as $key => $value) {
        $newKey = $func($key);

        if (is_array($value)) {
            $result[$newKey] = arrayMapRecursive($func, $value);
        } elseif (is_object($value)) {
            $result[$newKey] = getObjectVarsCamelCase($value);
        } else {
            $result[$newKey] = $value;
        }
    }

    return $result;
}
