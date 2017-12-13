<?php
session_start();
include 'lib.php';
include 'lib_form_helper.php';
$hasError = false;
$errormsg = "";

if (!Player::isLoggedIn()) {
    redirect("login.php");
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
    if ($player->isOrganizer() || $player->isSuper()) {
        $player_series = Player::getSessionPlayer()->organizersSeries();
        if ($player->isSuper()) {
            array_unshift($player_series, "System");
        }

        if (isset($_REQUEST['series']))
        {
            $seriesName = $_REQUEST['series'];
        }
        else
        {
            $seriesName = $player_series[0];
        }
        
        if (count($player_series) > 1) {
            printOrganizerSelect($player_series, $seriesName);
        } else {
            echo "<center> Managing {$seriesName} </center>";
        } 
        
        $auth = false;
        foreach ($player_series as $ps)
        {
            if (strcmp($seriesName, $ps) == 0)
            {
                $auth = true;
            }
        }
        if (!$auth)
        {
            printNoAdmin($player->isOrganizer());
            return;
        }
    }
    else
    {
        printNoAdmin($player->isOrganizer());
        printError();
        return;
    }
    
    handleActions($seriesName);
    printError();
    
    $view = "settings";
    
    if (isset($_REQUEST['view']) && ($_REQUEST['view'] != "")) {
        $view = $_REQUEST['view'];
    }

    if (!isset($_REQUEST['format'])) {
        printLoadFormat($seriesName);
        formatCPMenu(new Format(""), $seriesName);
        return;
    }
    $format = $_REQUEST['format'];

    if(Format::doesFormatExist($format)) {
        $active_format = new Format($format);
    } else {
        $active_format = new Format("");
    }
    formatCPMenu($active_format, $seriesName);
    switch ($view){
        case 'settings':
        printFormatSettings($active_format, $seriesName);
        break;
        case 'bandr':
        printBandR($active_format, $seriesName);
        break;
        case 'tribal':
        printTribalBandR($active_format, $seriesName);
        break;
        case 'cardsets':
        printCardSets($active_format, $seriesName);
        break;
        case 'no_view':
        break;
        default:
        echo "Unknown View!";
    }
    echo "</center><div class=\"clear\"></div></div>";
}

function printNoAdmin($isOrganizer) { 
    $hasError = true;
    if ($isOrganizer)
    $errormsg = "<center>You're not authorized to edit this format! Access Restricted.<br />";
    else
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

function handleActions($seriesName) {
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
                    if (!in_array($card, $format->card_legallist)) {
                        $success = $format->insertCardIntoLegallist($card);
                        if(!$success) {
                            $hasError = true;
                            $errormsg .= "Can't add {$card} to Legal list, it is either not in the database or on the ban list.";
                            return; 
                        }
                    }
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
    } else if (strncmp($_POST['action'], "Add All", 7) == 0) {
        $format = new Format($_POST['format']);
        $cardsetType = substr($_POST['action'], 8);
        $missing = getMissingSets($cardsetType, $format);
        foreach ($missing as $set)
        {
            $format->insertNewLegalSet($set);
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
        if(isset($_POST['tribal']))           {$format->tribal = 1;}            else {$format->tribal = 0;}

        if(isset($_POST['allowcommons']))     {$format->allow_commons = 1;}     else {$format->allow_commons = 0;}    
        if(isset($_POST['allowuncommons']))   {$format->allow_uncommons = 1;}   else {$format->allow_uncommons = 0;}
        if(isset($_POST['allowrares']))       {$format->allow_rares = 1;}       else {$format->allow_rares = 0;}
        if(isset($_POST['allowmythics']))     {$format->allow_mythics = 1;}     else {$format->allow_mythics = 0;}
        if(isset($_POST['allowtimeshifted'])) {$format->allow_timeshifted = 1;} else {$format->allow_timeshifted = 0;}

        if(isset($_POST['eternal'])) {$format->eternal = 1;} else {$format->eternal = 0;}
        
        $format->save();
    } else if($_POST['action'] == "New") {
        printNewFormat();
    } else if($_POST['action'] == "Create New Format") {      
        $format = new Format("");
        $format->name = $_POST['newformatname'];
        $seriesType = "Private";
        if ($seriesName == "System")
        {
            $seriesType = "System";
        }
        $format->type = $seriesType;
        $format->series_name = $seriesName;
        $success = $format->save();
        if ($success) {
            echo "<h4>New Format $format->name Created Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"$format->name\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>New Format {$_POST['newformatname']} Could Not Be Created:-(</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }
    } else if($_POST['action'] == "Load") {
        printLoadFormat($seriesName);
    } else if ($_POST['action'] == "Load Format") {
        // Nothing needs to be done.
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
        $seriesType = "Private";
        if ($seriesName == "System")
        {
            $seriesType = "System";
        }
        $format->type = $seriesType;
        $format->series_name = $seriesName;
        $success = $format->saveAs($_POST['oldformat']);
        if ($success) {
            echo "<h4>New Format $format->name Saved Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
            echo "<input type=\"hidden\" name=\"format\" value=\"$format->name\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>New Format {$_POST['newformat']} Could Not Be Saved :-(</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
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
        if ($seriesName == "System") { formatsDropMenu("All"); } 
        else { formatsDropMenu("Private", $seriesName); }
        echo "</td>";
        echo "<td colspan=\"2\">Rename Format As... <input type=\"text\" name=\"newformat\" STYLE=\"width: 175px\"/></td></tr>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Rename Format\" name =\"action\" /></td></tr>";
        echo"</table></form>";
    } else if($_POST['action'] == "Rename Format") {
        $format = new Format("");
        $format->name = $_POST['newformat'];
        $seriesType = "Private";
        if ($seriesName == "System")
        {
            $seriesType = "System";
        }
        $format->type = $seriesType;
        $format->series_name = $seriesName;
        $success = $format->rename($_POST['format']);
        if ($success) {
            echo "<h4>Format {$_POST['format']} Renamed as $format->name Successfully!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
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
        if ($seriesName == "System") { formatsDropMenu("All"); } 
        else { formatsDropMenu("Private", $seriesName); }
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
            echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";
        } else {
            echo "<h4>Could Not Delete {$_POST['format']}!</h4>";
            echo "<form action=\"formatcp.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"format\" value=\"{$_POST['format']}\" />";
            echo "<input class=\"inputbutton\" type=\"submit\" value=\"Continue\" name =\"action\" />";
            echo "</form>";          
        }
    // restricted to tribe start
    } else if ($_POST['action'] == "Update Restricted To Tribe List") {
        $format = new Format($_POST['format']);
        if (isset($_POST['addrestrictedtotribecreature']) && $_POST['addrestrictedtotribecreature'] != '') {
            $cards = parseCards($_POST['addrestrictedtotribecreature']);
            if(count($cards) > 0) {
                foreach($cards as $card) {
                    $success = $format->insertCardIntoRestrictedToTribeList($card);
                }
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't add {$card} to Restricted to tribe list, it is either not in the database, currently on the ban list, or is already on the Restricted to Tribe List";
                    return; 
                }
            }
        }
        if (isset($_POST['delrestrictedtotribe'])) {
            $delRestrictedToTribe = $_POST['delrestrictedtotribe'];
            foreach($delRestrictedToTribe as $cardName){
                $success = $format->deleteCardFromRestrictedToTribeList($cardName);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$cardName} from restricted to tribe list.";
                    return; 
                }
            }
        }
    } else if ($_POST['action'] == "Delete Entire Restricted To Tribe List") {
        $format = new Format($_POST['format']);
        $success = $format->deleteEntireRestrictedToTribeList(); // leave a message of success
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
    } else if ($_POST['action'] == "Update Subtype Ban") {
        $format = new Format($_POST['format']);
        
        if(isset($_POST['subtypeban'])) {
            $subTypeName = $_POST['subtypeban'];
            if ($subTypeName != "Unclassified") {
                $format->insertNewSubTypeBan($subTypeName);
            }     
        }
        
        if(isset($_POST['delbannedsubtype'])) {
            $delbannedsubtypes = $_POST['delbannedsubtype'];
            foreach($delbannedsubtypes as $bannedsubtype) {
                $success = $format->deleteSubTypeBan($bannedsubtype);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$bannedsubtype} from banned subtypes";
                    return; 
                }
            }
        }      
    } else if ($_POST['action'] == "Update Tribe Ban") {
        $format = new Format($_POST['format']);
        
        if(isset($_POST['tribeban'])) {
            $tribeName = $_POST['tribeban'];
            if ($tribeName != "Unclassified") {
                $format->insertNewTribeBan($tribeName);
            }     
        }
        
        if(isset($_POST['delbannedtribe'])) {
            $delbannedtribes = $_POST['delbannedtribe'];
            foreach($delbannedtribes as $bannedtribe) {
                $success = $format->deleteTribeBan($bannedtribe);
                if(!$success) {
                    $hasError = true;
                    $errormsg .= "Can't delete {$bannedtribe} from banned tribes";
                    return; 
                }
            }
        }      
    } else if ($_POST['action'] == "Ban All Tribes") {
        $format = new Format($_POST['format']);
        
        $format->banAllTribes();
    }
    else {
        $hasError = true;
        $errormsg = "Unknown action '{$_POST['action']}'";
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

function printLoadFormat($seriesName){
    echo "<h4>Load Format</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><td>";
    if ($seriesName == "System") { formatsDropMenu("All"); } 
    else { formatsDropMenu("Private", $seriesName); }
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Load Format\" name =\"action\" /></td></tr>";
    echo"</table></form>";
}

function printFormatSettings($active_format, $seriesName) {
    echo "<p style=\"width: 75%; text-align: left;\">This is where you define the format for your series. Step one is to 
    add the card sets that you want to allow players to use to build decks. Once you do that, any cards in those 
    sets you don't want players to use, add to the ban list. You don't need to ban cards that aren't in the allowed 
    card sets. Finally make sure that the appropriate rarities that you want to allow are checked. For example
    a pauper event would leave only the commons box checked.</p>";
    echo "<p style=\"width: 75%; text-align: left;\">The name of this filter will default to the name of the series.  
    To use this filter, go to the Season Points Management->Season Format and select this filter. This sets the
    filter to be used for the entire season. You can also set this filter by going to Host CP->Format. This only
    sets the filter to be used for that single event.</p>";
    echo "<p style=\"width: 75%; text-align: left;\">Coming in a future update will be the ability for you to create
    and manage your own custom filters. That way you can have Alt Events that have special filters.</p>";
    
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"settings\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    
    echo "<h4>Format Description</h4>";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    
    echo "<tr><td>";
    echo "<textarea class=\"inputbox\" rows=\"10\" cols=\"60\" name=\"formatdescription\">";
    echo "$active_format->description";
    echo "</textarea>";
    echo "</td></tr>\n";
    echo "</table>";
    echo "<h4>Card Modifiers</h4>";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    echo "<tr><th>Minimum Mainboard Cards</th>";
    echo "<td style=\"width: 50px; text-align: center;\">";
    stringField("minmain", $active_format->min_main_cards_allowed, 5);
    print_warning_if($active_format->min_main_cards_allowed == 0);
    echo "</td>";
    echo "<th>&nbsp;Maximum Mainboard Cards&nbsp;</th>";
    echo "<td style=\"width: 50px; text-align: center;\">";
    stringField("maxmain", $active_format->max_main_cards_allowed, 5);
    print_warning_if($active_format->max_main_cards_allowed == 0);
    echo "</td>";
    echo "</tr><tr><th>Minimum Sideboard Cards</th>";
    echo "<td style=\"width: 50px; text-align: center;\">";
    stringField("minside", $active_format->min_side_cards_allowed, 5);
    echo "</td>";
    echo "<th>&nbsp;Maximum Sideboard Cards&nbsp;</th>";
    echo "<td style=\"width: 50px; text-align: center;\">";
    stringField("maxside", $active_format->max_side_cards_allowed, 5);
    echo "</td>";
    echo "</tr></table>";
    
    echo "<h4>Deck Modifiers</h4>";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    echo "<tr><th style=\"width: 100px; text-align: center;\">Singleton</th><th style=\"width: 100px; text-align: center;\">Commander</th>";
    echo "<th style=\"width: 100px; text-align: center;\">Vanguard</th><th style=\"width: 100px; text-align: center;\">Planechase</th>";
    echo "<th style=\"width: 100px; text-align: center;\">Prismatic</th><th style=\"width: 100px; text-align: center;\">Tribal</th></tr>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"singleton\" value=\"1\" ";
    if($active_format->singleton == 1) {echo "checked=\"yes\" ";}   
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"commander\" value=\"1\" ";
    if($active_format->commander == 1) {echo "checked=\"yes\" ";} 
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"vanguard\" value=\"1\" ";
    if($active_format->vanguard == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"planechase\" value=\"1\" ";
    if($active_format->planechase == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"prismatic\" value=\"1\" ";
    if($active_format->prismatic == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"tribal\" value=\"1\" ";
    if($active_format->tribal == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "</tr></table>";
    
    echo "<h4>Allow Rarity Selection</h4>";
    print_warning_if(0 == $active_format->allow_commons + $active_format->allow_uncommons + $active_format->allow_rares + $active_format->allow_mythics + $active_format->allow_timeshifted);
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    echo "<tr><th style=\"width: 100px; text-align: center;\">Commons</th><th style=\"width: 100px; text-align: center;\">Uncommons</th>";
    echo "<th style=\"width: 100px; text-align: center;\">Rares</th><th style=\"width: 100px; text-align: center;\">Mythics</th>";
    echo "<th style=\"width: 100px; text-align: center;\">Timeshifted</th></tr>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"allowcommons\" value=\"1\" ";
    if($active_format->allow_commons == 1) {echo "checked=\"yes\" ";}   
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"allowuncommons\" value=\"1\" ";
    if($active_format->allow_uncommons == 1) {echo "checked=\"yes\" ";} 
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"allowrares\" value=\"1\" ";
    if($active_format->allow_rares == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"allowmythics\" value=\"1\" ";
    if($active_format->allow_mythics == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"allowtimeshifted\" value=\"1\" ";
    if($active_format->allow_timeshifted == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<h4>Format Modifiers</h4>";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    echo "<tr><th style=\"width: 100px; text-align: center;\">";
    print_tooltip("Eternal", "Eternal Formats treat all cardsets as legal.");
    echo " Format</th></tr>";
    echo "<td style=\"width: 100px; text-align: center;\"><input type=\"checkbox\" name=\"eternal\" value=\"1\" ";
    if($active_format->eternal == 1) {echo "checked=\"yes\" ";}    
    echo " /></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td colspan=\"5\" class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Update Format\" name =\"action\" /></td>";
    echo "</tr>";
    echo "</table></form>";
}

function formatCPMenu($active_format, $seriesName) {
    echo "<center>";
    echo "<h3>Format Editor</h3>";
    if ($active_format->name != "") {echo "<h4>Currently Editing: $active_format->name</h4>";}
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"no_view\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">";
    echo "<tr><td class=\"buttons\"><input class=\"inputbutton\" style=\"width: 75px\" type=\"submit\" value=\"New\" name =\"action\" />";
    echo "<input class=\"inputbutton\" style=\"width: 75px\" type=\"submit\" value=\"Load\" name =\"action\" />";
    echo "<input class=\"inputbutton\" style=\"width: 75px\" type=\"submit\" value=\"Save As\" name =\"action\" />"; 
    echo "<input class=\"inputbutton\" style=\"width: 75px\" type=\"submit\" value=\"Rename\" name =\"action\" />"; 
    echo "<input class=\"inputbutton\" style=\"width: 75px\" type=\"submit\" value=\"Delete\" name =\"action\" /></tr>";
    echo "</table></form>";
    if ($active_format->name != "") 
    {
        $escaped = urlencode($active_format->name);
        echo "<table><tr><td colspan=\"2\" align=\"center\">";
        echo "<a href=\"formatcp.php?view=settings&format={$escaped}\">Format Settings</a>";
        echo " | <a href=\"formatcp.php?view=bandr&format={$escaped}\">Legal, Banned & Restricted</a>";
        if ($active_format->tribal) {
            echo " | <a href=\"formatcp.php?view=tribal&format={$escaped}\">Tribes</a>";            
        }
        if (!$active_format->eternal) {
            echo " | <a href=\"formatcp.php?view=cardsets&format={$escaped}\">Legal Sets</a>";
        }
        else {
            echo " | ";
            print_tooltip("Legal Sets", "All sets are legal, as this is an Eternal format");
        }
        echo "</td></tr></table>";
    }
}

function printBandR($active_format, $seriesName)
{
    $bandCards = $active_format->getBanList();
    $legalCards = $active_format->getLegalList();
    $restrictedCards = $active_format->getRestrictedList();
    
    // beginning of the restricted list
    $cardCount = count($restrictedCards);
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    echo "<h4>Card Restricted List: $cardCount Cards</h4>\n";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><th style=\"text-align: center;\">Card Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
    if (count($restrictedCards)) {
        foreach($restrictedCards as $card) {
            echo "<tr><td style=\"text-align: center;\">";
            // don't print card link if list is over 100 cards
            if ($cardCount > 100) {
                echo "$card <br />";
            } else {
                printCardLink($card);
            }
            echo "</td>";
            echo "<td style=\"text-align: center;\">";
            echo "<input type=\"checkbox\" name=\"delrestrictedcards[]\" value=\"{$card}\" /></td></tr>";
        }
    } else {
        echo "<tr><td><font color=\"red\">No cards have been restricted</font></td>";
        echo "<td style=\"width: 100px; text-align: center;\">";
        not_allowed("No Restricted Cards To Delete");            
        echo "</td>";
        echo "</tr>";
    }
    echo "<tr><td colspan=\"2\"> Add new: ";
    echo "<textarea class=\"inputbox\" rows=\"5\" cols=\"40\" name=\"addrestrictedcard\"></textarea></td></tr>\n";
    echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
    echo "<tr>";
    echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Update Restricted List\" name =\"action\" /></td>";
    echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Delete Entire Restricted List\" name =\"action\" /></td>";
    echo "</tr></table></form>";

     // if the series is using a legal card list, don't show the banlist
     if (!count($legalCards)) {
        $cardCount = count($bandCards);
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
        echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
        echo "<h4>Card Banlist: $cardCount Cards</h4>\n";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><th style=\"text-align: center;\">Card Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
        if (count($bandCards)) {
            foreach($bandCards as $card) {
                echo "<tr><td style=\"text-align: center;\">";
                // don't print card link if list is over 100 cards
                if ($cardCount > 100) {
                    echo "$card <br />";
                } else {
                    printCardLink($card);
                }
                echo "</td>";
                echo "<td style=\"text-align: center;\">";
                echo "<input type=\"checkbox\" name=\"delbancards[]\" value=\"{$card}\" /></td></tr>";
            }
        } else {
            echo "<tr><td><font color=\"red\">No cards have been banned</font></td>";
            echo "<td style=\"width: 100px; text-align: center;\">";
            not_allowed("No Ban Cards To Delete");            
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr><td colspan=\"2\"> Add new: ";
        echo "<textarea class=\"inputbox\" rows=\"5\" cols=\"40\" name=\"addbancard\"></textarea></td></tr>\n";
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<tr>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Update Banlist\" name =\"action\" /></td>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Delete Entire Banlist\" name =\"action\" /></td>";
        echo "</tr></table></form>";
    }
    
    // if the series is using a ban list, then don't show the legal card list
    if (!count($bandCards)) {
        $cardCount = count($legalCards);
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
        echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
        echo "<h4>Legal Card List: $cardCount Cards</h4>\n";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><th style=\"text-align: center;\">Card Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
        if (count($legalCards)) {
            foreach($legalCards as $card) {
                echo "<tr><td style=\"text-align: center;\">";
                // don't print card link if list is over 100 cards
                if ($cardCount > 100) {
                    echo "$card <br />";
                } else {
                    printCardLink($card);
                }
                echo "</td>";
                echo "<td style=\"text-align: center;\">";
                echo "<input type=\"checkbox\" name=\"dellegalcards[]\" value=\"{$card}\" /></td></tr>";
            }
        } else {
            echo "<tr><td><font color=\"red\">No cards have been allowed</font></td>";
            echo "<td style=\"width: 100px; text-align: center;\">";
            not_allowed("No Legal List Cards to Delete");            
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr><td colspan=\"2\"> Add new: ";
        echo "<textarea class=\"inputbox\" rows=\"5\" cols=\"40\" name=\"addlegalcard\"></textarea></td></tr>\n";
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<tr>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Update Legal List\" name =\"action\" /></td>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Delete Entire Legal List\" name =\"action\" /></td>";
        echo "</tr></table></form>";
    }
}

function printTribalBandR($active_format, $seriesName) {
    $restrictedToTribe = $active_format->getRestrictedToTribeList();
    // restricted list to tribe
    if ($active_format->tribal) {
        $cardCount = count($restrictedToTribe);
        echo "<form action=\"formatcp.php\" method=\"post\">";
        echo "<input type=\"hidden\" name=\"view\" value=\"tribal\" />";
        echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
        echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
        echo "<h4>Restricted To Tribe List: $cardCount Cards</h4>\n";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><th style=\"text-align: center;\">Card Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
        if (count($restrictedToTribe)) {
            foreach($restrictedToTribe as $card) {
                echo "<tr><td style=\"text-align: center;\">";
                // don't print card link if list is over 100 cards
                if ($cardCount > 100) {
                    echo "$card <br />";
                } else {
                    printCardLink($card);
                }
                echo "</td>";
                echo "<td style=\"text-align: center;\">";
                echo "<input type=\"checkbox\" name=\"delrestrictedtotribe[]\" value=\"{$card}\" /></td></tr>";
            }
        } else {
            echo "<tr><td><font color=\"red\">No creatures have been restricted to tribe</font></td>";
            echo "<td style=\"width: 100px; text-align: center;\">";
            not_allowed("No Restricted To Tribe Creatures To Delete");            
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr><td colspan=\"2\"> Add new: ";
        echo "<textarea class=\"inputbox\" rows=\"5\" cols=\"40\" name=\"addrestrictedtotribecreature\"></textarea></td></tr>\n";
        echo "<input type=\"hidden\" name=\"view\" value=\"format_editor\" />";
        echo "<tr>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Update Restricted To Tribe List\" name =\"action\" /></td>";
        echo "<td class=\"buttons\"><input class=\"inputbutton\" type=\"submit\" value=\"Delete Entire Restricted To Tribe List\" name =\"action\" /></td>";
        echo "</tr></table></form>";
    }

    // tribe ban
    // tribe will be banned, subtype will still be allowed in other tribes decks
    if ($active_format->tribal) {
        $tribesBanned = $active_format->getTribesBanned();
        echo "<h4>Tribe Banlist</h4>\n";
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
        echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><th style=\"text-align: center;\">Tribe Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
        if (count($tribesBanned)) {
            foreach($tribesBanned as $bannedTribe) {
                echo "<tr><td style=\"text-align: center;\">$bannedTribe</td>";
                echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" name=\"delbannedtribe[]\" value=\"$bannedTribe\" />";
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td><font color=\"red\">No Tribes Currently Banned</font></td>";
            echo "<td style=\"width: 100px; text-align: center;\">";
            not_allowed("No Selected Tribe To Delete");            
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr><td>";
        tribeBanDropMenu($active_format);
        echo "</td>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Update Tribe Ban\" name =\"action\" />";
        echo "</td><td>";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Ban All Tribes\" name =\"action\" />";
        echo"</td></tr></table></form>";    
    }   
        
    // subtype ban
    // subtype is banned and is not allowed to be used by any deck
    if ($active_format->tribal) {
        $subTypesBanned = $active_format->getSubTypesBanned();
        echo "<h4>Subtype Banlist</h4>\n";
        echo "<form action=\"formatcp.php\" method=\"post\">"; 
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
        echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
        echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
        echo "<tr><th style=\"text-align: center;\">Tribe Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
        if (count($subTypesBanned)) {
            foreach($subTypesBanned as $bannedSubType) {
                echo "<tr><td style=\"text-align: center;\">$bannedSubType</td>";
                echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" name=\"delbannedsubtype[]\" value=\"$bannedSubType\" />";
                echo "</td></tr>";
            }
        } else {
            echo "<tr><td><font color=\"red\">No Subtypes Currently Banned</font></td>";
            echo "<td style=\"width: 100px; text-align: center;\">";
            not_allowed("No Selected SubType To Delete");            
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr><td>";
        subTypeBanDropMenu($active_format);
        echo "</td>";
        echo "<td colspan=\"2\" class=\"buttons\">";
        echo "<input type=\"hidden\" name=\"view\" value=\"bandr\" />";
        echo "<input class=\"inputbutton\" type=\"submit\" value=\"Update Subtype Ban\" name =\"action\" />";
        echo"</td></tr></table></form>";    
    }
}

function printCardSets($active_format, $seriesName) {
    $coreCardSets = $active_format->getCoreCardsets();
    $blockCardSets = $active_format->getBlockCardsets();
    $extraCardSets = $active_format->getExtraCardsets();
    echo "<h4>Core Cardsets Allowed</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><th style=\"text-align: center;\">Cardset Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
    if (count($coreCardSets)) {
        foreach($coreCardSets as $setName) {
            echo "<tr><td style=\"text-align: center;\">{$setName}</td>";
            echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" name=\"delcardsetname[]\" value=\"{$setName}\" />";
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td><font color=\"red\">No Core Sets are Allowed</font></td>";
        echo "<td style=\"width: 100px; text-align: center;\">";
        not_allowed("No Selected Card Set To Delete");            
        echo "</td>";
        echo "</tr>";
    }
    echo "<tr><td>";
    cardsetDropMenu("Core", $active_format, false);
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Update Cardsets\" name =\"action\" />";
    echo"</td></tr></table></form>";
    
    echo "<h4>Block Cardsets Allowed</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><th style=\"text-align: center;\">Cardset Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
    if (count($blockCardSets)) {
        foreach($blockCardSets as $setName) {
            echo "<tr><td style=\"text-align: center;\">{$setName}</td>";
            echo "<td style=\"text-align: center; width: 50px; \"><input type=\"checkbox\" name=\"delcardsetname[]\" value=\"{$setName}\" />";
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td><font color=\"red\">No Block Sets are Allowed</font></td>";
        echo "<td style=\"width: 100px; text-align: center;\">";
        not_allowed("No Selected Card Set To Delete");            
        echo "</td>";
        echo "</tr>";
    }
    
    echo "<tr><td>";
    cardsetDropMenu("Block", $active_format, false);
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Update Cardsets\" name =\"action\" />";
    echo"</td></tr></table></form>";
    
    echo "<h4>Extra Cardsets Allowed</h4>\n";
    echo "<form action=\"formatcp.php\" method=\"post\">"; 
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input type=\"hidden\" name=\"format\" value=\"{$active_format->name}\" />";
    echo "<input type=\"hidden\" name=\"series\" value=\"{$seriesName}\" />";
    echo "<table class=\"form\" style=\"border-width: 0px;\" align=\"center\">"; 
    echo "<tr><th style=\"text-align: center;\">Cardset Name</th><th style=\"width: 50px; text-align: center;\">Delete</th></tr>";
    if (count($extraCardSets)) {
        foreach($extraCardSets as $setName) {
            echo "<tr><td style=\"text-align: center;\">{$setName}</td>";
            echo "<td style=\"text-align: center; width: 50px;\"><input type=\"checkbox\" name=\"delcardsetname[]\" value=\"{$setName}\" />";
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td><font color=\"red\">No Extra Sets are Allowed</font></td>";
        echo "<td style=\"width: 100px; text-align: center;\">";
        not_allowed("No Selected Card Set To Delete");            
        echo "</td>";
        echo "</tr>";
    }
    echo "<tr><td>";
    cardsetDropMenu("Extra", $active_format, false);
    echo "</td>";
    echo "<td colspan=\"2\" class=\"buttons\">";
    echo "<input type=\"hidden\" name=\"view\" value=\"cardsets\" />";
    echo "<input class=\"inputbutton\" type=\"submit\" value=\"Update Cardsets\" name =\"action\" />";
    echo"</td></tr></table></form></center>";
}

function cardsetDropMenu($cardsetType, $format, $disabled) {
    if ($disabled) {
        echo "<select disabled=\"disabled\" class=\"inputbox\" name=\"cardsetname\" STYLE=\"width: 250px\">";
        echo "<option value=\"Unclassified\">- {$cardsetType} Cardset Name -</option>";
        echo "</select>\n";
        return;
    }
    $finalList = getMissingSets($cardsetType, $format);
    if ($finalList) {
      echo "<select class=\"inputbox\" name=\"cardsetname\" STYLE=\"width: 250px\">\n";      
      echo "<option value=\"Unclassified\">- {$cardsetType} Cardset Name -</option>\n";
      foreach ($finalList as $setName) {
        echo "<option value=\"$setName\">$setName</option>\n";
      }
    }
    else {
      echo "<select disabled=\"disabled\" class=\"inputbox\" name=\"cardsetname\" STYLE=\"width: 250px\">";
      echo "<option value=\"Unclassified\">- All {$cardsetType} sets have been added -</option>";
      echo "</select>\n";  
    }
    echo "</select>\n";
    if (count($finalList) > 2){
      echo "<input class=\"inputbutton\" type=\"submit\" value=\"Add All {$cardsetType}\" name =\"action\" />";
    }
  }

function getMissingSets($cardsetType, $format) {
    $cardsets = Database::list_result_single_param("SELECT name FROM cardsets WHERE type = ?", "s", $cardsetType);
    
    $legalsets = array();
    if (strcmp($cardsetType, "Core") == 0) {$legalsets = $format->getCoreCardsets();}
    if (strcmp($cardsetType, "Block") == 0) {$legalsets = $format->getBlockCardsets();}
    if (strcmp($cardsetType, "Extra") == 0) {$legalsets = $format->getExtraCardsets();}
    
    $finalList = array();
    foreach ($cardsets as $cardsetName) {
        
        if (!$format->isCardSetLegal($cardsetName)) {
            $finalList[] = $cardsetName;
        }
    }
    return $finalList;
}