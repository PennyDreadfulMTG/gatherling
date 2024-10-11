<?php

declare(strict_types=1);

namespace Gatherling\Models;

class SetScraper
{
    /** @return array<string, string> */
    public static function getSetList(): array
    {
        // gets a list of sets from magicthegathering.io
        $url = 'https://mtgjson.com/api/v5/SetList.json';

        $options = [
            'http' => [
                'method' => 'GET',
                'header' => "Accept-language: en\r\n" .
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n", // i.e. An iPad
            ],
        ];

        $context = stream_context_create($options);
        $s = file_get_contents($url, false, $context);
        $sets = json_decode($s);
        if (!$sets) {
            return [];
        }
        $sets = $sets->data;

        $knowncodes = Database::listResult('SELECT code FROM cardsets;');

        // Turn this into a dict for faster lookup
        $knowncodesDict = [];
        foreach ($knowncodes as $k => $v) {
            // Some codes are NULL in the db, including for Alara Reborn, Ninth Edition and others. I'm not solving that now.
            if ($v) {
                $knowncodesDict[$v] = true;
            }
        }

        $unknownSets = array_filter($sets, function ($set) use ($knowncodesDict) {
            return !isset($knowncodesDict[$set->code]);
        });

        $dropdown = [];
        foreach ($unknownSets as $unknownSet) {
            $dropdown[$unknownSet->code] = $unknownSet->name;
        }
        asort($dropdown);

        return $dropdown;
    }
}
