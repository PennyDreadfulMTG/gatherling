<?php
session_start();
include 'lib.php';
include 'lib_form_helper.php';

$hasError = false;
$errormsg = "";

if (!Player::isLoggedIn() || !Player::getSessionPlayer()->isSuper()) {
  redirect("index.php");
}

print_header("Format Control Panel");
?>

<div class="grid_10 suffix_1 prefix_1">
<div id="gatherling_main" class="box">
<div class="uppertitle"> Format Control Panel </div>
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
  
  printFormatCPIntroduction();
  printError();
  handleActions();
  
  $view = "change_password";

  if (isset($_GET['view']) && ($_GET['view'] != "")) {$view = $_GET['view'];}
  if (isset($_POST['view'])) {$view = $_POST['view'];}
  
  if (isset($_POST['format'])) {
        Format::formatEditor("formatcp.php", $_POST['format'], "System");
    } else {
    printNewFormat();
    printLoadFormat();
    } 
  echo "</center><div class=\"clear\"></div></div>";
}

function printFormatCPIntroduction() {
    echo "<br />";
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

function handleActions() {
    global $hasError;
    global $errormsg;
    if (!isset($_POST['action'])) {
      return;
    }
    if ($_POST['action'] == "Update Banlist") {
        $active_format = $_POST['format'];
        $format = new Format($active_format);
  
        if (isset($_POST['addbancard']) && $_POST['addbancard'] != '') {
            $cards = parseCards($_POST['addbancard']);
            if(count($cards) > 0) {
                foreach($cards as $card) {
                    $success = $format->insertCardIntoBanlist($card);
                }
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't add {$card} to Ban list, it is either not in the database, on the legal card list, or already on the ban list";
                    return; 
                }
            }
        }
  
        if (isset($_POST['delbancards'])) {
            $delBanCards = $_POST['delbancards'];
            foreach($delBanCards as $cardName){
                $success = $format->deleteCardFromBanlist($cardName);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$cardName} from ban list";
                    return; 
                }
            }
        }
    } else if ($_POST['action'] == "Delete Entire Banlist") {
        $format = new Format($_POST['format']);
        $success = $format->deleteEntireBanlist(); // leave a message of success
    } else if ($_POST['action'] == "Update Legal List") {
        $active_format = $_POST['format'];
        $format = new Format($active_format);
  
        if (isset($_POST['addlegalcard']) && $_POST['addlegalcard'] != '') {
            $cards = parseCards($_POST['addlegalcard']);
            if(count($cards) > 0) {
                foreach($cards as $card) {
                    $success = $format->insertCardIntoLegallist($card);
                }
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't add {$card} to Legal list, it is either not in the database, already on the ban list, or already on the legal list";
                    return; 
                }
            }
        }
  
        if (isset($_POST['dellegalcards'])) {
            $dellegalCards = $_POST['dellegalcards'];
            foreach($dellegalCards as $cardName){
                $success = $format->deleteCardFromLegallist($cardName);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$cardName} from legal list";
                    return; 
                }
            }
        }
    }else if ($_POST['action'] == "Delete Entire Legal List") {
        $format = new Format($_POST['format']);
        $success = $format->deleteEntireLegallist(); // leave a message of success
    } else if ($_POST['action'] == "Update Cardsets") {
        $format = new Format($_POST['format']);
        
        if(isset($_POST['cardsetname'])) {
            $cardsetName = $_POST['cardsetname'];
            if ($cardsetName != "Unclassified") {
                $format->insertNewLegalSet($cardsetName);
            }     
        }
        
        if(isset($_POST['delcardsetname'])) {
            $delcardsets = $_POST['delcardsetname'];
            foreach($delcardsets as $cardset) {
                $success = $format->deleteLegalCardSet($cardset);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$cardset} from allowed cardsets";
                    return; 
                }
            }
        }      
    } else if ($_POST['action'] == "Update Restricted List") {
        $active_format = $_POST['format'];
        $format = new Format($active_format);
  
        if (isset($_POST['addrestrictedcard']) && $_POST['addrestrictedcard'] != '') {
            $cards = parseCards($_POST['addrestrictedcard']);
            if(count($cards) > 0) {
                foreach($cards as $card) {
                    $success = $format->insertCardIntoRestrictedlist($card);
                }
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't add {$card} to Restricted list, it is either not in the database, on the ban list, legal card list, or already on the restricted list";
                    return; 
                }
            }
        }
  
        if (isset($_POST['delrestrictedcards'])) {
            $delRestrictedCards = $_POST['delrestrictedcards'];
            foreach($delRestrictedCards as $cardName){
                $success = $format->deleteCardFromRestrictedlist($cardName);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$cardName} from restricted list";
                    return; 
                }
            }
        }
    }else if ($_POST['action'] == "Delete Entire Restricted List") {
        $format = new Format($_POST['format']);
        $success = $format->deleteEntireRestrictedlist(); // leave a message of success
    } else if($_POST['action'] == "Update Format") {
        $format = new Format($_POST['format']);
  
        if(isset($_POST['formatdescription'])) {$format->description = $_POST['formatdescription'];}
        
        if(isset($_POST['minmain'])) {$format->min_main_cards_allowed = $_POST['minmain'];}    
        if(isset($_POST['maxmain'])) {$format->max_main_cards_allowed = $_POST['maxmain'];}    
        if(isset($_POST['minside'])) {$format->min_side_cards_allowed = $_POST['minside'];}    
        if(isset($_POST['maxside'])) {$format->max_side_cards_allowed = $_POST['maxside'];}    
  
        if(isset($_POST['singleton']))        {$format->singleton = 1;}         else {$format->singleton = 0;}    
        if(isset($_POST['commander']))        {$format->commander = 1;}         else {$format->commander = 0;}
        if(isset($_POST['vanguard']))         {$format->vanguard = 1;}          else {$format->vanguard = 0;}
        if(isset($_POST['planechase']))       {$format->planechase = 1;}        else {$format->planechase = 0;}
        if(isset($_POST['prismatic']))        {$format->prismatic = 1;}         else {$format->prismatic = 0;}
  
        if(isset($_POST['allowcommons']))     {$format->allow_commons = 1;}     else {$format->allow_commons = 0;}    
        if(isset($_POST['allowuncommons']))   {$format->allow_uncommons = 1;}   else {$format->allow_uncommons = 0;}
        if(isset($_POST['allowrares']))       {$format->allow_rares = 1;}       else {$format->allow_rares = 0;}
        if(isset($_POST['allowmythics']))     {$format->allow_mythics = 1;}     else {$format->allow_mythics = 0;}
        if(isset($_POST['allowtimeshifted'])) {$format->allow_timeshifted = 1;} else {$format->allow_timeshifted = 0;}
        
        $format->save();
    } else if($_POST['action'] == "New") {
      printNewFormat();
    } else if($_POST['action'] == "Create New Format") {      
        $format = new Format("");
        $format->name = $_POST['newformatname'];
        $format->type = "System";
        $format->series_name = "System";
        $success = $format->save();
        if ($success) {
            echo "<h4>New Format $format->name Created Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"$format->name\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>New Format {$_POST['newformatname']} Could Not Be Created:-(</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }
    } else if($_POST['action'] == "Load") {
          printLoadFormat();
    } else if($_POST['action'] == "Save As") {
        $format = new Format($_POST['format']);
        $oldformatname = $format->name;
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
        echo "<input type=\"hidden\" name=\"oldformat\" value=\"$oldformatname\" />";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><td colspan=\"2\">Save Format As... <input type=\"text\" class=\"inputbox\" name=\"newformat\" STYLE=\"width: 175px\"/></td></tr>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Save\" name =\"action\" /></td></tr>";
        echo"</table></form>";
    } else if($_POST['action'] == "Save") {
        $format = new Format("");
        $format->name = $_POST['newformat'];
        $format->type = "System";
        $format->series_name = "System";
        $success = $format->saveAs($_POST['oldformat']);
        if ($success) {
            echo "<h4>New Format $format->name Saved Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"$format->name\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>New Format {$_POST['newformat']} Could Not Be Saved :-(</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"{$_POST['oldformat']}\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }
    } else if($_POST['action'] == "Rename") {
        echo "<h4>Rename Format</h4>\n";
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><td>";
        formatsDropMenu("All");
        echo "</td>";
        echo "<td colspan=\"2\">Rename Format As... <input type=\"text\" name=\"newformat\" STYLE=\"width: 175px\"/></td></tr>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Rename Format\" name =\"action\" /></td></tr>";
        echo"</table></form>";
    } else if($_POST['action'] == "Rename Format") {
        $format = new Format("");
        $format->name = $_POST['newformat'];
        $format->type = "System";
        $format->series_name = "System";
        $success = $format->rename($_POST['format']);
        if ($success) {
            echo "<h4>Format {$_POST['format']} Renamed as $format->name Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"$format->name\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>Format {$_POST['format']} Could Not Be Renamed :-(</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"format\" value=\"{$_POST['format']}\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }
    } else if($_POST['action'] == "Delete") {
        echo "<h4>Delete Format</h4>\n";
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><td>";
        formatsDropMenu("All");
        echo "</td>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Delete Format\" name =\"action\" /></td></tr>";
        echo"</table></form>";
    } else if($_POST['action'] == "Delete Format") {
        $format = new Format($_POST['format']);
        $success = $format->delete();
        if ($success) {
            echo "<h4>Format {$_POST['format']} Deleted Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>Could Not Delete {$_POST['format']}!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"format\" value=\"{$_POST['format']}\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }      
    }
  }

  function printNewFormat(){
    echo "<h4>New Format</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><td colspan=\"2\">New Format Name: <input type=\"text\" name=\"newformatname\" STYLE=\"width: 175px\"/></td></tr>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Create New Format\" name =\"action\" /></td></tr>";
    echo"</table></form>";
}

function printLoadFormat(){
    echo "<h4>Load Format</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><td>";
    formatsDropMenu("All");
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Load Format\" name =\"action\" /></td></tr>";
    echo"</table></form>";
}