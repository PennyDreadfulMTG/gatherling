<?php
class Decksearch {
    
  public $errors = array();
  
  private $_results = array(); 
  private $_final_results=array(); 
  
  private $_eventname;
  private $_playername;
  private $_deckid; 

// holds the current deck id that info is being collected for
  
  
/**
 * Deck Search, Gatherling Deck Search Class
 * 
 * This class allows you to create a deck search query using a varing 
 * amout of inputs
 * 
 * example usage:
 * 
 * <code>
 * $decksearch = new Decksearch();
 * $decksearch->searchByColor($color_array);
 * $decksearch->searchByFormat($formatname);
 * $results = $decksearch->getFinalResults(); 
 * </code>
 * 
 * will return an array of deck id's matching the set search terms
 * 
 * 
 * 
 * @version 1.0
 * @package Decksearch
 * @category Deck
 * 
 */
  function __construct() { 
      
   
  }
  
  /**
   * call getFinalResults to complete a search request with any set inputs 
   * and returns a list of deck id's that match. 
   * 
   * @return array List of id's that match the search request
   */
  public function getFinalResults() {      
    if (count($this->_results) > 0 && count($this->errors) == 0) {
        $array_keys = array_keys($this->_results);
        $first_key = array_shift($array_keys);
        $tmp_results = $this->_results[$first_key];
        foreach ($this->_results as $key => $value) {  
            $tmp_results = array_intersect($tmp_results,$this->_results[$key]);
        }
        // check if there was matches, if not set error and return
        if(count($tmp_results) != 0) {        
            // filter decks in events that are current active
            // Only decks that has a field in entries will be filtered
            // will allow for creation and searching of decks without entries
            foreach ($tmp_results as $value) {
            // check if there is a record in entries
                $sql = "select Count(*) FROM entries where deck = ?";
                $result = Database::single_result_single_param($sql,'d',$value);
                if($result) { 
                    $sql = "SELECT d.id FROM decks d, entries e, events t WHERE d.id = ? AND d.id = e.deck AND e.event = t.name AND t.finalized = 1";
                    $arr_tmp = Database::single_result_single_param($sql,'d',$value);
                    if(!empty($arr_tmp)) {   
                        array_push($this->_final_results, $arr_tmp);
                    } 
                } else {
                    array_push($this->_final_results, $value);
                }                 
            } 
            return $this->_final_results;
        } else {
            $this->errors[] = "<center><br>Your search query did not have any matches";
            return false;
        }
    } else {
        return false;
    } 
  }
  
  /**
   * Add search by format to the search query and sets $_results array with matching deck ids
   * 
   * @param string $format Format to search decks by
   */
  public function searchByFormat($format) {
     $sql = "SELECT id FROM decks WHERE format = ?"; 
     $results = Database::list_result_single_param($sql,'s', $format);
     if (count($results) > 0) {
         $this->_results['format'] = $results;
     } else {
         $this->errors[] = "<center><br>No decks match the format: $format";    
    }
  }
  
  /**
   * Add search by players to the search query sets $_results array
   *  
   * @param string $player Player name to search decks by
   */
  public function searchByPlayer($player) {
     $sql = "SELECT id FROM decks WHERE playername LIKE ?"; 
     $results = Database::list_result_single_param($sql,'s', '%'.$player.'%');
     if (count($results) > 0) {
         $this->_results['player'] = $results;
     } else {
         $this->errors[] = "<center><br>No decks by the player like: <font color=red>$player</font></center>";    
    }
  }
  
  /**
   * Add search by medals to the search query and sets $_results array with matching deck ids
   *  
   * Input options: 1st 2nd t4 t8
   * 
   * @param string $medal Medal to search decks by
   */
  public function searchByMedals($medal) {
     $sql = "SELECT decks.id 
         FROM decks INNER JOIN entries 
         ON decks.id = entries.deck  
         WHERE entries.medal = ? 
         ORDER BY DATE(`created_date`) DESC";
     
     $results = Database::list_result_single_param($sql,'s', $medal);
     if (count($results) > 0) {
         $this->_results['medal'] = $results;
     } else {
         $this->errors[] = "<center><br>No decks found with the medal: <font color=red>$medal</font></center>";    
    }
  }
 
  /**
   *  Add search by colors to the search query sets $_results array with matching deck ids
   * 
   *  bcgruw u=Blue w=White b=Black r=Red g=Green c=Colorless
   *  e.g. array(u => 'u') order does not matter.
   * 
   * 
   *  @param [$color_str_input] Array of the input color
   *  @return mixed true if success/false otherwise
   */
    public function searchByColor($color_str_input) {
     // alphebetizes then sets the search string
     $final_color_str=null;
     ksort($color_str_input);
     foreach ($color_str_input as $value) {
        $final_color_str .= $value;
     }   
    
     $sql = "SELECT id FROM decks WHERE deck_colors = ?";
     $results = Database::list_result_single_param($sql,'s', $final_color_str);
     if (count($results) > 0) {
         $this->_results['color'] = $results;
     } else {
         $this->errors[] = "<center><br>No decks found matching the colors: <font color=red>$color_str_input</font></center>";    
    }
  }
  
  /**
   *  Add search by archetype to the search query and sets $_results array with matching deck ids
   * 
   * @param string $archetype Name of archetype to search for
   */
  public function searchByArchetype($archetype) {
    $sql = "SELECT id FROM decks WHERE archetype = ?";
    $results = Database::list_result_single_param($sql,'s', $archetype);
    if (count($results) > 0) {
         $this->_results['archetype'] = $results;
    } else {
         $this->errors[] = "<center><br>No decks found matching archetype: <font color=red>$archetype</font></center>";    
    }   
  }
  
  /**
   * Add search by series to the search query sets $_results array with matching deck ids
   * 
   * @param string $series Series name to search by
   * 
   */
  public function searchBySeries($series) {
    $sql = "SELECT entries.deck 
            FROM entries INNER JOIN events 
            ON entries.event = events.name 
            WHERE events.series = ? 
            AND entries.deck ORDER BY DATE(`registered_at`) DESC";
    
    $results = Database::list_result_single_param($sql,'s', $series);
    if (count($results) > 0) {
         $this->_results['series'] = $results;
    } else {
         $this->errors[] = "<center><br>No decks found matching series: <font color=red>$series</font></center>";    
    }  
  }
 
  /**
   *  Add search by card name to the search query and sets $_results array with matching deck ids
   * 
   * @param string $cardname Name of card to search for
   */
  public function searchByCardName($cardname) {
    if (strlen($cardname) >= 3 ) {
    $sql = "SELECT deckcontents.deck  
	FROM deckcontents INNER JOIN cards
		on deckcontents.card = cards.id
        WHERE cards.name LIKE ?";
    $results = Database::list_result_single_param($sql,'s', "%$cardname%");
    if (count($results) > 0) {
            //Remove Duplicate decks
             $results = array_unique($results);
         $this->_results['cardname'] = $results;
    } else {
         $this->errors[] = "<center><br>No decks found with the card name like: <font color=red>$cardname</font></center>";    
    }  
    } else {
        $this->errors[] = "<center><br>String length is too short must be <font color=red>3</font> characters or greater</center>";  
  }
  }

  public function idsToSortedInfo($id_arr) {
    // prepared statements would not work with mysql IN
    // needed a way to run the array through one query so I 
    // could sort properly
 
    global $CONFIG;
    $con = mysql_connect($CONFIG['db_hostname'],$CONFIG['db_username'],$CONFIG['db_password']) 
    or die('Could not connect to the server!');
  
    // select a database:
    mysql_select_db($CONFIG['db_database']) 
    or die('Could not select a database.');

    //sanitize the id_arr to protect against sql injection.
    $id_arr = array_filter(array_map('intval', $id_arr));

    $query = "SELECT id, archetype, name, playername, format, created_date from decks WHERE id IN (" . implode(",", $id_arr) . ") ORDER BY DATE(`created_date`) DESC"; 
    $result = mysql_query($query);
      
    $list = array();
    while ($row = mysql_fetch_assoc($result)) {      
        $row['record'] = $this->_getDeckRecord($row['id']);
      $list[] = $row;
}
    mysql_close();
    return $list;
  }

   private function _getDeckRecord($deckid) {
    // check if there is a record in entries     
    $sql = "select Count(*) FROM entries where deck = ?";
    $result = Database::single_result_single_param($sql,'d',$deckid);
    
    if($result) {
        $database = Database::getConnection(); 
        $stmt = $database->prepare("SELECT e.event, d.playername FROM decks d, entries e WHERE d.id = ? AND d.id = e.deck");
        $stmt->bind_param("d", $deckid);
        $stmt->execute();
        $stmt->bind_result($this->_eventname, $this->_playername);
        $stmt->fetch(); 
        $stmt->close();
       
        if (!empty($this->_eventname) && !empty($this->_playername)) {
            return $this->_recordString();
        } else {
             return "?-?";
}
    } else {
        return "?-?";
    }

  }
  
  private function _recordString() {
    $wins = 0;
    $losses = 0;
    $draws = 0;
    
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT m.id FROM matches m, subevents s WHERE m.subevent = s.id AND s.parent = ?
                            AND (m.playera = ? OR m.playerb = ?) ORDER BY s.timing, m.round");
    $stmt->bind_param("sss", $this->_eventname, $this->_playername, $this->_playername);
    $stmt->execute();
    $stmt->bind_result($matchid);

    $matchids = array();
    while ($stmt->fetch()) {
      $matchids[] = $matchid;
    }
    $stmt->close();

    $matches = array();
    foreach ($matchids as $matchid) {
      $matches[] = new Match($matchid);
    }
      
    foreach ($matches as $match) {
      if ($match->playerWon($this->_playername)) {
        $wins = $wins + 1;
      } else if ($match->playerLost($this->_playername)) {
        $losses = $losses + 1;
      } else {
        $draws = $draws + 1;
      }
    }
    
    if ($draws == 0) {
      return $wins . "-" . $losses;
    } else {
      return $wins . "-" . $losses . "-" . $draws;
    }
  }
}
