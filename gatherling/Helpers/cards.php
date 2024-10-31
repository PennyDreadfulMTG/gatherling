<?php

declare(strict_types=1);

namespace Gatherling\Helpers;

/**
 * @param string|array<string> $cards
 * @return list<string>
 */
function parseCards(string|array $cards): array
{
    $cardarr = [];
    if (!is_array($cards)) {
        $cards = explode("\n", $cards);
    }
    foreach ($cards as $card) {
        // AE Litigation
        $card = normaliseCardName($card);
        if ($card != '') {
            $cardarr[] = $card;
        }
    }
    return $cardarr;
}

function normaliseCardName(string $card, bool $toLower = false): string
{
    $pattern = ['/é/', '/è/', '/ë/', '/ê/', '/É/', '/È/', '/Ë/', '/Ê/', '/á/', '/à/', '/ä/', '/â/', '/å/', '/Á/', '/À/', '/Ä/', '/Â/', '/Å/', '/ó/', '/ò/', '/ö/', '/ô/', '/Ó/', '/Ò/', '/Ö/', '/Ô/', '/í/', '/ì/', '/ï/', '/î/', '/Í/', '/Ì/', '/Ï/', '/Î/', '/ú/', '/ù/', '/ü/', '/û/', '/Ú/', '/Ù/', '/Ü/', '/Û/', '/ý/', '/ÿ/', '/Ý/', '/ø/', '/Ø/', '/œ/', '/Œ/', '/Æ/', '/AE/', '/ç/', '/Ç/', '/—/', '/−/', '/â€”/', '/’/', '/½/'];
    $replace = ['e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'ae', 'ae', 'Ae', 'Ae', 'c', 'C', '-', '-', '-', "'", '{1/2}'];
    $card = preg_replace($pattern, $replace, $card);
    $card = preg_replace("/\306/", 'AE', $card);
    $card = preg_replace("/ \/\/\/? /", '/', $card);
    if ($toLower) {
        $card = strtolower($card);
    }
    return trim($card);
}

/**
 * @param string|array<string> $cards
 * @return array<string, int>
 */
function parseCardsWithQuantity(string|array $cards): array
{
    $cards = parseCards($cards);
    $cardarr = [];
    foreach ($cards as $line) {
        $chopped = rtrim($line);
        if (preg_match("/^[ \t]*([0-9]+)x?[ \t]+(.*?)( \(\w+\) \d+)?$/i", $chopped, $m)) {
            $qty = (int) $m[1];
            $card = rtrim($m[2]);
            if (isset($cardarr[$card])) {
                $cardarr[$card] += $qty;
            } else {
                $cardarr[$card] = $qty;
            }
        }
    }
    return $cardarr;
}
