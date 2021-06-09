<?php

namespace Gatherling;

function sortSets($a, $b)
{
    return strtotime($a->releaseDate) < strtotime($b->releaseDate);
}

class SetScraper
{
    public static function getSetList()
    { // gets a list of sets from magicthegathering.io

        $url = 'https://mtgjson.com/api/v5/SetList.json';

        $options = [
            'http'=> [
                'method'=> 'GET',
                'header'=> "Accept-language: en\r\n".
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n", // i.e. An iPad
            ],
        ];

        $context = stream_context_create($options);
        $sets = json_decode(file_get_contents($url, false, $context));
        if (!$sets) {
            return [];
        }
        $sets = $sets->data;
        usort($sets, 'sortSets');

        $dropdown = [];

        $knowncodes = Database::list_result('SELECT code FROM cardsets;');

        foreach ($sets as $s) {
            $installed = false;
            foreach ($knowncodes as $c) {
                if (strcasecmp($s->code, $c) == 0) {
                    $installed = true;
                }
            }
            if (!$installed) {
                $dropdown[$s->code] = $s->name;
            }
        }

        return $dropdown;
    }
}
