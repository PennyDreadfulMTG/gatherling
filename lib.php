<?php
require_once 'bootstrap.php';

$HC = "#DDDDDD";
$R1 = "#EEEEEE";
$R2 = "#FFFFFF";
$CC = $R1;
putenv("TZ=US/Eastern"); // force time functions to use US/Eastern time

function is_assoc($array) {
  return (bool)count(array_filter(array_keys($array), 'is_string'));
}

/** Gets the correct name, relative to the gatherling root dir, for a file in the theme.
 *  Allows for overrides, falls back to default/
 */
function theme_file($name) {
  global $CONFIG;
  $theme_dir = "styles/{$CONFIG['style']}/";
  $default_dir = "styles/default/";
  if (file_exists($theme_dir . $name)) {
    return $theme_dir . $name;
  }
  return $default_dir . $name;
}

function print_header($title, $js = null, $extra_head_content = "") {
  global $CONFIG;
  
  // if player ip address changes could be a hacker breaking in. 
  // Once you implement a remember me cookie, this will also 
  // destroy the remember me cookie if the IP's don't match
  // if (Player::isLoggedIn()) {
  //     $player = new Player(Player::loginName());
  //     if ($player->getIPAddresss() != Player::getClientIPAddress()) {
  //         Player::logOut();
  //         redirect("login.php?ipaddresschanged=true");
  //     }
  // }
  
  ini_set('session.gc_maxlifetime',10*60*60); // sets session timer to 10 hours, format is N hr * 60 minutes * 60 seconds
  ini_set('session.cookie_lifetime', 0); // sets the session cookie timer to only timeout when browser is closed
  echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
  echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
  echo "  <head>\n";
  echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
  echo "    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
  echo "    <meta name=\"google-site-verification\" content=\"VWE-XX0CgPTHYgUqJ6t13N75y-p_Q9dgtqXikM3EsBo\" />\n";
  echo "    <title>{$CONFIG['site_name']} | {$title}</title>\n";
  echo "    <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"". theme_file("css/stylesheet.css") . "\" />\n";
  echo "    <script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.4.1/jquery.min.js\"></script>\n";

  if ($js) {
    echo "<script type=\"text/javascript\">";
    echo $js;
    echo "</script>";
  }
  echo $extra_head_content;
  echo <<<EOT
  </head>
  <body>
    <div id="maincontainer" class="container_12">
        <div id="header_bar" class="box">
            <div id="header_gatherling">
EOT;
 include_once("analyticstracking.php"); // google analytics tracking
//  echo image_tag("header_gatherling.png");
echo <<<EOT
            </div>
            <div id="header_logo">
EOT;
  echo image_tag("header_logo.png");
  echo <<<EOT
            </div>
        </div>      
        <div id="mainmenu_submenu" class="grid_12 menubar">
        <ul>
          <li><span class=\"inputbutton\"><a href="./index.php">Home</a></span></li>
          <li><a href="https://discord.gg/t3E8KWM">Discord</a></li>
          <li><a href="./series.php">Events</a></li>
          <li><a href="./gatherling.php">Gatherling</a></li>
          <li><a href="./ratings.php">Ratings</a></li>
          <li><a href="https://pennydreadfulmagic.com/bugs/">Known Bugs</a></li>
        </ul>
      </div>
EOT;

  $player = Player::getSessionPlayer();

  $super = false;
  $host = false;
  $organizer = false;

  if ($player != NULL) {
    $host = $player->isHost();
    $super = $player->isSuper();
    $organizer = count($player->organizersSeries()) > 0;
  }

  $tabs = 5;
  if ($super || $organizer) {
    $tabs += 1;
  }
  if ($host) {
    $tabs += 1;
  }
  if ($super) {
    $tabs += 1;
  }

  echo <<<EOT
<div id="submenu" class="grid_12 tabs_$tabs menubar">
  <ul>
    <li><a href="profile.php">Profile</a></li>
    <li><a href="player.php">Player CP</a></li>
    <li><a href="eventreport.php">Metagame</a></li>
    <li><a href="decksearch.php">Deck Search</a></li>
EOT;
  if ($player == NULL) {
    echo "<li class=\"last\"><a href=\"login.php\">Login</a></li>\n";
  } else {
    echo "<li class=\"last\"><a href=\"logout.php\">Logout [{$player->name}]</a></li>\n";
  }

  if ($host || $super) {
    echo "<li><a href=\"event.php\">Host CP</a></li>\n";
  }

  if ($organizer || $super) {
    echo "<li><a href=\"seriescp.php\">Series CP</a></li>\n";
  }

  if ($organizer || $super) {
    echo "<li><a href=\"formatcp.php\">Format CP</a></li>\n";
  }

  if ($super) {
    echo "<li><a href=\"admincp.php\">Admin CP</a></li>\n";
  }

  echo "</ul> </div>\n";
}

function print_footer() {
  echo "<div class=\"prefix_1 suffix_1\">\n";
  echo "<div id=\"gatherling_footer\" class=\"box\">\n";
  version_tagline();
  echo "</div><!-- prefix_1 suffix_1 -->\n";
  echo "</div><!-- gatherling_footer -->\n";
  echo "<div class=\"clear\"></div>\n";
  echo "</div> <!-- container -->\n"; 
  echo "</body>\n";
  echo "</html>\n";
}

function headerColor() {
  global $HC, $CC, $R1, $R2;
  $CC = $R2;
  return $HC;
}

function rowColor() {
  global $CC, $R1, $R2;
  if(strcmp($CC, $R1) == 0) {$CC = $R2;}
  else {$CC = $R1;}
  return $CC;
}

function linkToLogin() {
  echo "<center>\n";
  echo "Please <a href=\"login.php\">Click Here</a> to log in.\n";
  echo "</center>\n";
}

function printCardLink($card) {
  $gathererName = preg_replace('/ /',']+[',$card);
  $gathererName = str_replace("/", "]+[", $gathererName);
  echo "<span class=\"cardHoverImageWrapper\">";
  echo "<a href=\"http://gatherer.wizards.com/Pages/Search/Default.aspx?name=+[{$gathererName}]\" ";
  echo "class=\"linkedCardName\" target=\"_blank\">{$card}<span class=\"linkCardHoverImage\"><p class=\"crop\" style=\"background-image: url(http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"><img src=\"http://gatherer.wizards.com/Handlers/Image.ashx?name={$card}&type=card\"></p></span></a></span>";
}

function image_tag($filename, $extra_attr = NULL) {
  $tag = "<img ";
  if (is_array($extra_attr)) {
    foreach ($extra_attr as $key => $value) {
      $tag .= "{$key}=\"{$value}\" ";
    }
  }
  $tag .= "src=\"" . theme_file("images/{$filename}") . "\" />";
  return $tag;
}

function noHost() {
  echo "<center>\n";
  echo "Only hosts and admins may access that page.</center>\n";
}

function medalImgStr($medal) {
  return image_tag("$medal.png", array("style" => "border-width: 0px"));
}

function seasonDropMenu($season, $useall = 0) {
    $db = Database::getConnection();
    $query = "SELECT MAX(season) AS m FROM events";
    $result = $db->query($query) or die($db->error);
    $maxarr = $result->fetch_assoc();
    $max = $maxarr['m'];
    $title = ($useall == 0) ? "- Season - " : "All";
    $result->close();
    numDropMenu("season", $title, max(10, $max + 1), $season);
}

function formatDropMenu($format, $useAll = 0, $form_name = 'format') {
    $db = Database::getConnection();
    $query = "SELECT name FROM formats ORDER BY priority desc, name";
    $result = $db->query($query) or die($db->error);
    echo "<select class=\"inputbox\" name=\"{$form_name}\">";
    $title = ($useAll == 0) ? "- Format -" : "All";
    echo "<option value=\"\">$title</option>";
    while($thisFormat = $result->fetch_assoc()) {
        $name = $thisFormat['name'];
        $selStr = (strcmp($name, $format) == 0) ? "selected" : "";
        echo "<option value=\"$name\" $selStr>$name</option>";
    }
    echo "</select>";
    $result->close();
}

function emailStatusDropDown($currentStatus = 1) {
    echo "<select class=\"inputbox\" name=\"emailstatus\">";
    if ($currentStatus == 0) {        
        echo "<option value=\"0\" selected>Private</option>";
    } else {        
        echo "<option value=\"0\">Private</option>";
    }
    if ($currentStatus == 1) {
        echo "<option value=\"1\" selected>Public</option>";        
    } else {
        echo "<option value=\"1\">Public</option>";
    }
    echo "</select>";    
}

function dropMenu($name, $options, $selected = NULL) {
  echo "<select class=\"inputbox\" name=\"{$name}\">";
  foreach ($options as $option) {
    $setxt = "";
    if (!is_null($selected) && $selected == $option) {
      $setxt = " selected";
    }
    echo "<option value=\"{$option}\"{$setxt}>{$option}</option>";
  }
  echo "</select>";
}


function numDropMenu($field, $title, $max, $def, $min = 0, $special="") {
    if(strcmp($def, "") == 0) {$def = -1;}
    echo "<select class=\"inputbox\" name=\"$field\">";
    echo "<option value=\"\">$title</option>";
    if(strcmp($special, "") != 0) {
        $sel = ($def == 128) ? "selected" : "";
        echo "<option value=\"128\" $sel>$special</option>";
    }
    for($n = $min; $n <= $max; $n++) {
        $selStr = ($n == $def) ? "selected" : "";
        echo "<option value=\"$n\" $selStr>$n</option>";
    }
    echo "</select>";
}

function timeDropMenu($hour, $minutes = 0) {
  if(strcmp($hour, "") == 0) {$hour = -1;}
  echo "<select class=\"inputbox\" name=\"hour\">";
  echo "<option value=\"\">- Hour -</option>";
  for($h = 0; $h < 24; $h++) {
    for ($m = 0; $m < 60; $m += 30) {
      $hstring = $h;
      if ($m == 0) {
        $mstring = ":00";
      } else {
        $mstring = ":$m";
      }
      if ($h == 0) {
        $hstring = "12";
      }
      $apstring = " AM";
      if ($h >= 12) {
        $hstring = $h != 12 ? $h - 12 : $h;
        $apstring = " PM";
      }
      if($h == 0 && $m == 0) {
        $hstring = "Midnight";
        $mstring = "";
        $apstring = "";
      } elseif ($h == 12 && $m == 0) {
        $hstring = "Noon";
        $mstring = "";
        $apstring = "";
      }
      $selStr = ($hour == $h) && ($minutes == $m) ? "selected" : "";
      echo "<option value=\"$h:$m\" $selStr>$hstring$mstring$apstring</option>";
    }
  }
  echo "</select>";
}

function minutes($mins) {
  return $mins * 60;
}

function json_headers() {
  header('Content-type: application/json');
  header('Cache-Control: no-cache');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
}

function distance_of_time_in_words($from_time,$to_time = 0, $include_seconds = false) {
    $inputSeconds = abs(($from_time - $to_time));

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    $time_to_start = "";
    if ($days > 0){$time_to_start = "$days Days, ";}
    if ($hours > 0){$time_to_start .= "$hours Hours, ";}
    if ($minutes > 0){$time_to_start .= "$minutes Minutes";}

    return $time_to_start;

    /*$ds = $distance_in_seconds = abs(($from_time - $to_time));
    $dm = $distance_in_minutes = abs(($from_time - $to_time))/60;
    $ds = $distance_in_seconds = abs(($from_time - $to_time));
  
  switch ($distance_in_minutes) {
    case $dm > 0 && $dm < 1:
    if($include_seconds == false) {
      if ($dm == 0) {
        return 'less than a minute';
      } else {
        return '1 minute';
      }
    } else {
      switch ($distance_of_seconds) {
        case $ds > 0 && $ds < 4:
          return 'less than 5 seconds';
          break;
        case $ds > 5 && $ds < 9:
          return 'less than 10 seconds';
          break;
        case $ds > 10 && $ds < 19:
          return 'less than 20 seconds';
          break;
        case $ds > 20 && $ds < 39:
          return 'half a minute';
          break;
        case $ds > 40 && $ds < 59:
          return 'less than a minute';
          break;
        default:
          return '1 minute';
        break;
      }
    }
    break;
    case $dm > 2 && $dm < 44:
      return round($dm) . ' minutes';
      break;
    case $dm > 45 && $dm < 89:
      return 'about 1 hour';
      break;
    case $dm > 90 && $dm < 1439:
      return 'about ' . round($dm / 60.0) . ' hours';
      break;
    case $dm > 1440 && $dm < 2879:
      return '1 day';
      break;
    case $dm > 2880 && $dm < 43199:
      return round($dm / 1440) . ' days';
      break;
    case $dm > 43200 && $dm < 86399:
      return 'about 1 month';
      break;
    case $dm > 86400 && $dm < 525599:
      return round($dm / 43200) . ' months';
      break;
    case $dm > 525600 && $dm < 1051199:
      return 'about 1 year';
      break;
    default:
      return 'over ' . round($dm / 525600) . ' years';
    break;
  }*/
  }

function not_allowed($reason) {
  echo "<span class=\"notallowed inputbutton\" title=\"{$reason}\">&#x26A0;</span>";
}

function displayPlayerEmailPopUp($player, $email) {
  echo "<a class=\"emailPop\" style=\color: green\" title=\"{$email}\">{$player}</a>";
}

function tribeBanDropMenu ($format) {
    $allTribes = Format::getTribesList();
    $bannedTribes = $format->getTribesBanned();
    $tribes = array_diff($allTribes, $bannedTribes); // remove tribes banned from drop menu
    
    echo "<select class=\"inputbox\" name=\"tribeban\">";
    echo "<option value=\"Unclassified\">- Tribe to Ban - </option>";
    foreach ($tribes as $tribe) {
        echo "<option value=\"$tribe\">$tribe</option>";
    }
    echo "</select>";
}

function subTypeBanDropMenu ($format) {
    $allSubTypes = Format::getTribesList();
    $bannedSubTypes = $format->getSubTypesBanned();
    $subTypes = array_diff($allSubTypes, $bannedSubTypes); // remove sub types banned from drop menu
    
    echo "<select class=\"inputbox\" name=\"subtypeban\">";
    echo "<option value=\"Unclassified\">- Subtype to Ban - </option>";
    foreach ($subTypes as $subType) {
        echo "<option value=\"$subType\">$subType</option>";
    }
    echo "</select>";
}

function formatsDropMenu($formatType="", $seriesName = "System") {
  $formatNames = array();
  
  if ($formatType == "System")  {$formatNames = Format::getSystemFormats();}
  if ($formatType == "Public")  {$formatNames = Format::getPublicFormats();}
  if ($formatType == "Private") {$formatNames = Format::getPrivateFormats($seriesName);}
  if ($formatType == "All")     {$formatNames = Format::getAllFormats();}
  
  echo "<select class=\"inputbox\" name=\"format\" STYLE=\"width: 250px\">\n";
  echo "<option value=\"Unclassified\">- {$formatType} Format Name -</option>\n";
  foreach ($formatNames as $formatName) {
      echo "<option value=\"$formatName\">$formatName</option>\n";
  }
}

function printOrganizerSelect($player_series, $selected) {
  echo "<center>";
  echo "<form action=\"seriescp.php\" method=\"get\">";
  echo "<select class=\"inputbox\" name=\"series\">";
  foreach ($player_series as $series) {
    echo "<option value=\"{$series}\"";
    if ($series == $selected) {
      echo " selected";
    }
    echo ">{$series}</option>";
  }
  echo "</select>";
  echo "<input class=\"inputbutton\" type=\"submit\" value=\"Select Series\">";  
  echo "</form>";
}

function print_warning_if($conditional) {
  if ($conditional) {
    echo "<span style=\"color: red;\">⚠</span>";
  }
}

function version_tagline() {
  print "Gatherling version 4.8.0 (\"Zagreus at the end of days / Zagreus lies all other ways / Zagreus comes when time's a maze / And all of history is weeping.\")";
  # print "Gatherling version 4.7.0 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
  # print "Gatherling version 4.5.2 (\"People assume that time is a strict progression of cause to effect, but actually — from a non-linear, non-subjective viewpoint — it's more like a big ball of wibbly-wobbly... timey-wimey... stuff.\")";
  # print "Gatherling version 4.0.0 (\"Call me old fashioned, but, if you really wanted peace, couldn't you just STOP FIGHTING?\")";
  # print "Gatherling version 3.3.0 (\"Do not offend the Chair Leg of Truth. It is wise and terrible.\")";
  # print "Gatherling version 2.1.27PK (\"Please give us a simple answer, so that we don't have to think, because if we think, we might find answers that don't fit the way we want the world to be.\")";
  # print "Gatherling version 2.1.26PK (\"The program wasn't designed to alter the past. It was designed to affect the future.\")";
  # print "Gatherling version 2.0.6 (\"We stole the Statue of Liberty! ...  The small one, from Las Vegas.\")";
  # print "Gatherling version 2.0.5 (\"No, that's perfectly normal paranoia. Everyone in the universe gets that.\")";
  # print "Gatherling version 2.0.4 (\"This is no time to talk about time. We don't have the time!\")";
  # print "Gatherling version 2.0.3 (\"Are you hungry? I haven't eaten since later this afternoon.\")";
  # print "Gatherling version 2.0.2 (\"Woah lady, I only speak two languages, English and bad English.\")";
  # print "Gatherling version 2.0.1 (\"Use this to defend yourself. It's a powerful weapon.\")";
  # print "Gatherling version 2.0.0 (\"I'm here to keep you safe, Sam.  I want to help you.\")";
  # print "Gatherling version 1.9.9 (\"You'd think they'd never seen a girl and a cat on a broom before\")";
  # print "Gatherling version 1.9.8 (\"I'm tellin' you, man, every third blink is slower.\")";
  # print "Gatherling version 1.9.7 (\"Try blue, it's the new red!\")";
  # print "Gatherling version 1.9.6 (\"Just relax and let your mind go blank. That shouldn't be too hard for you.\")";
  # print "Gatherling version 1.9.5 (\"The grade that you receive will be your last, WE SWEAR!\")";
  # print "Gatherling version 1.9.4 (\"We're gonna need some more FBI guys, I guess.\")";
  # print "Gatherling version 1.9.3 (\"This is the Ocean, silly, we're not the only two in here.\")";
  # print "Gatherling version 1.9.2 (\"So now you're the boss. You're the King of Bob.\")";
  # print "Gatherling version 1.9.1 (\"It's the United States of Don't Touch That Thing Right in Front of You.\")";
  # print "Gatherling version 1.9 (\"It's funny 'cause the squirrel got dead\")";
}

function redirect($page) {
  global $CONFIG;
  header("Location: {$CONFIG['base_url']}{$page}");
  exit(0);
}

function parseCards($cards) {
  if (!is_array($cards))
  {
    $cardarr = array();
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

function normaliseCardName($card, $tolower = false){
  $card = preg_replace("/Æ/", "AE", $card);
  $card = preg_replace("/\306/", "AE", $card);
  $card = preg_replace("/ö/", "o", $card);
  $card = preg_replace("/ \/\/ /", "/", $card);
  if ($tolower)
    $card = strtolower($card);
  return $card = trim($card);
}

function parseCardsWithQuantity($cards) {
  $cards = parseCards($cards);
  $badcards = array();
  $cardarr = array();
  foreach ($cards as $line) {
    $chopped = chop($line);
    if (preg_match("/[ \t]*([0-9]+)x?[ \t]+(.*)/i", $chopped, $m)) {
      $qty = $m[1];
      $card = chop($m[2]);
      if(isset($cardarr[$card])) {
        $cardarr[$card] += $qty;
      } else {
        $cardarr[$card] = $qty;
      }
    }
  }

  return $cardarr;
}

function print_tooltip($text, $tooltip) {
  echo "<span class=\"tooltip\" title=\"$tooltip\">$text</span>";
}
