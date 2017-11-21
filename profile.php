<?php session_start();
include 'lib.php';

print_header("Player Profile");

$playername = "";
if(isset($_SESSION['username'])) {$playername = $_SESSION['username'];}
if(isset($_GET['player'])) {$playername = $_GET['player'];}
if(isset($_POST['player'])) {$playername = $_POST['player'];}

$profile_edit = 0;
if (isset($_GET['profile_edit'])) {$profile_edit = $_GET['profile_edit'];}
if (isset($_POST['profile_edit'])) {$profile_edit = $_POST['profile_edit'];}


if (isset($_GET['email'])) {$email = $_GET['email'];}
if (isset($_GET['email_public'])) {$email_public = $_GET['email_public'];}
if (isset($_GET['time_zone'])) {$timezone = $_GET['time_zone'];}

if (isset($_POST['email'])) {$email = $_POST['email'];}
if (isset($_POST['email_public'])) {$email_public = $_POST['email_public'];}
if (isset($_POST['time_zone'])) {$timezone = $_POST['time_zone'];}

	searchForm($playername);
?> 
<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Player Profile </div>

<?php

content($profile_edit, $_GET);

?>

</div> 
</div> 

<?php print_footer(); ?>

<?php
function content($profile_edit, $local) {
  global $playername; 
  if(chop($playername) != "") {
      $player = Player::findByName($playername); 
      if (!is_null($player)) { 
          if ($profile_edit == 1){
              editForm($player->timezone, $player->emailAddress, $player->emailPrivacy);
          } else if ($profile_edit == 2){
              $player->emailAddress = $_GET['email'];
              $player->emailPrivacy = $_GET['email_public'];
              $player->timezone = $_GET['time_zone'];
              $player->save();
              profileTable($player);  
          } else {
              profileTable($player);
	  }
      } else {
          echo "<center>\n";
          echo "$playername could not be found in the database. Please check";
          echo " your spelling and try again.\n";
          echo "</center>\n";
      }
  } else {
        echo "<center>\n";
        echo "Please <a href=\"login.php\">log in</a> to see";
        echo " your profile. You may also use the search above without";
        echo " logging in.\n";
        echo "</center>\n";
  }
  echo "<br /><br />\n";
}

function profileTable($player) {
  echo "<div class=\"grid_5 alpha\"> <div id=\"gatherling_lefthalf\">\n";
  infoTable($player);
  bestDecksTable($player);
  echo "</div></div>\n";
  echo "<div class=\"grid_5 omega\"> <div id=\"gatherling_righthalf\">\n";
  medalTable($player); 
  trophyTable($player);
  echo "</div> </div>\n";
  echo "<div class=\"clear\"></div>";
}

function infoTable($player) {
  $ndx = 0; $sum = 0; $favF = "";
  foreach ($player->getFormatsPlayedStats() as $tmprow) { 
		$sum += $tmprow['cnt'];
		if ($ndx == 0) {
			$max = $tmprow['cnt'];
			$favF = $tmprow['format'];
		}
		$ndx++;
	}
	$pcgF = 0;
	if($sum > 0) {$pcgF = round(($max/$sum)*100);}

  $ndx = 0; $sum = 0; $favS = "";
  foreach ($player->getSeriesPlayedStats() as $tmprow) { 
    $sum += $tmprow['cnt'];
    if ($ndx == 0) {
      $max = $tmprow['cnt'];
      $favS = $tmprow['series'];
    }
    $ndx++;
  }
	$pcgS = 0;
  if($sum > 0) {$pcgS = round(($max/$sum)*100);}

  $line1 = strtoupper($player->name);
  if ($player->verified) { 
    $line1 .= image_tag("verified.png", array("title" => "Verified their MTGO account"));
  }

  $matches = $player->getAllMatches(); 
  $nummatches = count($matches);

  $rating = $player->getRating(); 
  $hosted = $player->getHostedEventsCount();
  $lastevent = $player->getLastEventPlayed();
  $emailAddress = $player->emailAddress;
  $timezone = $player->timezone;
  if($player->emailIsPublic() == 0 ){
      $emailprivacy = "Admin Viewable Only";
  } else {
      $emailprivacy = "Publicly Viewable";
  }

  echo "<table style=\"border-width: 0px;\" width=250>\n";
  echo "<tr><td align=\"left\" colspan=2 style=\"font-size: 10pt;\">";
  echo "<b>$line1</td></tr>\n";
  echo "<tr><td>Rating:</td>\n";
  echo "<td align=\"right\">{$rating}</td></tr>\n";
  echo "<tr><td>Matches Played:</td>\n";
  echo "<td align=\"right\">$nummatches</td></tr>\n";
  echo "<tr><td>Record:</td>\n";
  echo "<td align=\"right\">{$player->getRecord()}<td>";
  echo "</tr>\n";	
  if($hosted > 0) {
      echo "<tr><td>Events Hosted:</td>\n";
      echo "<td align=\"right\">$hosted</td></tr>\n";
  }
  echo "<tr><td>Favorite Format:</td>\n";
  echo "<td align=\"right\">$favF ($pcgF%)</td></tr>\n";
  echo "<tr><td>Favorite Series:</td>\n";
  echo "<td align=\"right\">$favS ($pcgS%)</td></tr>\n";
  echo "<tr><td>Last Active:</td>\n";
  if (!is_null($lastevent)) { 
    $lastActive = date("F j, Y", strtotime($lastevent->start));
    echo "<td align=\"right\">$lastActive ({$lastevent->name})</td></tr>\n";
  } else { 
    echo "<td align=\"right\">Never</td></tr>\n";
  }

  echo "<tr><td>Email:</td>\n";
  if ($emailprivacy == "Admin Viewable Only") {
      echo "<td align=\"right\">$emailprivacy</td></tr>\n";
  } else {
      echo "<td align=\"right\">$emailAddress ($emailprivacy)</td></tr>\n";
  }
  
  echo "<tr><td>Time Zone:</td>\n";
  echo "<td align=\"right\">{$player->time_zone()}</td></tr>\n";
  echo "<tr><td align=\"center\" colspan='2'><a href=\"profile.php?profile_edit=1\" class=\"borderless\">Edit Player Information</a></td></tr>\n";
  //timeZoneDropdown($player->timezone);
  echo "";
  echo "</table>";
}

function medalTable($player) {

  $medalcount = $player->getMedalStats();

	echo "<table style=\"border-width: 0px\" width=260>\n";
	echo "<tr><td align=\"center\" colspan=4><b>MEDALS EARNED</td></tr>\n";
	if(count($medalcount) == 0) {
		echo "<tr><td align=\"center\" colspan=2>";
		echo "<i>{$player->name} has not earned any medals.</td></tr>\n";
	}
	else {
		medalCell("1st", $medalcount['1st']);
		medalCell("2nd", $medalcount['2nd']);
		medalCell("t4", $medalcount['t4']);
		medalCell("t8", $medalcount['t8']);
	}
	echo "</table>\n";
}

function trophyTable($player) {
    $events = $player->getEventsWithTrophies();
    echo "<table style=\"border-width: 0px;\" width=260>\n";
    echo "<tr><td align=\"center\"><b>TROPHIES EARNED</td></tr>\n";
    if(count($events) == 0) {
        echo "<tr><td align=\"center\"><i>{$player->name} has not earned any trophies.</td></tr>\n";
    } else {
        foreach ($events as $eventname) {
            echo "<tr><td align=\"center\">";
            echo "<a href=\"deck.php?mode=view&event=$eventname\" class=\"borderless\">";
            echo Event::trophy_image_tag($eventname);
            echo "</a></td></tr>";
        }
    }
    echo "</table>\n";
}

function bestDecksTable($player) {
	echo "<table style=\"border-width: 0px\" width=250>\n";
	echo "<tr><td align=\"left\" colspan=3><b>MEDAL WINNING DECKS</td></tr>\n";
  $printed = 0;
  foreach ($player->getBestDeckStats() as $row) { 
    if($row['score'] > 0) {
			$record = deckRecordString($row['name'], $player);
			if(chop($row['name']) == "") {$row['name'] = "* NO NAME *";}
			echo "<tr><td>";
			echo "<a href=\"deck.php?mode=view&id={$row['id']}\">";
			echo "{$row['name']}</a></td>\n";
			echo "<td align=\"center\" width=50>$record</td>";
			echo "<td align=\"right\">";
			for($i = 0; $i < $row['1st']; $i++) {inlineMedal('1st');}
			for($i = 0; $i < $row['2nd']; $i++) {inlineMedal('2nd');}
			for($i = 0; $i < $row['t4']; $i++) {inlineMedal('t4');}
			for($i = 0; $i < $row['t8']; $i++) {inlineMedal('t8');}
			echo "</td></tr>\n";
			$printed++;
		}
	}
	if($printed == 0) {
		echo "<tr><td colspan=3><i>{$player->name} has no medal winning decks.";
		echo "</td></tr>\n";
	}
	echo "</table>\n";
}

function medalCell($medal, $n) {
	if(is_null($n)) {$n = 0;}
	echo "<tr><td align=\"right\" width=130>";
        echo medalImgStr($medal);
	echo  "<td>$n</td></tr>\n";
}

function inlineMedal($medal) {
  echo medalImgStr($medal) . "&nbsp;";
}

function deckRecordString($deckname, $player) {
  $matches = $player->getMatchesByDeckName($deckname); 
  $wins = 0; 
  $losses = 0; 
  $draws = 0;

  foreach ($matches as $match) { 
    if($match->playerWon($player->name)) {
      $wins++; 
    } elseif ($match->playerLost($player->name)) { 
      $losses++; 
    } else if ($match->playerBye($player->name)) {
      $wins = $wins + 1;
    } else if ($match->playerMatchInProgress($player->name)) {
      ; // do nothing since match is in progress and there are no results
    } else {
      $draws++; 
    }    
  }
  $recordString = $wins . "-" . $losses;
  if ($draws > 0) { 
    $recordString .= "-" . $draws;
  } 
  return $recordString;
}

function searchForm($name) {
  echo "<div class=\"grid_10 prefix_1 suffix_1\"> <div class=\"box\" id=\"gatherling_simpleform\">\n"; 
	echo "<form action=\"profile.php\" mode=\"post\">\n";
  echo "<center>Player Lookup: ";
	echo "<input class=\"inputbox\" type=\"text\" name=\"player\" value=\"$name\" />";
	echo "<input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Lookup Profile\" />\n";
  echo "</form></center>\n";
  echo "<div class=\"clear\"></div>\n";
  echo "</div> </div>\n";
}

function editForm($timezone, $email, $public) {
	echo "<div class=\"grid_10 prefix_1 suffix_1\"> <div class=\"box\" id=\"gatherling_simpleform\">\n";
	echo "<form action=\"profile.php\" mode=\"POST\">\n";
	echo "<center>Time Zone: ";
	timeZoneDropdown($timezone);
	echo "<br><label for=\"player\">Email Address: </label><input class=\"inputbox\" type=\"text\" name=\"email\" value=\"$email\" />";
	echo "<br><input type=\"radio\" name=\"email_public\" value=\"1\"";
	if ($public == 1){echo " checked ";};
	echo">Make my email publicly viewable";
	echo "<br><input type=\"radio\" name=\"email_public\" value=\"0\"";
	if ($public == 0){echo " checked ";};
	echo">Only allow admininstrators and event hosts to view my email";
	echo "<br><input type=\"hidden\" name=\"profile_edit\" value=\"2\">";
	echo "<br><input class=\"inputbutton\" type=\"submit\" name=\"mode\" value=\"Sumbit Changes\" />\n";
	echo "</form></center>\n";
	echo "<div class=\"clear\"></div>\n";
	echo "</div> </div>\n";
}

function timeZoneDropdown($timezone) {
	echo "<select name=\"time_zone\">";
	echo "<option value=\"-12\"";
	if ($timezone == -12){echo " selected=\"selected\"";}
	echo ">[UTC - 12] Baker Island Time</option>";
	echo "<option value=\"-11\"";
	if ($timezone == -11){echo " selected=\"selected\"";}
	echo ">[UTC - 11] Niue Time, Samoa Standard Time</option>";
	echo "<option value=\"-10\"";
	if ($timezone == -10){echo " selected=\"selected\"";};
	echo ">[UTC - 10] Hawaii-Aleutian Standard Time, Cook Island Time</option>";
	echo "<option value=\"-9.5\"";
	if ($timezone == -9.5){echo " selected=\"selected\"";}
	echo ">[UTC - 9:30] Marquesas Islands Time</option>";
	echo "<option value=\"-9\"";
	if ($timezone == -9){echo " selected=\"selected\"";}
	echo ">[UTC - 9] Alaska Standard Time, Gambier Island Time</option>";
	echo "<option value=\"-8\"";
	if ($timezone == -8){echo " selected=\"selected\"";}
	echo ">[UTC - 8] Pacific Standard Time</option>";
	echo "<option value=\"-7\"";
	if ($timezone == -7){echo " selected=\"selected\"";}
	echo ">[UTC - 7] Mountain Standard Time</option>";
	echo "<option value=\"-6\"";
	if ($timezone == -6){echo " selected=\"selected\"";}
	echo ">[UTC - 6] Central Standard Time</option>";
	echo "<option value=\"-5\"";
	if ($timezone == -5){echo " selected=\"selected\"";}
	echo ">[UTC - 5] Eastern Standard Time</option>";
	echo "<option value=\"-4.5\"";
	if ($timezone == -4.5){echo " selected=\"selected\"";}
	echo ">[UTC - 4:30] Venezuelan Standard Time</option>";
	echo "<option value=\"-4\"";
	if ($timezone == -4){echo " selected=\"selected\"";}
	echo ">[UTC - 4] Atlantic Standard Time</option>";
	echo "<option value=\"-3.5\"";
	if ($timezone == -3.5){echo " selected=\"selected\"";}
	echo ">[UTC - 3:30] Newfoundland Standard Time</option>";
	echo "<option value=\"-3\"";
	if ($timezone == -3){echo " selected=\"selected\"";}
	echo ">[UTC - 3] Amazon Standard Time, Central Greenland Time</option>";
	echo "<option value=\"-2\"";
	if ($timezone == -2){echo " selected=\"selected\"";}
	echo ">[UTC - 2] Fernando de Noronha Time, South Georgia &amp; the South Sandwich Islands Time</option>";
	echo "<option value=\"-1\"";
	if ($timezone == -1){echo " selected=\"selected\"";}
	echo ">[UTC - 1] Azores Standard Time, Cape Verde Time, Eastern Greenland Time</option>";
	echo "<option value=\"0\"";
	if ($timezone == 0){echo " selected=\"selected\"";}
	echo ">[UTC] Western European Time, Greenwich Mean Time</option>";
	echo "<option value=\"1\"";
	if ($timezone == 1){echo " selected=\"selected\"";}
	echo ">[UTC + 1] Central European Time, West African Time</option>";
	echo "<option value=\"2\"";
	if ($timezone == 2){echo " selected=\"selected\"";}
	echo ">[UTC + 2] Eastern European Time, Central African Time</option>";
	echo "<option value=\"3\"";
	if ($timezone == 3){echo " selected=\"selected\"";}
	echo ">[UTC + 3] Moscow Standard Time, Eastern African Time</option>";
	echo "<option value=\"3.5\"";
	if ($timezone == 3.5){echo " selected=\"selected\"";}
	echo ">[UTC + 3:30] Iran Standard Time</option>";
	echo "<option value=\"4\"";
	if ($timezone == 4){echo " selected=\"selected\"";}
	echo ">[UTC + 4] Gulf Standard Time, Samara Standard Time</option>";
	echo "<option value=\"4.5\"";
	if ($timezone == 4.5){echo " selected=\"selected\"";}
	echo ">[UTC + 4:30] Afghanistan Time</option>";
	echo "<option value=\"5\"";
	if ($timezone == 5){echo " selected=\"selected\"";}
	echo ">[UTC + 5] Pakistan Standard Time, Yekaterinburg Standard Time</option>";
	echo "<option value=\"5.5\"";
	if ($timezone == 5.5){echo " selected=\"selected\"";}
	echo ">[UTC + 5:30] Indian Standard Time, Sri Lanka Time</option>";
	echo "<option value=\"5.75\"";
	if ($timezone == 5.75){echo " selected=\"selected\"";}
	echo ">[UTC + 5:45] Nepal Time</option>";
	echo "<option value=\"6\"";
	if ($timezone == 6){echo " selected=\"selected\"";}
	echo ">[UTC + 6] Bangladesh Time, Bhutan Time, Novosibirsk Standard Time</option>";
	echo "<option value=\"6.5\"";
	if ($timezone == 6.5){echo " selected=\"selected\"";}
	echo ">[UTC + 6:30] Cocos Islands Time, Myanmar Time</option>";
	echo "<option value=\"7\"";
	if ($timezone == 7){echo " selected=\"selected\"";}
	echo ">[UTC + 7] Indochina Time, Krasnoyarsk Standard Time</option>";
	echo "<option value=\"8\"";
	if ($timezone == 8){echo " selected=\"selected\"";}
	echo ">[UTC + 8] Chinese Standard Time, Australian Western Standard Time, Irkutsk Standard Time</option>";
	echo "<option value=\"8.75\"";
	if ($timezone == 8.75){echo " selected=\"selected\"";}
	echo ">[UTC + 8:45] Southeastern Western Australia Standard Time</option>";
	echo "<option value=\"9\"";
	if ($timezone == 9){echo " selected=\"selected\"";}
	echo ">[UTC + 9] Japan Standard Time, Korea Standard Time, Chita Standard Time</option>";
	echo "<option value=\"9.5\"";
	if ($timezone == 9.5){echo " selected=\"selected\"";}
	echo ">[UTC + 9:30] Australian Central Standard Time</option>";
	echo "<option value=\"10\"";
	if ($timezone == 10){echo " selected=\"selected\"";}
	echo ">[UTC + 10] Australian Eastern Standard Time, Vladivostok Standard Time</option>";
	echo "<option value=\"10.5\"";
	if ($timezone == 10.5){echo " selected=\"selected\"";}
	echo ">[UTC + 10:30] Lord Howe Standard Time</option>";
	echo "<option value=\"11\"";
	if ($timezone == 11){echo " selected=\"selected\"";}
	echo ">[UTC + 11] Solomon Island Time, Magadan Standard Time</option>";
	echo "<option value=\"11.5\"";
	if ($timezone == 11.5){echo " selected=\"selected\"";}
	echo ">[UTC + 11:30] Norfolk Island Time</option>";
	echo "<option value=\"12\"";
	if ($timezone == 12){echo " selected=\"selected\"";}
	echo ">[UTC + 12] New Zealand Time, Fiji Time, Kamchatka Standard Time</option>";
	echo "<option value=\"12.75\"";
	if ($timezone == 12.75){echo " selected=\"selected\"";}
	echo ">[UTC + 12:45] Chatham Islands Time</option>";
	echo "<option value=\"13\"";
	if ($timezone == 13){echo " selected=\"selected\"";}
	echo ">[UTC + 13] Tonga Time, Phoenix Islands Time</option>";
	echo "<option value=\"14\"";
	if ($timezone == 14){echo " selected=\"selected\"";}
	echo ">[UTC + 14] Line Island Time</option>";
	echo "</select>";
}
?>
