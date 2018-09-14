<?php
ini_set("max_execution_time","0");
require_once __DIR__.'/../lib.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$series = new Series('Test');
$recentEvents = $series->getRecentEvents(1);
$number = 0;
if (count($recentEvents) == 0) {
    $number = 1;
} else {
    $event = $recentEvents[0];
    do {
        $number = $event->number + 1;
        $event = $event->findNext();
    } while ($event != null);
}

//Cretae Event 1
echo '<h3>First we are replicating Round 4 of this event https://gatherling.com/event.php?name=Penny%20Dreadful%20Saturdays%209.08&view=match</h3> <br />';
$players = ['thejacob', 'bakert99', 'narumin', 'Patalam', '13enito', '3cbb', 'ambienceinvoker', 'drikorf', 'jace365', 'maeyama', 'mrrphy', 'MyGaZz', 'nerdyjoe', 'Pluto_Nash', 'Pseudodude', 'ttacocatt'];
$event = testEventCreation();
echo 'Event '.$event->name.'<br />';
foreach ($players as $player) {
    $event->addPlayer($player);
}
foreach ($players as $player) {
    $deck = insertDeck($player, $event->name, '60 Plains', '');
}
echo count($players).' players registered and deck valid '.(count($event->getRegisteredEntries()) === count($players)?"&#9745;":"&#9746;").'<br />';

//Pairing for Event 1 Round 1
echo "Inputing Test 1 round 1<br />";
$event->addMatch(new Standings($event->name, 'mrrphy'), new Standings($event->name, 'drikorf'), 1, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'ttacocatt'), new Standings($event->name, 'jace365'), 1, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'narumin'), new Standings($event->name, 'nerdyjoe'), 1, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'maeyama'), new Standings($event->name, 'bakert99'), 1, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'ambienceinvoker'), new Standings($event->name, 'Pluto_Nash'), 1, 'A', 2, 1);
$event->addMatch(new Standings($event->name, '13enito'), new Standings($event->name, 'thejacob'), 1, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'MyGaZz'), new Standings($event->name, 'Patalam'), 1, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'Pseudodude'), new Standings($event->name, 'Pseudodude'), 1, 'BYE');

//Pairing for Event 1 Round 2
echo "Inputing Test 1 round 2<br />";
$event->addMatch(new Standings($event->name, 'Pseudodude'), new Standings($event->name, 'bakert99'), 2, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'Patalam'), new Standings($event->name, 'drikorf'), 2, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'ambienceinvoker'), new Standings($event->name, 'narumin'), 2, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'ttacocatt'), new Standings($event->name, 'thejacob'), 2, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'nerdyjoe'), new Standings($event->name, 'maeyama'), 2, 'A', 2, 0);
$event->addMatch(new Standings($event->name, '13enito'), new Standings($event->name, '3cbb'), 2, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'mrrphy'), new Standings($event->name, 'jace365'), 2, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'MyGaZz'), new Standings($event->name, 'MyGaZz'), 2, 'BYE');

//Pairing for Event 1 Round 3
echo "Inputing Test 1 round 3<br />";
$event->addMatch(new Standings($event->name, 'thejacob'), new Standings($event->name, 'narumin'), 3, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'bakert99'), new Standings($event->name, 'Patalam'), 3, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'MyGaZz'), new Standings($event->name, 'nerdyjoe'), 3, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'Pseudodude'), new Standings($event->name, '3cbb'), 3, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'jace365'), new Standings($event->name, 'ambienceinvoker'), 3, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'ttacocatt'), new Standings($event->name, 'drikorf'), 3, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'mrrphy'), new Standings($event->name, '13enito'), 3, 'A', 2, 0);

$event->active = 1;
$event->current_round = 4;
$event->mainstruct = 'Swiss (Blossom)';
$event->save();

//These steps are necessary because somehow the system drops everyone and undrop all of players after I set the event as active
$event->repairRound();
$event->dropPlayer('Pluto_Nash', 1);
$event->dropPlayer('maeyama', 2);
$event->dropPlayer('Pseudodude', 3);
$event->dropPlayer('13enito', 3);
$event->repairRound();

echo "Now we are gonna do 200 times re-pairing of round 4 each using Blossom and Brute method. '.' means no error, 'x' means double byes occured, 'z' means a player received more than a bye <br />";

echo runTheTest($event);

//Cretae Event 2
echo '<br /><br /><h3>For our second event we are replicating Round 3 of this event http://www.gatherling.com/eventreport.php?event=Classic%20Heirloom%2012.66</h3> <br />';
$players = ['TheUsualSuspect', 'Teylows', 'rremedio1', 'TheWhetherMan1', 'Maluc', 'brian1234', 'Farfishere'];
$event = testEventCreation();
foreach ($players as $player) {
    $event->addPlayer($player);
}
foreach ($players as $player) {
    $deck = insertDeck($player, $event->name, '60 Plains', '');
}
echo count($players).' players registered and deck valid '.(count($event->getRegisteredEntries()) === count($players)?"&#9745;":"&#9746;").'<br />';

//Pairing for Event 2 Round 1
echo "Inputing Test 2 round 1<br />";
$event->addMatch(new Standings($event->name, 'Teylows'), new Standings($event->name, 'Maluc'), 1, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'rremedio1'), new Standings($event->name, 'brian1234'), 1, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'TheWhetherMan1'), new Standings($event->name, 'Farfishere'), 1, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'TheUsualSuspect'), new Standings($event->name, 'TheUsualSuspect'), 1, 'BYE');

//Pairing for Event 2 Round 2
echo "Inputing Test 2 round 2<br />";
$event->addMatch(new Standings($event->name, 'TheUsualSuspect'), new Standings($event->name, 'TheWhetherMan1'), 2, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'rremedio1'), new Standings($event->name, 'Maluc'), 2, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'Farfishere'), new Standings($event->name, 'Farfishere'), 2, 'BYE');

$event->active = 1;
$event->current_round = 3;
$event->mainstruct = 'Swiss (Blossom)';
$event->save();

//These steps are necessary because somehow the system drops everyone and undrop all of players after I set the event as active
$event->repairRound();
$event->dropPlayer('Teylows', 1);
$event->dropPlayer('brian1234', 1);
$event->repairRound();

echo runTheTest($event);


//Cretae Event 3
echo '<br /><br /><h3>For our third event we are replicating Round 3 of this event http://www.gatherling.com/eventreport.php?event=Classic%20Heirloom%2012.74</h3> <br />';
$players = ['Tarrons', 'Yokai_', 'Rakura', 'Pink_Person', 'TheWhetherMan1', 'wiltay0494', 'BoozeMongoose', 'rremedio1'];
$event = testEventCreation();
foreach ($players as $player) {
    $event->addPlayer($player);
}
foreach ($players as $player) {
    $deck = insertDeck($player, $event->name, '60 Plains', '');
}
echo count($players).' players registered and deck valid '.(count($event->getRegisteredEntries()) === count($players)?"&#9745;":"&#9746;").'<br />';

//Pairing for Event 3 Round 1
echo "Inputing Test 3 round 1<br />";
$event->addMatch(new Standings($event->name, 'Tarrons'), new Standings($event->name, 'Yokai_'), 1, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'Rakura'), new Standings($event->name, 'Pink_Person'), 1, 'B', 0, 2);
$event->addMatch(new Standings($event->name, 'TheWhetherMan1'), new Standings($event->name, 'wiltay0494'), 1, 'A', 2, 1);
$event->addMatch(new Standings($event->name, 'rremedio1'), new Standings($event->name, 'BoozeMongoose'), 1, 'B', 1, 2);

//Pairing for Event 3 Round 2
echo "Inputing Test 3 round 2<br />";
$event->addMatch(new Standings($event->name, 'Tarrons'), new Standings($event->name, 'TheWhetherMan1'), 2, 'A', 2, 0);
$event->addMatch(new Standings($event->name, 'Pink_Person'), new Standings($event->name, 'BoozeMongoose'), 2, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'rremedio1'), new Standings($event->name, 'wiltay0494'), 2, 'A', 2, 0);

$event->active = 1;
$event->current_round = 3;
$event->mainstruct = 'Swiss (Blossom)';
$event->save();

//These steps are necessary because somehow the system drops everyone and undrop all of players after I set the event as active
$event->repairRound();
$event->dropPlayer('Yokai_', 1);
$event->dropPlayer('Rakura', 1);
$event->repairRound();

echo runTheTest($event);


//Cretae Event 4
echo '<br /><br /><h3>For our fourth event we are replicating Round 3 of this event http://www.gatherling.com/eventreport.php?event=Classic%20Heirloom%2012.49</h3> <br />';
$players = ['br_laern', 'Yokai_', 'Jota_F', 'Farfishere', 'TheWhetherMan1'];
$event = testEventCreation();
foreach ($players as $player) {
    $event->addPlayer($player);
}
foreach ($players as $player) {
    $deck = insertDeck($player, $event->name, '60 Plains', '');
}
echo count($players).' players registered and deck valid '.(count($event->getRegisteredEntries()) === count($players)?"&#9745;":"&#9746;").'<br />';

//Pairing for Event 4 Round 1
echo "Inputing Test 4 round 1<br />";
$event->addMatch(new Standings($event->name, 'br_laern'), new Standings($event->name, 'br_laern'), 1, 'BYE', 2, 0);
$event->addMatch(new Standings($event->name, 'Farfishere'), new Standings($event->name, 'TheWhetherMan1'), 1, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'Yokai_'), new Standings($event->name, 'Jota_F'), 1, 'B', 0, 2);

//Pairing for Event 4 Round 2
echo "Inputing Test 4 round 2<br />";
$event->addMatch(new Standings($event->name, 'br_laern'), new Standings($event->name, 'Jota_F'), 2, 'B', 1, 2);
$event->addMatch(new Standings($event->name, 'Farfishere'), new Standings($event->name, 'Farfishere'), 2, 'BYE', 2, 0);
$event->addMatch(new Standings($event->name, 'Yokai_'), new Standings($event->name, 'TheWhetherMan1'), 2, 'A', 2, 1);

$event->active = 1;
$event->current_round = 3;
$event->mainstruct = 'Swiss (Blossom)';
$event->save();

//These steps are necessary because somehow the system drops everyone and undrop all of players after I set the event as active
$event->repairRound();

echo runTheTest($event);


function runTheTest(&$event)
{
    $blossomDoubleBye = 0;
    $blossomPlayerGetsDoubleBye = 0;
    $bruteDoubleBye = 0;
    $brutePlayerGetsDoubleBye = 0;

    echo 'Blossom Pairing starts ';
    for ($i = 0; $i < 200; $i++) {
        $event->repairRound();
        $matches = $event->getRoundMatches($event->current_round);
        $noOfByes = numberForByes($matches);
        if ($noOfByes > 0) {
            if ($noOfByes > 1) {
                echo 'x';
                $blossomDoubleBye++;
            }
            foreach ($matches as $match) {
                if ($match->result == 'BYE') {
                    if (numberOfByesReceived($event->name, $match->playera) > 0) {
                        echo 'z';
                        $blossomPlayerGetsDoubleBye++;
                    } elseif ($noOfByes === 1) {
                        echo '.';
                    }
                }
            }
        } else {
            echo '.';
        }
    }

    $event->mainstruct = 'Swiss';
    $event->save();

    echo '<br />Brute Pairing starts ';
    for ($i = 0; $i < 200; $i++) {
        $event->repairRound();
        $matches = $event->getRoundMatches($event->current_round);
        $noOfByes = numberForByes($matches);
        if ($noOfByes > 0) {
           if ($noOfByes > 1) {
                echo 'x';
                $bruteDoubleBye++;
            }
            foreach ($matches as $match) {
                if ($match->result == 'BYE') {
                    if (numberOfByesReceived($event->name, $match->playera) > 0) {
                        echo 'z';
                        $brutePlayerGetsDoubleBye++;
                    } elseif ($noOfByes === 1) {
                        echo '.';
                    }
                }
            }
        } else {
            echo '.';
        }
    }

    return "<br />Out of 200 tries, there are $blossomDoubleBye cases of double byes and $blossomPlayerGetsDoubleBye cases of a player receiving more than 1 bye using Blossom Pairing. Out of 200 tries, there are $bruteDoubleBye cases of double byes and $brutePlayerGetsDoubleBye cases of a player receiving more than 1 bye using Brute Pairing.";
}

function testEventCreation()
{
    global $number;
    // $number = 2;

    $name = sprintf('%s %d.%02d', $series->name, 1, $number);

    if (!Event::exists($name)) {
        echo "Creating event $name<br />";
        $event = new Event('');
        $event->start = date('Y-m-d H:00:00');
        $event->name = $name;

        $event->format = 'Modern';
        $event->host = null;
        $event->cohost = null;
        $event->kvalue = 16;
        $event->series = $series->name;
        $event->season = 1;
        $event->number = $number;
        $event->threadurl = '';
        $event->metaurl = '';
        $event->reporturl = '';

        $event->prereg_allowed = 1;
        $event->pkonly = 0;
        $event->player_reportable = 1;

        $event->mainrounds = 4;
        $event->mainstruct = 'Swiss (Blossom)';
        $event->finalrounds = 2;
        $event->finalstruct = 'Single Elimination';
        $event->save();
    } else {
        echo "Resetting event $name<br />";
        $event = new Event($name);
        $event->resetEvent();
        $event->save();

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT deck FROM entries where event = ?");
        $stmt->bind_param('s', $event->name);

        $stmt->execute();
        $stmt->bind_result($deckid);
        $deckids = [];
        while ($stmt->fetch()) {
            $deckids[] = $deckid;
        }

        if (count($deckids) > 0) {
            $stmt = $db->prepare("DELETE FROM entries, deckerrors, deckcontents, decks USING entries LEFT JOIN deckerrors ON entries.deck=deckerrors.deck LEFT JOIN deckcontents ON entries.deck=deckcontents.deck LEFT JOIN decks ON entries.deck=decks.id WHERE entries.deck=?");
            
            foreach ($deckids as $idDelete) {
                $stmt->bind_param('s', $idDelete);
                $stmt->execute();
            }
        }

        $stmt->close();
    }
    
    $event = new Event($name);
    return $event;
}

function insertDeck($player, $eventName, $main, $side)
{
    $deck = new Deck(0);
    $deck->playername = $player;
    $deck->eventname = $eventName;
    $deck->maindeck_cards = parseCardsWithQuantity($main);
    $deck->sideboard_cards = parseCardsWithQuantity($side);
    $deck->save();

    return $deck;
}

function numberForByes($matches)
{
    $noOfByes = 0;
    for ($i = 0; $i < count($matches); $i++) {
        if ($matches[$i]->result == 'BYE') {
            $noOfByes++;
        }
    }

    return $noOfByes;
}

function numberOfByesReceived($eventname, $playername)
{
    $standing = new Standings($eventname, $playername);

    return $standing->byes;
}
