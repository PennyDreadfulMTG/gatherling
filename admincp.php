<?php
session_start();
include 'lib.php';
include 'lib_form_helper.php';

$hasError = false;
$errormsg = "";

if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
  redirect("index.php");
}

print_header("Admin Control Panel");
?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Admin Control Panel </div>
<center>
<?php do_page(); ?>
</div>
<?php print_footer(); ?>

<?php 

function do_page() {
  $player = Player::getSessionPlayer();
  if (!$player->isSuper()) {
    printNoAdmin(); 
    return;
  }
  
  printAdminCPIntroduction();
  handleActions();
  printError();
  adminCPMenu();
  
  $view = "change_password";

  if (isset($_GET['view']) && ($_GET['view'] != "")) {$view = $_GET['view'];}
  if (isset($_POST['view'])) {$view = $_POST['view'];}
  
  if ($view == "no_view") {
      ; // Show Nothing
  } else if ($view == "change_password") {
      printChangePasswordForm();
  } elseif ($view == "create_series") {
      printCreateNewSeriesForm(); 
  } elseif ($view == "format_editor") {
      redirect('formatcp.php');
  } elseif(($view == "add_cardset")) {
      printAddCardSet();
  } elseif ($view == "calc_ratings") {
      printCalcRatingsForm();
  }
  
  echo "</center><div class=\"clear\"></div></div>";
}

function printAdminCPIntroduction() {
    echo "Welcome to the Admin CP! <br />";
}

function printNoAdmin() { 
  $hasError = true;
  $errormsg = "<center>You're not an Admin here on Gatherling.com! Access Restricted.<br />";
  echo "<a href=\"player.php\">Back to the Player Control Panel</a></center>";
} 

function printError() {
  global $hasError;
  global $errormsg;
  if ($hasError) {
    echo "<div class=\"error\">{$errormsg}</div>";
  }
}

function adminCPMenu() {
  echo "<table><tr><td colspan=\"2\" align=\"center\">";
  echo "<a href=\"admincp.php?view=change_password\">Change Player Password</a>";
  echo " | <a href=\"admincp.php?view=create_series\">Create New Series</a>";
  // echo " | <a href=\"admincp.php?view=format_editor\">Format Editor</a>";
  echo " | <a href=\"admincp.php?view=calc_ratings\">Ratings</a>";
  echo " | <a href=\"admincp.php?view=add_cardset\">Add New Cardset</a>";
  echo "</td></tr></table>";
}

function printCreateNewSeriesForm() {
  echo "<h4>Create New Series</h4>";
  echo "<form action=\"admincp.php\" method=\"post\">";
  echo "<input type=\"hidden\" name=\"view\" value=\"create_series\" />";
  echo "<table class=\"form\" style=\"border-width: 0px\" align=\"center\">";
  echo "<tr><td colspan=\"2\">New Series Name: <input class=\"inputbox\" type=\"text\" name=\"seriesname\" STYLE=\"width: 175px\"/></td></tr>";
 
  # Active
  echo "<tr><th> Series is Active </th> <td> ";
  echo "<select class=\"inputbox\" name=\"isactive\"> <option value=\"1\">Yes</option> <option value=\"0\" selected>No</option></select>"; 
  echo "</td></tr>";
  
  # Start day
  echo "<tr><th>Normal Start Day</th><td> ";
  echo "<select class=\"inputbox\" name=\"start_day\">";
  $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
  foreach ($days as $dayofweek) { 
      echo "<option>{$dayofweek}</option>";
    } 
  echo "</select>";
  echo "</td></tr>";
  
  # Start time
  echo "<tr><th>Normal start time</th><td> "; 
  $time_parts = explode(":", "12:00:00");
  timeDropMenu($time_parts[0], $time_parts[1]);
  echo "</td> </tr>";  
  
  # Pre-registration on by default?
  echo "<tr><th>Pre-Registration Default</th>";
  echo "<td><input type=\"checkbox\" value=\"1\" name=\"preregdefault\" /></td></tr>";
  echo "<tr><th>Pauper Krew Members Only Default</th>";
  echo "<td><input type=\"checkbox\" value=\"1\" name=\"pkonlydefault\" /></td></tr>";
  
  # Submit button 
  echo "<tr><td colspan=\"2\" class=\"buttons\">";
  echo "<input class=\"inputbutton\" type=\"submit\" name=\"action\" value=\"Create Series\" /></td></tr>";
  echo "</table></form>";
}

function printCalcRatingsForm() {
  $ratings = new Ratings();
  echo "<h4>Calculate Ratings</h4>";
  echo "<form action=\"admincp.php\" method=\"post\">";
  echo "<input type=\"hidden\" name=\"view\" value=\"calc_ratings\" />";    
  echo "<table class=\"form\" style=\"border-width: 0px\" align=\"center\">";
  echo "<tr><td class=\"buttons\">";
  echo "<input class=\"inputbutton\" type=\"submit\" name=\"action\" value=\"Re-Calculate All Ratings\" /></td></tr>";
  echo "<tr><td class=\"buttons\">";
  echo "<tr><td>Select a rating to Re-Calculate: ";
  $ratings->formatDropMenuR();
  echo "&nbsp;";
  echo "<input class=\"inputbutton\" type=\"submit\" name=\"action\" value=\"Re-Calcualte By Format\" /></td></tr>";
  echo "</table></form>";
}

function printAddCardSet() {
    echo "<h3><center>Install New Cardset</center></h3>";
    echo "<table class=\"form\" style=\"border-width: 0px\" align=\"center\">";
    echo "<input type=\"hidden\" name=\"return\" value=\"admincp.php\" />";
    echo "<input type=\"hidden\" name=\"ret_args\" value=\"view=add_cardset\" />";
    print_file_input("Cardset JSON", "cardsetfile");
    print_submit("Install New Cardset");
    echo "</table></form>";

    echo "<form action=\"util/insertcardset.php\" method=\"post\" enctype=\"multipart/form-data\">";
    flush();
    echo "<h3><center>Or</center></h3>";
    echo "<table class=\"form\" style=\"border-width: 0px\" align=\"center\">";
    echo "<input type=\"hidden\" name=\"return\" value=\"admincp.php\" />";
    echo "<input type=\"hidden\" name=\"ret_args\" value=\"view=add_cardset\" />";
    // print_text_input("Cardset code", "cardsetcode");
    $missing = SetScraper::getSetList();
    print_select_input("Missing Sets", "cardsetcode", $missing);
    print_submit("Install New Cardset");
    echo "</table></form>";
}

function printChangePasswordForm() {
  echo "<form action=\"admincp.php\" method=\"post\">";
  echo "<input type=\"hidden\" name=\"view\" value=\"change_password\" />";
  echo "<h3><center>Change User Password</center></h3>";
  echo "<table class=\"form\" style=\"border-width: 0px\" align=\"center\">";
  print_text_input("Username", "username");
  print_text_input("New Password", "new_password");
  print_submit("Change Password");
  echo "</table> </form>";
}

function handleActions() {
  global $hasError;
  global $errormsg;
  if (!isset($_POST['action'])) {
    return;
  }
  if ($_POST['action'] == "Change Password") {
    $player = new Player($_POST['username']);
    $player->setPassword($_POST['new_password']);
    $result = "Password changed for user {$player->name} to {$_POST['new_password']}";
  } else if ($_POST['action'] == "Install New Cardset") {
      $set = $_POST['edition'];
      $settype = $_POST['settype'];
      $releasedate = $_POST['releasedate'];
      //$file = fopen($_FILES['cardsetfile']['tmp_name'], "r");
      //insertCardSetAction ($set, $settype, $releasedate, $file);
      print_r($_POST);
      SetScraper::addNewSet($set[0],$releasedate,$settype );
      //$result = "New card set: $set was added to database!";
  } else if ($_POST['action'] == "Create Series") {
    $newactive = $_POST['isactive'];
    $newtime = $_POST['hour'];
    $newday = $_POST['start_day'];
    $prereg = 0;
    $pkonly = 0;

    if (isset($_POST['preregdefault'])) {
        $prereg = $_POST['preregdefault'];
    } else { 
        $prereg = 0;
    }

    if (isset($_POST['pkonlydefault'])) { 
        $pkonly = $_POST['pkonlydefault'];
    } else {
        $pkonly = 0;
    }
    $series = new Series("");
    $newseries = $_POST['seriesname']; 
    if ($series->authCheck(Player::loginName())) { 
      $series->name = $newseries;
      $series->active = $newactive; 
      $series->start_time = $newtime . ":00";
      $series->start_day = $newday;
      $series->prereg_default = $prereg;
      $series->pkonly_default = $pkonly;
      $series->save();
    } 
    $result = "New series $series->name was created!";
  } else if ($_POST['action'] == "Re-Calculate All Ratings") {
      $ratings = new Ratings();
      $ratings->deleteAllRatings();
      $ratings->calcAllRatings();
  } else if ($_POST['action'] == "Re-Calcualte By Format") {
      $ratings = new Ratings();
      $ratings->deleteRatingByFormat($_POST['format']);
      if ($_POST['format'] == "Composite") {
          $ratings->calcCompositeRating();
      } else {
          $ratings->calcRatingByFormat($_POST['format']);      
      }
    }
}

function printNewFormat(){
    echo "<h4>New Format</h4>\n";
    echo "<form action=\"admincp.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><td colspan=\"2\">New Format Name: <input type=\"text\" name=\"newformatname\" STYLE=\"width: 175px\"/></td></tr>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Create New Format\" name =\"action\" /></td></tr>";
    echo"</table></form>";
}

function printLoadFormat(){
    echo "<h4>Load Format</h4>\n";
    echo "<form action=\"admincp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><td>";
    formatsDropMenu("All");
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Load Format\" name =\"action\" /></td></tr>";
    echo"</table></form>";
}

function insertCardSetAction ($set, $settype, $releasedate, $file) {
echo "<div class=\"cardinsert_news grid_8 box\">";
echo "<div class=\"clear\"></div>";

if ($file == FALSE) {
  die("Can't open the file you uploaded: {$_FILES['cardsetfile']['tmp_name']}");
}

$card = array();
$cardsinserted = 0;

$database = Database::getConnection();

// Insert the card set
$stmt = $database->prepare("INSERT INTO cardsets(released, name, type) values(?, ?, ?)");
$stmt->bind_param("sss", $releasedate, $set, $settype); 

if (!$stmt->execute()) {
  echo "!!!!!!!!!! Set Insertion Error !!!!!!!!!<br /><br /><br />";
  die($stmt->error);
} else {
  echo "Inserted new {$settype} card set {$set}!<br />Inserting cards for {$set}...<br /><br />";
}
$stmt->close();
echo "<div class=\"clear\"></div>";
echo "</div>"; // News box

$stmt = $database->prepare("INSERT INTO cards(cost, convertedcost, name, cardset, type,
  isw, isu, isb, isr, isg, isp, rarity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);"); 

$addnewdiv = true;

while(!feof($file)) 
{ 
  if ($addnewdiv) {
      echo "<div class=\"cardinsert_news grid_8 box\">";
      echo "<div class=\"clear\"></div>";
      $addnewdiv = false;
  }
  $line = fgets($file);
  echo "Grabbing Line: {$line}<br />";
  if(preg_match("/^(.*):::\s+(.*)$/", $line, $matches)) 
  { 
    echo "Card Attribute: {$matches[1]}<br />";
    echo "Attribute Value: {$matches[2]}<br />";
    $card[$matches[1]] = $matches[2];
    if($matches[1] == "Set/Rarity") 
    {
      preg_match("/$set (Land|Common|Uncommon|Rare|Mythic Rare|Special|Bonus)/", $card[$matches[1]], $submatches);
      $card[$matches[1]] = $submatches[1];
      echo "<div class=\"clear\"></div>";
      echo "</div>"; // gatherling news
      echo "<div class=\"cardinsert_news grid_8 box\">";
      echo "<div class=\"clear\"></div>";
      echo "<br /><br />**********   Inserting Card   **********<br />";
      echo "<div class=\"clear\"></div>";
      echo "</div>"; // gatherling news
      $addnewdiv = true;
      $cardsinserted++;
      insertCard($card, $set, $submatches[1], $stmt);
    }
  }
  else
  {
      echo "Line is not usable content so will be ignored<br />";
  }
}
echo "<div class=\"clear\"></div>";
echo "</div>"; // gatherling news
echo "<div class=\"cardinsert_news grid_8 box\">";
echo "<div class=\"clear\"></div>";
echo "<br /><br />********** End of File Reached **********<br />";
echo "******** Total Cards Inserted: {$cardsinserted} ********<br />";
echo "<div class=\"clear\"></div>";
echo "</div>"; // gatherling news
$stmt->close();
}

function insertCard($card, $set, $rarity, $stmt) {
  # new gatherer - card type is now a . because of unicode
  $card['Type'] = str_replace('.', '-', $card['Type']);

  if(array_key_exists('Cost', $card)) {
      $cmc = getConvertedCost($card['Cost']);
      if (is_null($card['Cost'])) {
          $card['Cost'] = 0;
      }
  } else {
          $cmc = 0;
          $card['Cost'] = 0;
  }
  
  $card['Name'] = preg_replace("/Æ/", "AE", $card['Name']);
  $card['Name'] = preg_replace("/\306/", "AE", $card['Name']);
  $card['Name'] = preg_replace("/ö/", "o", $card['Name']);
  $card['Name'] = preg_replace("/é/", "e", $card['Name']);

  echo "<div class=\"cardsidecolumn cardinsert box\">";
  echo "<div class=\"clear\"></div>";
  echo "Card Name:           {$card['Name']}<br />";
  echo "Card Mana Cost:      {$card['Cost']}<br />";
  echo "Converted Mana Cost: {$cmc}<br />";
  echo "Card Type:           {$card['Type']}<br />";
  echo "Card Rarity:         {$rarity}<br />";
    
  $isw = $isu = $isb = $isr = $isg = $isp = 0;
  if(preg_match("/W/", $card['Cost'])) {$isw = 1;echo "Card is:             White<br />";}
  if(preg_match("/U/", $card['Cost'])) {$isu = 1;echo "Card is:             Blue<br />";}
  if(preg_match("/B/", $card['Cost'])) {$isb = 1;echo "Card is:             Black<br />";}
  if(preg_match("/R/", $card['Cost'])) {$isr = 1;echo "Card is:             Red<br />";}
  if(preg_match("/G/", $card['Cost'])) {$isg = 1;echo "Card is:             Green<br />";}
  if(preg_match("/P/", $card['Cost'])) {$isp = 1;echo "Card is:             Phyrexian<br />";}

  echo "Card Set:            {$set}<br /><br />";
  echo "<div class=\"clear\"></div>";
  echo "</div>"; // cardsidecolumn

  $stmt->bind_param("sdsssdddddds", $card['Cost'], $cmc, $card['Name'], $set, $card['Type'], $isw, $isu, $isb, $isr, $isg, $isp, $rarity); 

  echo "<div class=\"cardinsert_news grid_8 box\">";
  echo "<div class=\"clear\"></div>";
  
  if (!$stmt->execute()) {
    echo "!!!!!!!!!! Card Insertion Error !!!!!!!!!<br /><br /><br />";
    die($stmt->error);
  } else {
      echo "Card Inserted Successfully!<br /><br />";
  }
  
  echo "<div class=\"clear\"></div>";
  echo "</div>"; // gatherling news box
}

function getConvertedCost($cost) {
  if (is_null($cost)) {$cost = 0;}
  $cost = str_replace ('(','',$cost);
  $cost = str_replace (')','',$cost);
  $cost = str_replace ('/P','',$cost);
  $cost = str_replace ('W/','',$cost);
  $cost = str_replace ('R/','',$cost);
  $cost = str_replace ('G/','',$cost);
  $cost = str_replace ('U/','',$cost);
  $cost = str_replace ('B/','',$cost);
  $cost = str_replace ('1/','',$cost);
  $cost = str_replace ('2/','',$cost);
  $cost = str_replace ('3/','',$cost);
  $cost = str_replace ('4/','',$cost);
  $cost = str_replace ('5/','',$cost);
  $cost = str_replace ('6/','',$cost);
  $cost = str_replace ('7/','',$cost);
  $cost = str_replace ('8/','',$cost);
  $cost = str_replace ('9/','',$cost);
  $cost = chop($cost);
  $cmc = strlen($cost);
  if(preg_match("/^([0-9])/", $cost, $matches)) {
    $cmc = $matches[1] + strlen($cost) - 1;
  }
  return $cmc;
}

