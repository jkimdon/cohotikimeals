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

    // used in edit participation handler, edit_meal_summary
    function create_override_from_recurrence( $recurrenceId, $unixmealdate ) {

        $newmealid = $this->getOne("SELECT MAX(cal_id) AS maxid FROM cohomeals_meal") + 1;

        $sql = "SELECT which_day, which_week, meal_title, time, base_price, signup_deadline, menu " .
            "FROM cohomeals_meal_recurrence WHERE recurrenceId = $recurrenceId";
        $meal = $this->fetchAll($sql);
        if ( !$meal ) return false;
        $mealdate = $this->date_format("%Y%m%d", $unixmealdate);
        
        // make the override meal
        $sql = "INSERT INTO cohomeals_meal (cal_id, cal_date, cal_time, cal_signup_deadline, cal_base_price, " .
            "cal_max_diners, cal_menu, cal_notes, cal_cancelled, meal_title, recurrenceId, recurrence_override) " .
            "VALUES (";
        $sql .= $newmealid . ", " . $mealdate . ", " . $meal[0]["time"] . ", " . $meal[0]["signup_deadline"] . ", " .
            $meal[0]["base_price"] . ", 0, '" . $meal[0]["menu"] . "', '', 0, '" . $meal[0]["meal_title"] . "', " . $recurrenceId . ", true)";
        $result = $this->query($sql);

        ///////////
        // insert the recurring participants 

        // crew
        $crew = $this->load_recurring_crew($recurrenceId);
        foreach( $crew as $cr ) {
            $sql = "INSERT INTO cohomeals_meal_participant (cal_id, cal_login, cal_type, cal_notes ) VALUES ("
                . $newmealid . ", '" . $cr["username"] . "', 'C'" . ", '" . $cr['job'] . "')";
            if ( !$this->query($sql) ) {
                $smarty->assign('msg', 'Error adding recurring crew.');
                $smarty->display("error.tpl");
                die;
            }
        }
        
        // diners (no recurring guests)
        $diners = $this->load_diners($recurrenceId, "recurring");
        foreach( $diners as $diner ) {
            $sql = "INSERT INTO cohomeals_meal_participant (cal_id, cal_login, cal_type ) VALUES ("
                . $newmealid . ", '" . $diner["username"] . "', 'M'" . ")";
            if ( !$this->query($sql) ) {
                $smarty->assign('msg', 'Error adding recurring crew.');
                $smarty->display("error.tpl");
                die;
            }
        }
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
          else $mybuddy = $this->is_signer( $crew[$i]["username"], $this->loggedinuser );
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
            " WHERE recurringMealId = $mealid AND participant_type = 'M'";
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

  // used in meal summary
  function count_diners( $mealId, $use_multiplier=false ) { // only regular (not recurring) meals at this point
      if ( $mealId <= 0 ) return 0;
      
      if ( $use_multiplier == true ) {
          $numdiners = 0;
          $query = "SELECT cal_login FROM cohomeals_meal_participant " .
              "WHERE cal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $row ) {
              $multiplier = $this->get_multiplier( $row["cal_login"] );
              $numdiners += $multiplier;
          }
          
          $query = "SELECT cal_fullname, meal_multiplier FROM cohomeals_meal_guest " .
              "WHERE cal_meal_id = $mealId AND (cal_type = 'M' OR 'T')";
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $row ) {
              $numdiners += $row["meal_multiplier"];
          }
      }
      else { // just plain old head count
          $numdiners = 0;
          $query = "SELECT cal_login FROM cohomeals_meal_participant " .
              "WHERE cal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $row ) {
              $numdiners++;
          }

          $query = "SELECT cal_fullname FROM cohomeals_meal_guest " .
              "WHERE cal_meal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $row ) {
              $numdiners++;
          }
      } 

      return $numdiners;
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


  // used in load buddies and meal summary
  function getAllMealUsers() { // find the people actively in the meal program
      $myusers = new UsersLib;
      $groupnames = array();
      $groupnames[0] = "CoHo owners";
      $groupnames[1] = "on-site renters";
      $groupnames[2] = "Official Friend (active)";
      $groupnames[3] = "Associate Member (active)";
      $mealusers = $myusers->get_users( 0, -1, 'login_asc', '', '', true, $groupnames, '', false, false, false); 

      $ret = array();
      foreach ( $mealusers['data'] as $mealuser ) {
          if ( ($mealuser['user'] != 'admin') && ($mealuser['user'] != 'testassociatemember') && ($mealuser['user'] != 'testfriend') && ($mealuser['user'] != 'testrenter') ) {
              $realname = $this->get_user_preference($mealuser['user'], 'realName', $mealuser['user']);
              if ( $realname == "" ) $realname = $mealuser['user'];
              $ret[] = array( "username"=>$mealuser['user'], "realName"=>$realname );
          }
      }
      return $ret;
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
      $buddies = array();
      
      if ( $theuserisadmin ) {
          $buddies = $this->getAllMealUsers(); 
      } else {
          $sql = "SELECT cal_signee FROM cohomeals_buddy " .
              "WHERE cal_signer = '$theuser'";
          $allrows = $this->fetchAll($sql);
          foreach ($allrows as $buddy) {
              $realname = $this->get_user_preference($buddy["cal_signee"], 'realName', $buddy["cal_signee"]);
              if ( $realname == "" ) $realname = $buddy["cal_signee"];
              $buddies[] = array( "username" => $buddy["cal_signee"], "realName" => $realname );
          }          
          if ( $include_self == true ) {
              $realname = $this->get_user_preference($theuser, 'realName', $theuser);
              if ( $realname == "" ) $realname = $theuser;              
              $buddies[] = array( "username" => $theuser, "realName" => $realname );
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

  // used in a few times meal summary 
  function load_pantry_foods() {
      $foods = array();

      $sql = "SELECT cal_category, cal_food_id, cal_description, cal_unit, cal_unit_price " . 
          "FROM cohomeals_pantry_food WHERE cal_available_meals = 1 " .
          "ORDER BY cal_category, cal_description"; 
      $allfoods = $this->fetchAll($sql);
      foreach( $allfoods as $food ) { 
          $foods[] = array("category"=>$food["cal_category"], "name"=>$food["cal_description"], "id"=>$food["cal_food_id"], "unit"=>$food["cal_unit"], "unitcost"=>$food["cal_unit_price"]);
      } 
      return $foods;
  }

  // used in meal summary
  function get_pantry_purchases( &$pantry_details, $mealId, $pantry_omit=[] ) {
      if ( $mealId <= 0 ) {
          $pantry_details = '';
          return 0;
      }

      $omit_ids = array();
      foreach ( $pantry_omit as $omit ) {
          $query = "SELECT cal_food_id FROM cohomeals_pantry_food WHERE cal_description = '$omit'";
          $allomit = $this->fetchAll( $query );
          foreach( $allomit as $oneomit ) {
              $omit_ids[] = $oneomit["cal_food_id"];
          }
      }
      
      $pricesum = 0;
      $pantry_details = array();
      $query = "SELECT cal_total_price, cal_number_units, cal_food_id FROM cohomeals_pantry_purchases " .
          "WHERE cal_meal_id = $mealId AND cal_food_id NOT IN ('" . implode( "', '", $omit_ids ) . "')";
      $allrows = $this->fetchAll( $query );
      foreach( $allrows as $row ) {
          $pricesum += $row["cal_total_price"];

          $query2 = "SELECT cal_unit, cal_description FROM cohomeals_pantry_food " .
              "WHERE cal_food_id = " . $row["cal_food_id"];
          $food = $this->getOne( $query2 );
          if( $food ) {
              $pantry_details[] = array( "food"=>$food["cal_description"], "cost"=>$row["cal_total_price"]/100.00, "numunits"=>$row["cal_number_units"], "units"=>$food["cal_unit"] );
          }
      }
      return $pricesum;
  }

  
  // is this used?
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


  // used in meal view entry and edit_meal_summary
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
      
          $query = "SELECT cal_walkins, cal_signup_deadline, cal_date, cal_time, " .
              "cal_base_price, cal_max_diners, cal_menu, cal_notes, cal_cancelled, meal_title";
          $query .= " FROM cohomeals_meal WHERE cal_id = " . $mealid;
          $res = $this->query($query);
          if ( $info = $res->fetchRow() ) {
              if ( $info["meal_title"] == "" ) $mealinfo["title"] = "Community meal";
              else $mealinfo["title"] = $info["meal_title"];
              $mealinfo["menu"] = $info["cal_menu"];
              $mealinfo["signup_deadline"] = $info["cal_signup_deadline"];
              $mealinfo["base_price"] = $info["cal_base_price"]; 
              $mealinfo["walkins"] = $info["cal_walkins"];
              $mealinfo["notes"] = $info["cal_notes"];
              $mealinfo["max_diners"] = $info["cal_max_diners"];
              $mealinfo["cancelled"] = $info["cal_cancelled"];
              $mealinfo["mealdatetime"] = $this->coho_datetime_to_unix( $info["cal_date"], $info["cal_time"] );
          } else return false;
      }
      return true;
  }


  // used in meal summary
  function get_food_cost_for_meal( $mealId, $food_description ) {
      if ( $mealId <= 0 ) return 0;
      if ( !isset( $food_description ) ) return 0;
      
      $costsum = 0;

      $sql = "SELECT cal_food_id FROM cohomeals_pantry_food WHERE cal_description = '$food_description'";
      $allfood = $this->fetchAll( $sql );
      foreach( $allfood as $food ) {
          $foodId = $food["cal_food_id"];
          $sql2 = "SELECT cal_total_price FROM cohomeals_pantry_purchases " .
              "WHERE cal_meal_id = $mealId AND cal_food_id = $foodId";
          $allcost = $this->fetchAll( $sql2 );
          foreach( $allcost as $cost ) {
              $costsum += $cost["cal_total_price"];
          }
      }
      return $costsum;
  }

  // used in meal summary
  function get_multiplier( $diner ) {
      $multiplier = $this->get_user_preference( $diner, 'meal_multiplier', 1.0 );
      if ( (!is_numeric($multiplier)) || ($multiplier<0) || ($multiplier>99) ) $multiplier = 1.0;
      return $multiplier;
  }

  // used (or should be) all over the place
  function get_fullname( $username ) {
      $realname = '';
      $realname = $this->get_user_preference($username, 'realName', $username);
      if ($realname == '' ) $realname = $username;
      return $realname;
  }
  
  // used in meal summary
  function person_cost( $mealId, $diner ) { // only non-recurring for now
      $mealinfo = array();
      $this->load_meal_info( "regular", $mealId, $mealinfo ); 
      $cost = $mealinfo["base_price"] * $this->get_multiplier( $diner );
      $cost /= 100.00;
      return $cost;
  }

  // gives income in *cents* to avoid rounding errors.
  //    transfers to dollars should be done by caller if desired
  // used in meal summary
  // and in charge_person to make sure not double-charged.
  //     - if billingGroup == 0, then do for all billing groups (i.e. in meal summary)
  function diner_income( $mealId, $paperwork_done=true, $billingGroup=0 ) { // only non-recurring meals
      $income = 0;

      $query = "SELECT cal_base_price FROM cohomeals_meal WHERE cal_id = $mealId";
      $base_price = $this->getOne( $query );
      
      if ( $paperwork_done == false ) { // calculate from participant tables
          // meal plan participants
          $query2 = "SELECT cal_login FROM cohomeals_meal_participant " .
              "WHERE cal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
          $allrows = $this->fetchAll($query2);
          foreach( $allrows as $diner ) { 
              if ( $billingGroup != 0 ) { 
                  if ( $billingGroup != $this->get_billingId( $diner["cal_login"] ) )
                      continue;
              } 
              $income += ( $this->get_multiplier( $diner["cal_login"] ) * $base_price );
          }
          
          // guests
          $query = "SELECT meal_multiplier, cal_host FROM cohomeals_meal_guest " .
              "WHERE cal_meal_id = $mealId AND cal_type = 'M'";
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $row ) { 
              if ( $billingGroup != 0 ) {
                  if ( $billingGroup != $this->get_billingId( $row["cal_host"] ) ) 
                      break;
              } 
              $income += ( $row["meal_multiplier"] * $base_price );
          }
      } else { // paperwork is done, so get info from financial log

          $query = "SELECT cal_amount FROM cohomeals_financial_log WHERE cal_meal_id = $mealId";
          if ($billingGroup != 0) {
              $query .= " AND cal_billing_group = $billingGroup";
          }
          $allrows = $this->fetchAll($query);
          foreach( $allrows as $amt ) {
              $income += $amt["cal_amount"];
          }
          $income *= -1;
      }
      return $income;
  }

  
  // used in meal summary
  function paperwork_done( $mealId ) {
      $isdone = false;
      $sql = "SELECT paperwork_done FROM cohomeals_meal WHERE cal_id=$mealId";
      if ($this->getOne($sql) != 0 ) $isdone = true;
      return $isdone;
  }

  // used in charge_for_meal and view entry
  function is_charged( $mealId ) {
      $charged = false;
      $query = "SELECT diners_charged FROM cohomeals_meal WHERE cal_id = $mealId";
      $charged = $this->getOne( $query ); 
      return $charged;
  }
  
  // used in meal summary and cron and charge_meal
  function charge_for_meal( $mealId, $chargeoverride=false ) { // non-recurring only. should have been changed to non-recurring before this

      // check to see if already charged
      $charged = $this->is_charged( $mealId );
      if ($chargeoverride == true ) $charged = false; 
      if ( $charged != false ) {
          return false;
      }

      // do the charging. meal plan participants and guests
      $query = "SELECT cal_base_price FROM cohomeals_meal WHERE cal_id = $mealId";
      $base_price = $this->getOne( $query );

      // meal plan participants
      $query = "SELECT cal_login FROM cohomeals_meal_participant " .
          "WHERE cal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
      $allrows = $this->fetchAll($query);
      foreach( $allrows as $diner ) { 
          $multiplier = $this->get_multiplier( $diner["cal_login"] );
          $tmpamount = -1*$multiplier * $base_price;
          $amount = floor($tmpamount);
          $bg = $this->get_billingId( $diner["cal_login"] );
          $realname = $this->get_user_preference($diner["cal_login"], 'realName', $diner["cal_login"]);
          if ($realname == '' ) $realname = $diner["cal_login"];
          $description = $realname . " dining. (multiplier = " . $multiplier . ")";
          $this->charge_person( $bg, $amount, $description, $mealId, $realname, $diner["cal_login"] );
      }

      // guests
      $query = "SELECT meal_multiplier, cal_host, cal_fullname FROM cohomeals_meal_guest " .
          "WHERE cal_meal_id = $mealId AND (cal_type = 'M' OR cal_type = 'T')";
      $allrows = $this->fetchAll($query);
      foreach( $allrows as $guest ) {
          $bg = $this->get_billingId( $guest["cal_host"] );
          $tmpamount = -1*$guest["meal_multiplier"] * $base_price;
          $amount = floor($tmpamount);
          $description = "Guest " . $guest["cal_fullname"] . " dining (multiplier = " . $guest["meal_multiplier"] . ")";
          $this->charge_person( $bg, $amount, $description, $mealId, $guest["cal_fullname"], $guest["cal_host"] );
      }

      // now double-check to be sure charges are as expected
      //
      // go through each billing group
      $query = "SELECT cal_billing_group, cal_login FROM cohomeals_financial_log WHERE cal_meal_id = $mealId ORDER BY cal_billing_group";
      $allrows = $this->fetchAll($query);
      $prev_bg = 0;
      foreach( $allrows as $bg ) {
          $billingGroup = $bg["cal_billing_group"];
          if ( $billingGroup != $prev_bg ) {
              $prev_bg = $billingGroup;
              $expected_charges = -1*$this->diner_income( $mealId, false, $billingGroup );
              $actual_charges = -1*$this->diner_income( $mealId, true, $billingGroup );
              $diff = $expected_charges - $actual_charges;
              if ( $expected_charges != $actual_charges ) { 
                  $this->enter_finlog( $billingGroup, $diff, "Fixing up charges for this meal", $mealId, $bg["cal_login"] );
              }
          }
      }

      // set the charged flag
      $query = "UPDATE cohomeals_meal SET diners_charged=1 WHERE cal_id = $mealId";
      if ( !$this->query( $query ) ) {
          $smarty->assign('msg', 'Error refunding meal.');
          $smarty->display("error.tpl");
          die;
      }
  }


  // used in charge for meal and refund meal
  //   checks if the person has already been specifically charged. doesn't check for billing-group-wide charges or refunds. that should be done separately
  function charge_person( $billingGroup, $amt, $description, $mealId, $fullname, $userId ) {

      $amount = floor($amt);
      // previous charges should have full name in log description
      $query = "SELECT cal_amount FROM cohomeals_financial_log WHERE cal_meal_id = $mealId AND cal_billing_group = $billingGroup AND cal_description LIKE '%" . $fullname . "%'"; 
      $allwithlogin = $this->fetchAll($query);
      $precharged = 0;
      foreach( $allwithlogin as $preamt ) {
          $precharged += $preamt["cal_amount"];
      }
      if ( $precharged != $amount ) {
          $this->enter_finlog( $billingGroup, $amount-$precharged, $description, $mealId, $userId );
      }
  }

  
  // used by charge_person, charge_for_meal (also expect to use in refund and credit functions)
  //
  // WARNING: does not check for anything except running balance. other checking should be done before calling this.
  function enter_finlog( $billingGroup, $amt, $description, $mealId, $userId ) {

      if ( ($billingGroup <= 0) || (!is_numeric($billingGroup)) )
          $billingGroup = $this->get_billingId( $userId ); 
      if ( ($billingGroup == false) || (!is_numeric($billingGroup)) || ($billingGroup <=0) ) {
          $billingGroup = $this->make_new_billingGroup( $userId );
      }

      $balance = 0;
      $last_balance = 0;
      $last_time = 0;
      $amount = floor($amt);
      
      $sql = "SELECT cal_amount, cal_running_balance, cal_timestamp, cal_meal_id " .
          "FROM cohomeals_financial_log " . 
          "WHERE cal_billing_group = '$billingGroup' ".
          "ORDER BY cal_log_id";
      $allrows = $this->fetchAll( $sql );
      foreach( $allrows as $row ) {
          $balance += $row['cal_amount'];
          $last_balance = $row['cal_running_balance'];
          $last_time = $row['cal_timestamp'];
          if ( $row['cal_meal_id'] == $mealId ) {
              $mealamount += $row['cal_amount'];
          }
      }

      if ( $last_balance != $balance ) {
          $errormsg = "mismatched balance for billing group $billingGroup: " . 
              "at time $last_time, balance = $last_balance; " .
              "balance sum = $balance<br>";
          echo $errormsg;
//          die; // don't die since then it messes everything else up. ideally it would send admin an email... (fixme)
      }

      // enter the log
      if ( $amount != 0 ) { // insert the charge
          $sql = "SELECT MAX(cal_log_id) FROM cohomeals_financial_log";
          $maxid = $this->getOne( $sql );
          $id = $maxid + 1;
          $sql = "INSERT INTO cohomeals_financial_log " .
              "( cal_log_id, cal_login, cal_billing_group, cal_description, " .
              "cal_meal_id, cal_amount, cal_running_balance ) " . 
              "VALUES ( $id, '$userId', $billingGroup, '$description', $mealId, $amount, ";
          $balance += $amount;
          $sql .= $balance . ")";
          $this->query( $sql );
      }
      return true;
  }

  // used by meal admin only at this point
  // find and replace all diner charges for meal (but don't remove the peoples' names from the signup)
  // regular meals only since to have been charged, they should have been transformed into regular
  function refund_meal( $mealId ) {
      if ( !$this->is_meal_admin ) {
          return false;
      }

      // have to deal with varying methods of previous refunds, including by-person and by-billing-group.
      // 
      // find all the financial entries for this meal 
      //     and refund by user (below will double-check for each billing group)
      // (should cover regular meal participants and guests since login is host)
      $query = "SELECT cal_login, cal_billing_group, cal_amount, cal_description " .
          "FROM cohomeals_financial_log WHERE cal_meal_id = $mealId ORDER BY cal_login";
      $allrows = $this->fetchAll( $query );
      foreach( $allrows as $row ) {
          $amt = -1*$row["cal_amount"]; // change to credit
          $msg = "Refund for: " . $row["cal_description"];
          $this->enter_finlog( $row["cal_billing_group"], $amt, $msg, $mealId, $row["cal_login"] );
      }

      // now double-check to be sure charges are all removed
      //
      // go through each billing group
      $query = "SELECT cal_billing_group, cal_login FROM cohomeals_financial_log WHERE cal_meal_id = $mealId ORDER BY cal_billing_group";
      $allrows = $this->fetchAll($query);
      $prev_bg = 0;
      foreach( $allrows as $bg ) {
          $billingGroup = $bg["cal_billing_group"];
          if ( $billingGroup != $prev_bg ) {
              $prev_bg = $billingGroup;
              $actual_income = $this->diner_income( $mealId, true, $billingGroup );
              if ( $actual_income != 0 ) {
                  $this->enter_finlog( $billingGroup, -1*$actual_income, "Fixing up refunds for this meal", $mealId, $bg["cal_login"] );
              }
          }
      }
      
      // reset the charged flag
      $query = "UPDATE cohomeals_meal SET diners_charged=NULL WHERE cal_id = $mealId";
      if ( !$this->query( $query ) ) {
          $smarty->assign('msg', 'Error refunding meal.');
          $smarty->display("error.tpl");
          die;
      }
      return $mealdatetime;
  }


  // used as option in refund_meal
  // doesn't remove any walkins nor refund any diners
  function delete_entered_expenses( $mealId ) {
      if ( !$this->is_meal_admin ) {
          return false;
      }
      // shoppers
      $query = "DELETE FROM cohomeals_food_expenditures WHERE cal_meal_id = $mealId";
      if ( !$this->query( $query ) ) {
          $smarty->assign('msg', 'Error removing shoppers.');
          $smarty->display("error.tpl");
          die;
      }

      // pantry, including farmers market and flat rate
      $query = "DELETE FROM cohomeals_pantry_purchases WHERE cal_meal_id = $mealId";
      if (!$this->query( $query ) ) {
          $smarty->assign('msg', 'Error removing pantry items.');
          $smarty->display("error.tpl");
          die;
      }

      // reset paperwork flag
      $query = "UPDATE cohomeals_meal SET paperwork_done = NULL WHERE cal_id = $mealId";
      if (!$this->query( $query ) ) {
          $smarty->assign('msg', 'Error setting paperwork flag.');
          $smarty->display("error.tpl");
          die;
      }
  }


  
  // used in charge_meal
  // regular meals only  
  function get_mealdatetime( $mealId ) { 
      if ( !is_numeric( $mealId ) || $mealId <= 0 ) {
          return false;
      }
      $mealinfo = array();
      $query = "SELECT cal_date, cal_time FROM cohomeals_meal WHERE cal_id = $mealId";
      $res = $this->query( $query );
      if ( $info = $res->fetchRow() ) 
          return $this->coho_datetime_to_unix( $info["cal_date"], $info["cal_time"] );
      else return 0;
  }

  // used in viewing financial logs
  // regular meals only
  function get_mealtitle( $mealId ) {
      $query = "SELECT meal_title FROM cohomeals_meal WHERE cal_id = $mealId";
      if ( $title = $this->getOne( $query ) )
          return $title;
      else return "Community Meal";
  }
  
  
  // used several places
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

    $unixtime = TikiLib::make_time($HH,$MinMin,0,$MM,$DD,$YYYY);

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

  // i think this can  be eliminated
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
  
  // i think this can be eliminated  
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

  // used in admin financial view in coho_meals-user_info.php
  function get_billingGroups( &$billingArray ) {
      if ( !$this->is_meal_admin ) return NULL;
      $billingArray[0] = 'All';
      $query = "SELECT billingGroupId, billingGroupName FROM cohomeals_billing_groups ORDER BY billingGroupName";
      $allrows = $this->fetchAll($query);
      foreach ( $allrows as $row ) {
          $id = $row['billingGroupId'];
          $billingArray[$id] = $row['billingGroupName'];
      }
      return true;
  }
  
  // used in charge meals
  function make_new_billingGroup( $userId ) {

      // make sure they don't already have a billing group
      $query = "SELECT value FROM tiki_user_preferences WHERE prefName = 'billingGroup' AND user = '$userId'";
      $bgid = $this->getOne( $query );
      if ( (is_numeric($bgid)) && ($bgid>0) ) { // already has a billing group
          return $bgid;
      }

      // check to see if they have a name instead of a number
      $hadname = false;
      $query = "SELECT cal_billing_group FROM cohomeals_financial_log WHERE cal_login = '$userId'";
      $bganswer = $this->getOne( $query );
      if ( is_numeric($bganswer) ) {
          // insert preference, and
          $this->query("INSERT INTO tiki_user_preferences (user, prefName, value) VALUES ('$userId', 'billingGroup', $bganswer)");
          return $bganswer;
      } elseif ( !$bgname ) {
          $bgname = $userId . "12345"; // make one up
      } else {
          $hadname = true;
      }
                                                          
      // ok now we have a name, let's get a number
      $newid = $this->getOne( "SELECT MAX(billingGroupId) FROM cohomeals_billing_groups" );
      
      // insert name into billing group table,
      $this->query("INSERT INTO cohomeals_billing_groups (billingGroupId, billingGroupName) VALUES ($newid, '$bgname')");
      
      // insert preference, and
      $this->query("INSERT INTO tiki_user_preferences (user, prefName, value) VALUES ('$userId', 'billingGroup', $newid)");

      // change in financial log
      if ( $hadname ) {
          $this->query("UPDATE cohomeals_financial_log SET cal_billing_group=$newid WHERE cal_billing_group='$bgname'");
      }
      return $newid;
  }

}
//$cohomealslib = new CohoMealsLib;



?>