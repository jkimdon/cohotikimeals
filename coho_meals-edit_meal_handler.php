<?php

$section = 'cohomeals';
require_once ('tiki-setup.php');
include_once ('lib/calendar/calendarlib.php');
include_once ('lib/cohomeals/coho_mealslib.php');
$access->check_feature('feature_cohomeals');

$access->check_permission('tiki_p_view_meals'); 
$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;

$cohomeals = new CohoMealsLib;

if ( !isset($_REQUEST["id"]) || !isset($_REQUEST["newtitle"]) || !isset($_REQUEST["mealdate_Month"]) || !isset($_REQUEST["mealdate_Day"]) || !isset($_REQUEST["mealdate_Year"]) || !isset($_REQUEST["mealtime_Hour"]) || !isset($_REQUEST["mealtime_Minute"]) || !isset($_REQUEST["mealtime_Meridian"]) ) {
    $smarty->assign('msg', 'Incorrect parameters.');
    $smarty->display("error.tpl");
    die;
}

if ( $cohomeals->is_working( $user ) || $is_meal_admin ) {

    $mealId = $_REQUEST["id"]; 
    if ( !($mealId > 0) ) {
        $smarty->assign('msg', 'Empty meal id.');
        $smarty->display("error.tpl");
        die;
    }

    $mealquery = "UPDATE cohomeals_meal SET";
    $condition = " WHERE cal_id = " . $mealId;
    $newtitle = $_REQUEST["newtitle"];
    if ( $newtitle == "" ) $newtitle = "Community Meal";
    $mealquery .= " meal_title = '" . $newtitle . "'";
    
    $newmonth = $_REQUEST["mealdate_Month"];
    $newday = $_REQUEST["mealdate_Day"];
    $newyear = $_REQUEST["mealdate_Year"];
    $tmptz = TikiDate::TimezoneIsValidId($prefs['server_timezone']) ? $prefs['server_timezone'] : 'US/Pacific';
    $tz = new DateTimeZone( $tmptz );
    $newdatetime = new DateTime( "now", $tz );
    $newdatetime->setDate( $newyear, $newmonth, $newday );
    $mealquery .= ", cal_date = " . $newdatetime->format('Ymd');
    
    $newhour = $_REQUEST["mealtime_Hour"];
    $newminute = $_REQUEST["mealtime_Minute"];
    $newampm = $_REQUEST["mealtime_Meridian"];
    if ( $newampm == "pm" ) $newhour += 12;
    $newdatetime->setTime( $newhour, $newminute );
    $tmptime = str_pad( $newdatetime->format('Hi'), 6, "0", STR_PAD_RIGHT );
    $mealquery .= ", cal_time = " . $tmptime;
    $mealdatetime = $newdatetime->format('U');
    
    if ( isset($_REQUEST["menu"] ) ) $mealquery .= ", cal_menu = '" . $_REQUEST["menu"] . "'";

    if ( isset($_REQUEST["notes"] ) ) $mealquery .= ", cal_notes = '" . $_REQUEST["notes"] . "'";

    $mealquery .= $condition;
    $cohomeals->query($mealquery);
    
    $crew = $cohomeals->load_crew( $mealId );
    $maxnone = 0;
    foreach ( $crew as $cm ) {
        $un = $cm["username"];
        $oldjob = $cm["job"];
        $identifier = $un . "-" . str_replace(' ', '-', $oldjob);
        if ( preg_match( '/^none/', $cm["username"] ) ) {
            $newnone = trim( $cm["username"], "none" );
            if ( $newnone > $maxnone ) $maxnone = $newnone;
            if ( isset($_REQUEST["$identifier"]) ) {
                $newjob = $_REQUEST["$identifier"];
                if ( $newjob == "" ) {
                    $crewquery = "DELETE FROM cohomeals_meal_participant WHERE cal_id = $mealId AND cal_login = '$un' AND cal_type = 'C' AND cal_notes='$oldjob'";
                    $cohomeals->query($crewquery);
                } else {
                    if ( $newjob != $oldjob ) {
                        $crewquery = "UPDATE cohomeals_meal_participant SET cal_notes = '$newjob' WHERE cal_id = $mealId AND cal_login = '$un' AND cal_type = 'C' AND cal_notes = '$oldjob'";
                        $cohomeals->query($crewquery);
                    }
                }
            }
        }
    }
    if ( isset($_REQUEST["newcrew"]) ) {
        $newcrew = $_REQUEST["newcrew"];
        foreach( $newcrew as $newcrewjob ) {
            if ( $newcrewjob != "" ) {
                $maxnone++;
                $newname = "none" . $maxnone;
                $crewquery = "INSERT INTO cohomeals_meal_participant ( cal_id, cal_login, cal_type, cal_notes ) VALUES ( $mealId, '$newname', 'C', '$newcrewjob' )";
                $cohomeals->query($crewquery);
            }
        }
    }

    $nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
    header("Location: $nexturl");
    die;
}

$smarty->assign('msg', 'Not authorized.');
$smarty->display("error.tpl");
die;



?>