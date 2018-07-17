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

$myurl = 'coho_meals-signup_guest.php';

$mealtype = $_REQUEST["mealtype"];
if ( ($mealtype != "regular") && ($mealtype != "recurring") ) {
    $smarty->assign('errortype', 'Invalid meal type.');
    $smarty->display("error.tpl");
    die;
}

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );

if (!isset($_REQUEST["id"]) || !isset($_REQUEST["unixmealdate"]) || !isset($_REQUEST["action"]) || !isset($_REQUEST["type"]) || !isset($_REQUEST["guestName"]) || !isset($_REQUEST["host"]) ) {
    $smarty->assign('errortype', 'Inappropriate variables.');
    $smarty->display("error.tpl");
    die;
}

$mealId = $_REQUEST["id"];
$unixmealdate = $_REQUEST["unixmealdate"];
$mealtype = $_REQUEST["mealtype"];
$action = $_REQUEST["action"];
$participation_type = $_REQUEST["type"];
$read_guestname = $_REQUEST["guestName"]; 
$guestname = preg_replace( '/\++/', ' ', $read_guestname); 
$olduser = $_REQUEST["olduser"]; 
$job = $_REQUEST["job"];
$host = $_REQUEST["host"];
if (isset($_REQUEST["meal_multiplier"])) $meal_multiplier = $_REQUEST["meal_multiplier"];
else $meal_multiplier = 1;

// if recurring, make a new overriding meal with same as recurring and then recall this handler on the new meal to make the changes
if ( $mealtype == "recurring" ) {
    $newMealId = $cohomeals->create_override_from_recurrence( $mealId, $unixmealdate );
    if (!$newMealId) {
        $smarty->assign('errortype', 'Error creating meal.');
        $smarty->display("error.tpl");
        die;
        }
    $stripped_guestname = preg_replace('/\s+/', '+', $guestname);
    header("Location: coho_meals-signup_guest.php?$guestName=" . $stripped_guestname . "&id=" . $newMealId . "&unixmealdate=" . $unixmealdate . "&mealtype=regular&action=" . $action . "&type=" . $participation_type . "&host=" . $host . "&olduser=" . $olduer . "&job=" . $job);
    die;
}

// if already non-recurring meal
if ( $mealtype == "regular" ) {

    if ($action == 'A') {
        if ( $participation_type == 'M' ) { // only dining is functional. if you want a guest to crew, just write it in the job description
            $sql = "INSERT INTO cohomeals_meal_guest (cal_meal_id, cal_fullname, cal_host, meal_multiplier, cal_type) " .
                "VALUES ($mealId, '$guestname', '$host', $meal_multiplier, 'M')";
            if ( !$cohomeals->query($sql) ) {
                $smarty->assign('errortype', 'Error adding guest.');
                $smarty->display("error.tpl");
                die;
            }
        }
    } elseif ($action == 'D') {
        $sql = "DELETE FROM cohomeals_meal_guest WHERE cal_meal_id=$mealId AND cal_fullname='$guestname' AND cal_host='$host' AND cal_type ='M'";
        if ( !$cohomeals->query($sql) ) {
            $smarty->assign('errortype', 'Error removing guest.');
            $smarty->display("error.tpl");
            die;
        }
    }
}

$nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $unixmealdate;
header("Location: $nexturl");
die;

?>