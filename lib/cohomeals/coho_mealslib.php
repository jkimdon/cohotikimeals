<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: calendarlib.php 39969 2012-02-27 19:00:08Z sylvieg $

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}


class CohoMealsLib extends TikiLib
{
    protected $loggedinuser = "";
    protected $is_meal_admin = false;

    function set_meal_admin( $amI ) {
        if ( $amI == true ) $this->is_meal_admin = true;
        else $this->is_meal_admin = false;
    }

    function set_user( $usertoset="" ) {
        if ( isset($usertoset) )
            $this->loggedinuser = (string)filter_var( $usertoset, FILTER_SANITIZE_STRING );
    }

    // used in edit participation handler
    function create_override_from_recurrence( $recurrenceId, $unixmealdate ) {

        $newmealid = $this->getOne("SELECT MAX(cal_id) AS maxid FROM cohomeals_meal") + 1;

        $sql = "SELECT which_day, which_week, meal_title, time, base_price, signup_deadline, menu " .
            "FROM cohomeals_meal_recurrence WHERE recurrenceId = $recurrenceId";
        $meal = $this->fetchAll($sql);
        if ( !$meal ) return false;
        $mealdate = $this->date_format("%Y%m%d", $unixmealdate);
        
        $sql = "INSERT INTO cohomeals_meal (cal_id, cal_date, cal_time, cal_signup_deadline, cal_base_price, " .
            "cal_max_diners, cal_menu, cal_notes, cal_cancelled, meal_title, recurrenceId, recurrence_override) " .
            "VALUES (";
        $sql .= $newmealid . ", " . $mealdate . ", " . $meal[0]["time"] . ", " . $meal[0]["signup_deadline"] . ", " .
            $meal[0]["base_price"] . ", 0, '" . $meal[0]["menu"] . "', '', 0, '" . $meal[0]["meal_title"] . "', " . $recurrenceId . ", true)";
        $result = $this->query($sql);
        
        if ($result) return $newmealid;
        else return false;
    }

    
  // used in add_coho_meal_items in calendarlib 
  function has_head_chef( $mealid ) 
  {
    $head_chef = "";
    $query = "SELECT cal_login FROM cohomeals_meal_participant " .
      "WHERE cal_id = $mealid AND cal_type = 'H'";
    $haschef = $this->getOne($query);
    
    return $haschef;
  }


  // used in add_coho_meal_items in calendarlib 
  function recurring_head_chef( $recurrenceId ) 
  {
    $head_chef = "";
    $query = "SELECT userId FROM cohomeals_participant_recurrence " .
      "WHERE recurringMealId = " . $recurrenceId . " AND participant_type = 'H'";
    $haschef = $this->getOne($query);
    
    return $haschef;
  }



  

  /// fixme debug: i think I can delete this
  function print_crew( $mealid, $use_longnames = true )
  {
    $query = "SELECT cal_login, cal_notes " . 
      "FROM coho_meals_meal_participant " . 
      "WHERE cal_id = $mealid AND cal_type = 'C'";
    $crew = array();
    $allrows = $this->fetchAll($query);
    $i = 0;
    foreach ($allrows as $row) {
      $crew_user = $row['cal_login'];
      $crew_job = $row['cal_notes'];
      $crew['username'][$i] = $crew_user;

      if ( ereg( "^none", $crew_user ) ) {
	$crew['name'][$i] = "<font color=\"#DD0000\">STILL NEEDED</font>";
	$name_length = 12;
      } else {
	$crew['name'][$i] = $this->get_user_preference($crew_user, 'realName', '??');
	$name_length = strlen( $crew['name'][$i] );
      }

      $job_length = strlen( $crew_job );
      if ( $use_longnames == false ) {
	$available_length = 25 - $name_length;
      } else $available_length = $job_length;
      
      if ( $job_length > $available_length ) {
	$crew['job'][$i] = substr_replace( $crew_job, "...", $available_length, $job_length );
      } else {
	$crew['job'][$i] = $crew_job;
      }
      $i++;
    }

    // keep the different jobs together
    if ( count( $crew['job'] ) > 0 ) 
      array_multisort( $crew['job'], $crew['name'], $crew['username'] );
    
    return $crew;
  }



  // used in add_coho_meal_items  
  function load_crew( $mealid ) {
    $query = "SELECT cal_login, cal_notes " . 
      "FROM cohomeals_meal_participant " .
      "WHERE cal_id = $mealid AND cal_type = 'C' " .
      "ORDER BY cal_notes";
    $allrows = $this->fetchAll($query);
    
    $crew = array();
    $i=0;
    foreach ($allrows as $row) {
      $crew[$i] = array();
      $crew[$i]["username"] = $row['cal_login'];
      $crew[$i]["job"] = $row['cal_notes'];

      if ( preg_match( '/^none/', $crew[$i]["username"] ) ) {
          $crew[$i]["fullname"] = "<font color=\"#DD0000\">STILL NEEDED</font>";
          $crew[$i]["has_volunteer"] = 0;
          $crew[$i]["mybuddy"] = false;
      } else {
          $crew[$i]["fullname"] = $this->get_user_preference($crew[$i]["username"], 'realName', $crew[$i]["username"]);
          $crew[$i]["has_volunteer"] = 1;
          $mybuddy = false;
          if ( $this->is_meal_admin ) $mybuddy = true;
          else if ( !isset($this->loggedinuser) ) $mybuddy = false;
          else $mybuddy = $this->is_signer( $cur_login, $this->loggedinuser );
          $crew[$i]["mybuddy"] = $mybuddy;
      }
      $i++;
    }

    return $crew;
  }

  // used in add_coho_meal_items  
  function load_recurring_crew( $recurrenceId ) {
    $query = "SELECT userId, crew_description " . 
      "FROM cohomeals_participant_recurrence " .
      "WHERE recurringMealId = $recurrenceId AND participant_type = 'C' " .
      "ORDER BY crew_description";
    $allrows = $this->fetchAll($query);
    
    $crew = array();
    $i=0;
    foreach ($allrows as $row) {
      $crew[$i] = array();
      $crew[$i]["username"] = $row['userId'];
      $crew[$i]["job"] = $row['crew_description'];

      if ( preg_match( '/^none/', $crew[$i]["username"] ) ) {
	$crew[$i]["fullname"] = "<font color=\"#DD0000\">STILL NEEDED</font>";
	$crew[$i]["has_volunteer"] = 0;
      } else {
	$crew[$i]["fullname"] = $this->get_user_preference($crew[$i]["username"], 'realName', $crew[$i]["username"]);
	$crew[$i]["has_volunteer"] = 1;
	}
      $i++;
    }

    return $crew;
  }
  

  // used in coho_meals-view_entry.php
  // type "T" take-home plate is no longer an option, but we leave it here (read only) to
  //    maintain compatibility with previously-created meals
  function load_diners($mealid, $mealtype) 
  {
    if ($mealtype == "recurring") {
        $query = "SELECT userId FROM cohomeals_participant_recurrence" .
            " WHERE userId = $mealid AND participant_type = 'M'";
    } else {
        $query = "SELECT cal_login FROM cohomeals_meal_participant " .
            "WHERE cal_id = $mealid AND (cal_type = 'M' OR cal_type = 'T')";
    }
    $allrows = $this->fetchAll($query);

    $ordering = array ();
    for ( $i=0; $i<11; $i++ ) {
      $ordering[$i] = array ();
    }
    
    $ret = array();
    $tmp_ret = array();
    $i=0;

    foreach( $allrows as $row ) {

      if ($mealtype == "recurring") $cur_login = $row["userId"];
      else $cur_login = $row["cal_login"];

      // get building number from unit number
      $unit = $this->get_user_preference($cur_login, 'unitNumber', '0');
      if (!$unit || $unit == 0) $building = 10;
      else {
          $temp = (int)($unit / 10);
          $temp2 = (int)($temp / 10);
          $building = $temp - 10*$temp2;
      }
      $ordering[$building][$i] = $i;

      $mybuddy = false;
      if ( !isset($this->loggedinuser) ) $mybuddy = false;
      else $mybuddy = $this->is_signer( $cur_login, $this->loggedinuser );
      if ( $this->is_meal_admin ) $mybuddy = true;
      
      $tmp_ret[$i++] = array(
			     "username" => $cur_login,
			     "realName" => $this->get_user_preference($cur_login, 'realName', $cur_login),
			     "dining" => "M", // no more take-home plate
                 "mybuddy" => $mybuddy, // shows if user can sign up this person cur_login
			     "building" => $building
			     );
      $tmp = $i-1;
    }


    /// re-order by building
    $newcount = 0;
    for ( $i=1; $i<11; $i++ ) {
      sort( $ordering[$i] );
      $b = $ordering[$i];
      foreach ( $b as $key => $value ) {
          $ret[$newcount++] = $tmp_ret[$value];
      }
    }
    
    return $ret;
    
  }

  // used in load_food_restrictions_by_meal
  function is_dining( $username, $mealid ) {
  
      $dining = false;

      if ($mealid != 0) {
          $sql = "SELECT cal_type " .
              "FROM cohomeals_meal_participant" . 
              " WHERE cal_id = $mealid AND cal_login = '$username'" .
              " AND (cal_type = 'M' OR cal_type = 'T')"; // check T for backwards compatibility
          if ( $this->getOne( $sql ) ) $dining = true;
      }
      return $dining;
  }


  // used in load buddies
  function getAllMealUsers() { // find the people actively in the meal program
      $myusers = new UsersLib;
      $groupnames = array();
      $groupnames[0] = "CoHo owners";
      $groupnames[1] = "on-site renters";
      $groupnames[2] = "Official Friend (active)";
      $groupnames[3] = "Associate Member (active)";
      $mealusers = $myusers->get_users( 0, -1, 'login_asc', '', '', true, $groupnames, '', false, false, false); 
      return $mealusers;
  }

  // not used yet
  function isOnMealPlan( $checklogin ) { // debugging something else. fix this after that works
      return true;
  }
  
  
  /////////////////////////////////
  // buddy functions

  function is_signer( $signee, $potential_signer ) {
      $ret = false;

      if ( $signee == $potential_signer ) {
          $ret = true; 
      }
      if ( $this->is_meal_admin ) {
          $ret = true;
      }
      
      $sql = "SELECT cal_signer FROM cohomeals_buddy " .
          "WHERE cal_signee = '$signee' AND cal_signer = '$potential_signer'";
      if ( $res = $this->getOne( $sql ) ) {
          $ret = true; 
      }
      return $ret;
  }

  // used in meal details page
  function load_buddies_signees( $theuser, $theuserisadmin, $include_self=false ) {
      $ret = array ();
      $i = 0;
      $buddies = array();
      
      if ( $theuserisadmin ) {
          $buddyinfo = $this->getAllMealUsers(); 
          foreach ( $buddyinfo['data'] as $buddy ) { 
              $realname = $this->get_user_preference($buddy['user'], 'realName', $buddy['user']);
              if ( $realname == "" ) $realname = $buddy['user'];
              $buddies[$i++] = array( "username" => $buddy['user'], "realName" => $realname );
          }
      } else {
          $sql = "SELECT cal_signee FROM webcal_buddy " .
              "WHERE cal_signer = '$theuser'";
          $allrows = $this->fetchAll($sql);
          foreach ($allrows as $buddy) {
              $realname = $this->get_user_preference($buddy["cal_signee"], 'realName', $buddy["cal_signee"]);
              if ( $realname == "" ) $realname = $buddy["cal_signee"];
              $buddies[$i++] = array( "username" => $buddy["cal_signee"], "realName" => $realname );
          }          
          if ( $include_self == true ) {
              $realname = $this->get_user_preference($theuser, 'realName', $theuser);
              if ( $realname == "" ) $realname = $theuser;              
              $buddies[$i++] = array( "username" => $theuser, "realName" => $realname );
          }
      }
      return $buddies;
  }
  


  //
  /////////////////////////////////

  // used in meal detail page
  function load_guests( $mealid, $participation_type, $mealtype="regular" ) { // only works for regular meals at this point
      $guests = array();

      if ($mealtype != "regular") {
          return false;
      }
      
      $sql = "SELECT cal_fullname, cal_host, meal_multiplier " .
          "FROM cohomeals_meal_guest " .
          "WHERE cal_meal_id = $mealid AND cal_type = '$participation_type'"; 
      $allrows = $this->fetchAll($sql);
      foreach( $allrows as $row ) { 
          $hostusername = $row["cal_host"];
          $hostrealname = $this->get_user_preference($hostusername, 'realName', $hostusername);
          if ( $hostrealname == "" ) $hostrealname = $hostusername;
          $guests[] = array( "realName" => $row["cal_fullname"], "hostusername" => $hostusername, "hostrealname"=>$hostrealname, "meal_multiplier"=>$row["meal_multiplier"]);
      }
      return $guests;
  }
  
  
  // used in meal detail page
  function load_food_restrictions_by_meal($mealid=0) {

      if ($mealid==0) return false;
      
      $foodrestrictions = array();
      
      /// common restrictions listed first
      $listed_first = array();
      $listed_first[0] = "dairy";
      $listed_first[1] = "wheat/gluten";
      $listed_first[2] = "spicy";
      $listed_first[3] = "bell peppers";

      $i = 0;
      foreach( $listed_first as $food) {
          $sql = "SELECT cal_login, cal_comments FROM cohomeals_food_prefs " .
              "WHERE cal_food LIKE '$food'";
          $allrows = $this->fetchAll($sql);
          foreach( $allrows as $eater ) {
              $eaterlogin = $eater["cal_login"];
              if ( $this->is_dining( $eaterlogin, $mealid ) ) {
                  $realname = $this->get_user_preference($eaterlogin, 'realName', $eaterlogin);
                  if ( $realname == "" ) $realname = $eaterlogin;
                  $foodrestrictions[] = array( "user" => $eaterlogin, "realName" => $realname, "food" => $food, "comments" => $eater["cal_comments"]);
              }
          }
      } 

      ///  now list all the other restrictions
      $sql = "SELECT cal_food, cal_login, cal_comments FROM cohomeals_food_prefs" .
          " WHERE cal_food NOT IN ('" . implode( "', '", $listed_first ) . "') ORDER BY cal_food";
      $allrows = $this->fetchAll($sql);
      foreach( $allrows as $eater ) {
          $eaterlogin = $eater["cal_login"];
          if ( $this->is_dining( $eaterlogin, $mealid ) ) {
              $realname = $this->get_user_preference($eaterlogin, 'realName', $eaterlogin);
              if ( $realname == "" ) $realname = $eaterlogin; 
              $foodrestrictions[] = array( "user" => $eaterlogin, "realName" => $realname, "food" => $eater["cal_food"], "comments" => $eater["cal_comments"]);
          }
      }
      
      return $foodrestrictions;
  }
  

  // output unix timestamp
  function get_day( $ref_date_YYYYMMDD, $num_days ) 
  {
    $YYYY = substr ( $ref_date_YYYYMMDD, 0, 4 );
    $MM = substr ( $ref_date_YYYYMMDD, 4, 2 );
    $DD = substr ( $ref_date_YYYYMMDD, 6, 2 );
    //    $newdate = date( "Ymd", mktime( 3,0,0, $sm, $sd+$num_days, $sy ) ); // relic from different output

    $newdate = TikiLib::make_time(0,0,0,$MM,$DD+$num_days,$YYYY);
    return $newdate;
  }


  function get_eat_work_status($mealid, $user_login) 
  {
    $query = "SELECT `cal_type` FROM `coho_meals_meal_participant` " .
      "WHERE cal_id = $mealid AND cal_login = '$user_login' ";
    $ret = $this->fetchAll($query);

    $eat = false;
    $work = false;
    foreach($ret as $res) {
      if ($res['cal_type'] == 'M') $eat = true; // dining
      if ($res['cal_type'] == 'F') $eat = true; // take-home plate
      if ($res['cal_type'] == 'H') $work = true; // head chef
      if ($res['cal_type'] == 'C') $work = true; // crew
    }

    $eatwork = "";
    if ($work == true) $eatwork .= "W";
    if ($eat == true) $eatwork .= "E";

    return $eatwork;
  }


  // used in meal view entry
  function load_meal_info($mealtype, $mealid, &$mealinfo)
  {
      if ($mealtype == 'recurring') {
          $query = "SELECT signup_deadline, base_price, menu, meal_title";
          $query .= " FROM cohomeals_meal_recurrence WHERE recurrenceId=" . $mealid;
          $res = $this->query($query);
          if ( $info = $res->fetchRow() ) {
              if ( $info["meal_title"] == "" ) $mealinfo["title"] = "Community meal";
              else $mealinfo["title"] = $info["meal_title"];
              $mealinfo["menu"] = $info["menu"];
              $mealinfo["signup_deadline"] = $info["signup_deadline"];
              $mealinfo["base_price"] = $info["base_price"];
              $mealinfo["walkins"] = 'C';
              $mealinfo["notes"] = "";
              $mealinfo["max_diners"] = 0;
              $mealinfo["cancelled"] = 0;
          } else return false;

      } else {
      
          $query = "SELECT cal_walkins, cal_signup_deadline, " .
              "cal_base_price, cal_max_diners, cal_menu, cal_notes, cal_cancelled, meal_title";
          $query .= " FROM cohomeals_meal WHERE cal_id = " . $mealid;
          $res = $this->query($query);
          if ( $info = $res->fetchRow() ) {
              $mealinfo["title"] = $info["meal_title"];
              $mealinfo["menu"] = $info["cal_menu"];
              $mealinfo["signup_deadline"] = $info["cal_signup_deadline"];
              $mealinfo["base_price"] = $info["cal_base_price"];
              $mealinfo["walkins"] = $info["cal_walkins"];
              $mealinfo["notes"] = $info["cal_notes"];
              $mealinfo["max_diners"] = $info["cal_max_diners"];
              $mealinfo["cancelled"] = $info["cal_cancelled"];
          } else return false;
      }
      return true;
  }


  function coho_datetime_to_unix($YYYYMMDD, $HHMM)
  {
    $length = strlen($HHMM); 
    if ( ($length==3) || ($length==5) ) // in case someone enters $HMMSS or $HMM
      $tmp = str_pad($HHMM,$length+1,"0",STR_PAD_LEFT);
    else $tmp = $HHMM;
    $tmp = str_pad($tmp,6,"0",STR_PAD_RIGHT);
    $HH = substr($tmp,0,2);
    $MinMin = substr($tmp,2,2);

    $YYYY = substr($YYYYMMDD, 0, 4);
    $MM = substr($YYYYMMDD, 4, 2);
    $DD = substr($YYYYMMDD, 6, 2);

    $unixtime = TikiLib::make_time($HH,$MinMin,$SS,$MM,$DD,$YYYY);
    return $unixtime;
  }

  function coho_time_to_unix($unixdate, $HHMM)
  {
    $length = strlen($HHMM); 
    if ( ($length==3) || ($length==5) ) // in case someone enters $HMMSS or $HMM
      $tmp = str_pad($HHMM,$length+1,"0",STR_PAD_LEFT);
    else $tmp = $HHMM;
    $tmp = str_pad($tmp,6,"0",STR_PAD_RIGHT);
    $HH = substr($tmp,0,2);
    $MinMin = substr($tmp,2,2);

    $YYYY = date('Y', $unixdate);
    $MM = date('m', $unixdate);
    $DD = date('d', $unixdate);

    $unixtime = TikiLib::make_time($HH,$MinMin,$SS,$MM,$DD,$YYYY);
    return $unixtime;
  }




  function price_to_str($price) 
  {
    $sign = "";
    if ( $price < 0 ) {
      $price *= -1.0;
      $sign = "-";
    }
    $dollars = (int)($price / 100);
    $cents = $price - ($dollars*100);
    $ret = sprintf( "%s\$%d.%02d", $sign, $dollars, $cents );
    return $ret;
  }

  // used in coho_meals-view_entry.php (walkin fees have been eliminated)
  function get_adjusted_price($base_price, $fee_class)
  {
    
    /// calculate cost based on above information
    $cost = $base_price;
    
    switch ( $fee_class ) {
    case "F":
      $cost = 0;
      break;
    case "Q":
      $cost /= 4;
      break;
    case "K":
      $cost /= 2;
      break;
    case "T":
      $cost *= 3;
      $cost /= 4;
      break;
    }
    
    return $cost;
  }
  
  
  function is_walkin($mealid, $user_in_question) 
  {
    $ret = false;
    
    $sql = "SELECT cal_walkin " . 
      "FROM coho_meals_meal_participant " .
      "WHERE cal_id = $mealid AND cal_login = '$user_in_question'";
    $res = $this->query($sql);
    $info = $res->fetchRow();
    
    if ( $info["cal_walkin"] == 1 ) {
      $ret = true;
    }
    
    return $ret;
  }

  // used in user preferences view
  function get_billing_group_name( $bgnumber ) {
      $bgname = NULL;

      $sql = "SELECT billingGroupName FROM cohomeals_billing_groups" .
          " WHERE billingGroupId = $bgnumber";
      $res = $this->query($sql);
      $info = $res->fetchRow();
      if ( $info["billingGroupName"] ) {
          $bgname = $info["billingGroupName"];
      }
      
      return $bgname;
  }

  // used in coho_meals-user_info
  function get_billingId( $theuser ) {
      $bgId = $this->get_user_preference( $theuser, 'billingGroup' );
      if ($bgId > 0) return $bgId;
      else return false;
  }

  
}

//$cohomealslib = new CohoMealsLib;



?>