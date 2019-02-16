<?php

$data = filter_input(INPUT_POST, 'data');
$xml = simplexml_load_string($data) or die('Error: Cannot create object');
$maindeck = [];
$sideboard = [];
$noOfRows = count($xml->Cards);
for ($x = 0; $x < $noOfRows; $x++) {
    $nameToAdd = strval($xml->Cards[$x]['Name']);
    $quantityToAdd = intval($xml->Cards[$x]['Quantity']);
    if ($xml->Cards[$x]['Sideboard'] == 'false') { //Maindeck
        if ($x < $noOfRows - 1) { //Not the last line
            if (($nameToAdd == strval($xml->Cards[$x + 1]['Name'])) && ($xml->Cards[$x + 1]['Sideboard'] == 'false')) { //This card name has multiple rows
                for ($y = $x + 1; $y < count($xml->Cards); $y++) {
                    if ($nameToAdd == strval($xml->Cards[$y]['Name'])) {
                        $quantityToAdd += intval($xml->Cards[$y]['Quantity']);
                    } else {
                        break;
                    }
                }

                $x = $y - 1;
            }
        }
        $maindeck[] = "$quantityToAdd $nameToAdd";
    } else { //Sideboard
        if ($x < $noOfRows - 1) { //Not the last line
            if (($nameToAdd == strval($xml->Cards[$x + 1]['Name'])) && ($xml->Cards[$x + 1]['Sideboard'] == 'true')) { //This card name has multiple rows
                for ($y = $x + 1; $y < count($xml->Cards); $y++) {
                    if ($nameToAdd == strval($xml->Cards[$y]['Name'])) {
                        $quantityToAdd += intval($xml->Cards[$y]['Quantity']);
                    } else {
                        break;
                    }
                }

                $x = $y - 1;
            }
        }
        $sideboard[] = "$quantityToAdd $nameToAdd";
    }
}
echo json_encode(['main'=>$maindeck, 'side'=>$sideboard]);
