<?php 

class Database {

  static public function getConnection() { 
    static $instance;

    if (!isset($instance)) { 
      global $CONFIG; 
      $instance = new mysqli($CONFIG['db_hostname'], $CONFIG['db_username'],
                             $CONFIG['db_password'], $CONFIG['db_database']);
        if (mysqli_connect_errno()) {
            echo(mysqli_connect_error());
            die('failed to connect to db');
        }
    } 

    return $instance;
  } 

  static public function getPDOConnection() {
    static $pdo_instance;

    if (!isset($pdo_instance)) {
      global $CONFIG;
      $pdo_instance = new PDO('mysql:hostname=' . $CONFIG['db_hostname'] . ';dbname=' . $CONFIG['db_database'],
                              $CONFIG['db_username'], $CONFIG['db_password']);
    }

    return $pdo_instance;
  }

  static public function single_result($sql) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($result);
    $stmt->fetch();
    $stmt->close();
    return $result;
  }

  // Does PHP have an arguments[] property that would allow processing of any number of parameters?
  // could I just make $paramType and $param arrays that would allow a single function to handle any number
  // of parameters? Going to have to play with this. 
  static public function single_result_single_param($sql, $paramType, $param) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bind_param($paramType, $param);
    $stmt->execute();
    $stmt->bind_result($result);
    $stmt->fetch();
    $stmt->close();
    return $result;
  }

  static public function single_result_double_param($sql, $paramTypes, $param1, $param2) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bind_param($paramTypes, $param1, $param2);
    $stmt->execute();
    $stmt->bind_result($result);
    $stmt->fetch();
    $stmt->close();
    return $result;
  }

  static public function list_result($sql) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($result);

    $list = array();
    while ($stmt->fetch()) {
      $list[] = $result;
    }
    $stmt->close();

    return $list;
  }

  static public function list_result_single_param($sql, $paramType, $param) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bind_param($paramType, $param);
    $stmt->execute();
    $stmt->bind_result($result);

    $list = array();
    while ($stmt->fetch()) {
      $list[] = $result;
    }
    $stmt->close();

    return $list;
  }
  
  static public function list_result_double_param($sql, $paramTypes, $param1, $param2) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bind_param($paramTypes, $param1, $param2);
    $stmt->execute();
    $stmt->bind_result($result);

    $list = array();
    while ($stmt->fetch()) {
      $list[] = $result;
    }
    $stmt->close();

    return $list;
  }

  static public function no_result_single_param($sql, $paramType, $param) {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bind_param($paramType, $param);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
  }
  
  static public function db_query() {
      $params = func_get_args();
      $query = array_shift($params);
      $paramspec = array_shift($params);
      
      $db = Database::getConnection();
      $stmt = $db->prepare($query);
      $stmt or die($db->error);
      
      if (count($params) == 1) {
          list($one) = $params;
          $stmt->bind_param($paramspec, $one);
      } else if (count($params) == 2) {
          list($one, $two) = $params;
          $stmt->bind_param($paramspec, $one, $two);
      } else if (count($params) == 3) {
          list($one, $two, $three) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three);
      } else if (count($params) == 4) {
          list($one, $two, $three, $four) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four);
      } else if (count($params) == 5) {
          list($one, $two, $three, $four, $five) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five);
      } else if (count($params) == 6) {
          list($one, $two, $three, $four, $five, $six) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six);
      } else if (count($params) == 7) {
          list($one, $two, $three, $four, $five, $six, $seven) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven);
      } else if (count($params) == 8) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight);
      } else if (count($params) == 9) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
      } else if (count($params) == 10) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten);
      }
      $stmt->execute() or die($stmt->error);
      $stmt->close();
      return true;
  }

  static public function db_query_single() {
      $params = func_get_args();      
      $query = array_shift($params);
      $paramspec = array_shift($params);
 
      $db = Database::getConnection();
      $stmt = $db->prepare($query);
      $stmt or die($db->error);
  
      if (count($params) == 1) {
          list($one) = $params;
          $stmt->bind_param($paramspec, $one);
      } else if (count($params) == 2) {
          list($one, $two) = $params;
          $stmt->bind_param($paramspec, $one, $two);
      } else if (count($params) == 3) {
          list($one, $two, $three) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three);
      } else if (count($params) == 4) {
          list($one, $two, $three, $four) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four);
      } else if (count($params) == 5) {
          list($one, $two, $three, $four, $five) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five);
      } else if (count($params) == 6) {
          list($one, $two, $three, $four, $five, $six) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six);
      } else if (count($params) == 7) {
          list($one, $two, $three, $four, $five, $six, $seven) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven);
      } else if (count($params) == 8) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight);
      } else if (count($params) == 9) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine);
      } else if (count($params) == 10) {
          list($one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten) = $params;
          $stmt->bind_param($paramspec, $one, $two, $three, $four, $five, $six, $seven, $eight, $nine, $ten);
      }
      $stmt->execute() or die($stmt->error);
      $stmt->bind_result($result);
      $stmt->fetch();
      $stmt->close();
      return $result;
  }  
}
