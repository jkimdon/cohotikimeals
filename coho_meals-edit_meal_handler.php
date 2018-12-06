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

$mealId = $_REQUEST["id"]; 
if ( !($mealId > 0) ) {
    $smarty->assign('msg', 'Empty meal id.');
    $smarty->display("error.tpl");
    die;
}

if ( $cohomeals->is_working( $mealId, $user ) || $is_meal_admin ) {

    $updatearray = array();
    $conditionarray = ['cal_id'=>$mealId];
    
    $newtitle = $_REQUEST["newtitle"];
    if ( $newtitle == "" ) $newtitle = "Community Meal";
    $updatearray['meal_title'] = $newtitle;
    
    $newmonth = $_REQUEST["mealdate_Month"];
    $newday = $_REQUEST["mealdate_Day"];
    $newyear = $_REQUEST["mealdate_Year"];
    $tmptz = TikiDate::TimezoneIsValidId($prefs['server_timezone']) ? $prefs['server_timezone'] : 'US/Pacific';
    $tz = new DateTimeZone( $tmptz );
    $newdatetime = new DateTime( "now", $tz );
    $newdatetime->setDate( $newyear, $newmonth, $newday );
    $updatearray['cal_date'] = $newdatetime->format('Ymd');
    
    $newhour = $_REQUEST["mealtime_Hour"];
    $newminute = $_REQUEST["mealtime_Minute"];
    $newampm = $_REQUEST["mealtime_Meridian"];
    if ( $newampm == "pm" ) $newhour += 12;
    $newdatetime->setTime( $newhour, $newminute );
    $tmptime = str_pad( $newdatetime->format('Hi'), 6, "0", STR_PAD_RIGHT );
    $updatearray['cal_time'] = $tmptime;
    $mealdatetime = $newdatetime->format('U');

    
    if ( isset($_REQUEST["menu"] ) ) {
        $updatearray['cal_menu'] = $_REQUEST["menu"];
    }

    if ( isset($_REQUEST["notes"] ) ) {
        $updatearray['cal_notes'] = $_REQUEST["notes"];
    }

    $mealchange = $tikilib->table('cohomeals_meal');
    $mealchange->update( $updatearray, $conditionarray );

    
    $crew = $cohomeals->load_crew( $mealId );
    $maxnone = 0;
    $crewchange = $tikilib->table('cohomeals_meal_participant');
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
                    $crewchange->delete([ 'cal_id'=>$mealId, 'cal_login'=>$un, 'cal_type'=>'C', 'cal_notes'=>$oldjob]);
                } else {
                    if ( $newjob != $oldjob ) {
                        $crewchange->update( ['cal_notes'=>$newjob], ['cal_id'=>$mealId, 'cal_login'=>$un, 'cal_type'=>'C', 'cal_notes'=>$oldjob] );
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
                $crewchange->insert( ['cal_id'=>$mealId, 'cal_login'=>$newname, 'cal_type'=>'C', 'cal_notes'=>$newcrewjob] );
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