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
      } else {
	$crew[$i]["fullname"] = $this->get_user_preference($crew[$i]["username"], 'realName', $crew[$i]["username"]);
	$crew[$i]["has_volunteer"] = 1;
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
  // type "T" take-home plate is no longer an option, but we leave it here to
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
      if ($unit == 0) $building = 10;
      else {
	$temp = (int)($unit / 10);
	$temp2 = (int)($temp / 10);
	$building = $temp - 10*$temp2;
      }
      $ordering[$building][$i] = $i;

      $tmp_ret[$i++] = array(
			     "username" => $cur_login,
			     "realName" => $this->get_user_preference($cur_login, 'realName', $cur_login),
			     "dining" => "M", // no more take-home plate
			     "building" => $building
			     );
      $tmp = $i-1;
      echo $tmp_ret[$tmp]["realName"] . ": " . $tmp_ret[$tmp]["building"] . " = " . $unit . "<br>";//debug
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

}
$cohomealslib = new CohoMealsLib;



?>