<?php

function sortSets($a, $b) {
    return strtotime($a->releaseDate) < strtotime($b->releaseDate);
}

class SetScraper {


    static function getSetList() { // gets a list of sets from magiccards.info

        $url = "https://api.magicthegathering.io/v1/sets";

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $sets = json_decode(file_get_contents($url, false, $context));
        $sets = $sets->sets;
        usort($sets, 'sortSets');

        $dropdown = array();
        
        $knowncodes = Database::list_result("SELECT code FROM cardsets;");

        foreach ($sets as $s) {
            $installed = false;
            foreach ($knowncodes as $c){
                if ($s->code == $c){
                    $installed = true;
                }
            }
            if (!$installed) {
                $dropdown[$s->code] = $s->name;
            }
        }
        return $dropdown;
    }

    static function addNewSet($set, $releaseDate, $settype ) { // Imports the cards from the new set to the database

        function process_card( $node )        {
            $database = Database::getConnection();
            $stmt = $database->prepare("INSERT INTO cards(cost, convertedcost, name, cardset, type,
  isw, isu, isb, isr, isg, isp, rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");


            $children = $node->childNodes;

            $cardName = trim($children->item(2)->nodeValue);
            $cardType = $children->item(4)->nodeValue;
            $cardCost = $children->item(6)->nodeValue;
            $cardRarity = $children->item(8)->nodeValue;
            $cardSet = trim($children->item(12)->nodeValue);

            $card = array();
            $card['Name'] = $cardName;
            $card['Type'] = $cardType;
            $card['Cost'] = $cardCost;

            insertCard($card, $cardSet, $cardRarity, $stmt);

        }

        $url = "http://magiccards.info/".$set.".html";

        $options = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $html = file_get_contents($url, false, $context);

        $pokemon_doc = new DOMDocument();

        libxml_use_internal_errors(TRUE); //disable libxml errors

        if(!empty($html)){ //if any html is actually returned

            $pokemon_doc->loadHTML($html);
            libxml_clear_errors();

            $pokemon_xpath = new DOMXPath($pokemon_doc);
            $pokemon_row = $pokemon_xpath->query('//tr[@class]');
            $setName = trim($pokemon_row->item(0)->childNodes->item(12)->nodeValue);

            // Insert the card set
            $database = Database::getConnection();
            $stmt = $database->prepare("INSERT INTO cardsets(released, name, type) values(?, ?, ?)");
            $stmt->bind_param("sss", $releaseDate, $setName, $settype);

            if (!$stmt->execute()) {
                echo "!!!!!!!!!! Set Insertion Error !!!!!!!!!<br /><br /><br />";
                die($stmt->error);
            } else {
                echo "Inserted new {$settype} card set {$setName}!<br />Inserting cards for {$setName}...<br /><br />";
            }
            $stmt->close();

            //Insert cards for set
            if($pokemon_row->length > 0){
                foreach($pokemon_row as $row){
                    process_card($row);
                }
            }
        }

    }
}
