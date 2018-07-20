<?php

$section = 'cohomeals';
require_once ('tiki-setup.php');
include_once ('lib/calendar/calendarlib.php');
include_once ('lib/cohomeals/coho_mealslib.php');
$access->check_feature('feature_calendar');
$access->check_feature('feature_cohomeals');

$access->check_permission('tiki_p_view_meals'); //fixme: get more appropriate permissions

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;

$myurl = 'coho_meals-edit_participation_handler.php';

$mealtype = $_REQUEST["mealtype"];
if ( ($mealtype != "regular") && ($mealtype != "recurring") ) {
    $smarty->assign('errortype', 'Invalid meal type.');
    $smarty->display("error.tpl");
    die;
}

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );

$mealId = $_REQUEST["id"];
$mealdatetime = $_REQUEST["mealdatetime"];
$mealtype = $_REQUEST["mealtype"];
$action = $_REQUEST["action"];
$participation_type = $_REQUEST["type"];
$people_read = $_REQUEST["people"]; 
$olduser = $_REQUEST["olduser"];
$who=array();
if (!is_array($people_read)) {
    $who[0] = $people_read;
    $people_url = "&people=" . $people_read;
}
else {
    $who = $people_read;
    $people_url = '';
    foreach( $who as $p ) {
        $people_url .= "&people[]=" . $p;
    }
}
$job = $_REQUEST["job"]; 

// if recurring, make a new overriding meal with same as recurring and then recall this handler on the new meal to make the changes
if ( $mealtype == "recurring" ) {
    $newMealId = $cohomeals->create_override_from_recurrence( $mealId, $mealdatetime );
    if (!$newMealId) {
        $smarty->assign('errortype', 'Error creating meal.');
        $smarty->display("error.tpl");
        die;
    }
    $joburl = "&job=" . preg_replace('/\s+/', '+', $job);
    header("Location: coho_meals-edit_participation_handler.php?id=" . $newMealId . "&mealdatetime=" . $mealdatetime . "&mealtype=regular&action=" . $action . "&type=" . $participation_type . "&olduser=" . $olduser . $peopleurl . $joburl);
    die;
}

// if already non-recurring meal,
if ( $mealtype == "regular" ) { 
    foreach( $who as $person ) { 
        if ( $cohomeals->is_signer( $person, $user ) ) {
            if ($action == 'D') {
                if ( ($participation_type == 'H') || ($participation_type == 'M') ) {
                    $sql = "DELETE FROM cohomeals_meal_participant " .
                        "WHERE cal_id = $mealId AND cal_login = '$person' AND cal_type = '$participation_type'";
                    $cohomeals->query($sql); 
                } else if ($participation_type == 'C') {
                    /// find last "none" login placeholder in participant table
                    $i=1;
                    $found_last = false;
                    while ( $found_last == false ) {
                        $none = "none" . $i;
                        $sql = "SELECT cal_login FROM cohomeals_meal_participant " .
                            "WHERE cal_id = $mealId AND cal_login = '$none' AND cal_type = 'C' AND cal_notes = '$job'";
                        if ( !$cohomeals->getOne($sql) ) $found_last = true;
                        else $i++;
                    }
                    $none = "none" . $i;
                    $sql = "UPDATE cohomeals_meal_participant " .
                        "SET cal_login = '$none' " .
                        "WHERE cal_id = $mealId AND cal_type = 'C' AND cal_login = '$person' AND cal_notes = '$job'"; 
                    if ( !$cohomeals->query($sql) ) {
                        $smarty->assign('errortype', 'Error adding person.');
                        $smarty->display("error.tpl");
                        die;
                    }
                }
            }
            elseif ($action == 'A') {
                if ( ($participation_type == 'H') || ($participation_type == 'M') ) {
                    $sql = "INSERT INTO cohomeals_meal_participant (cal_id, cal_login, cal_type) " .
                        "VALUES (" . $mealId . ", '" . $person . "', '" . $participation_type . "')";
                    if ( !$cohomeals->query($sql) ) {
                        $smarty->assign('errortype', 'Error adding person.');
                        $smarty->display("error.tpl");
                        die;
                    }
                }
                elseif ( $participation_type == 'C' ) {
                    $sql = "UPDATE cohomeals_meal_participant SET cal_login = '$person' " .
                        "WHERE cal_id = $mealId AND cal_type = 'C' AND cal_notes = '$job' AND cal_login = '$olduser'";
                    if ( !$cohomeals->query($sql) ) {
                        $smarty->assign('errortype', 'Error updating participation.');
                        $smarty->display("error.tpl");
                        die;
                    }
                }
            }
        }
    }
}
$nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
header("Location: $nexturl");
die;

?>