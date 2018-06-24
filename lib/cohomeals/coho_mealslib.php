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


  function has_head_chef( $mealid ) 
  {
    $head_chef = "";
    $query = "SELECT cal_login FROM coho_meals_meal_participant " .
      "WHERE cal_id = $mealid AND cal_type = 'H'";
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



  
  function load_crew( $mealid ) {
    $query = "SELECT cal_login, cal_notes " . 
      "FROM coho_meals_meal_participant " .
      "WHERE cal_id = $mealid AND cal_type = 'C' " .
      "ORDER BY cal_notes";
    $allrows = $this->fetchAll($query);
    
    $crew = array();
    $i=0;
    foreach ($allrows as $row) {
      $crew[$i] = array();
      $crew[$i]["username"] = $row['cal_login'];
      $crew[$i]["job"] = $row['cal_notes'];

      if ( ereg( "^none", $crew[$i]["username"] ) ) {
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
  


  function load_diners($mealid) 
  {
    $query = "SELECT cal_login, cal_type FROM coho_meals_meal_participant " .
      "WHERE cal_id = $mealid AND (cal_type = 'M' OR cal_type = 'T')";
    $allrows = $this->fetchAll($query);

    $ordering = array ();
    for ( $i=0; $i<11; $i++ ) {
      $ordering[$i] = array ();
    }
    
    $ret = array();
    $tmp_ret = array();
    $i=0;

    foreach( $allrows as $row ) {

      $cur_login = $row["cal_login"];

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
			     "dining" => $row["cal_type"],
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

  
  function load_meal_info($mealid, &$mealinfo)
  {
    $query = "SELECT `cal_date`, `cal_time`, `cal_suit`, `cal_menu`, " .
      "`cal_signup_deadline`, `cal_base_price`, " .
      "`cal_walkins`, `cal_notes`, `cal_max_diners`, `cal_cancelled` " .
      "FROM `coho_meals_meal` WHERE `cal_id` = $mealid";
    $vars = array($mealid);
    $res = $this->query($query,$vars);
    $info = $res->fetchRow();
   
    // reformat signup deadline
    $signup_deadline = $this->get_day( $info["cal_date"], -1*$info["cal_signup_deadline"]);

    $mealinfo["cal_date"] = $info["cal_date"];
    $mealinfo["cal_time"] = $info["cal_time"];
    $mealinfo["cal_suit"] = $info["cal_suit"];
    $mealinfo["cal_menu"] = $info["cal_menu"];
    $mealinfo["cal_signup_deadline"] = $signup_deadline;
    $mealinfo["cal_base_price"] = $info["cal_base_price"];
    $mealinfo["cal_walkins"] = $info["cal_walkins"];
    $mealinfo["cal_notes"] = $info["cal_notes"];
    $mealinfo["cal_max_diners"] = $info["cal_max_diners"];
    $mealinfo["cal_cancelled"] = $info["cal_cancelled"];
    $mealinfo["unix_datetime"] = $this->coho_datetime_to_unix($info["cal_date"], $info["cal_time"]);
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

  
  function get_adjusted_price($mealid, $fee_class, $known_walkin=false, $user_in_question="")
  {
    
    /// get meal details. establish base price, past_deadline
    $base_price = 400;
    $sql = "SELECT cal_base_price, cal_date, cal_signup_deadline " .
      "FROM coho_meals_meal " .
      "WHERE cal_id = $mealid";
    $res = $this->query($sql);
    $info = $res->fetchRow();
    
    $past_deadline = true;
    $signup_deadline = $this->get_day( $info["cal_date"], -1*$info["cal_signup_deadline"]);
    if ( $signup_deadline >= time() ) $past_deadline = false;
    
    $base_price = $info["cal_base_price"];
    
    
    /// establish price category based on preregistration or walkin
    $category = "pre";
    if ( $known_walkin == true ) $category = "walkin";
    else if ( $user_in_question != '' ) {
      if ( $this->is_walkin( $mealid, $user_in_question ) == 1 ) $category = "walkin";
    } else {
      if ( $past_deadline == true ) $category = "walkin";
    }
    
    
    /// calculate cost based on above information
    $cost = $base_price;
    if ( ($category == "walkin") && ($base_price != 0) ) $cost += 100;
    
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


}
$cohomealslib = new CohoMealsLib;



?>