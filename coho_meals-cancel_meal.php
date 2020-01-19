<?php

$section = 'cohomeals';
require_once('tiki-setup.php');
include_once ('lib/cohomeals/coho_mealslib.php');

$access->check_permission('tiki_p_admin_meals');

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
if ( $is_meal_admin != true ) {
    $smarty->assign('msg', 'Do not have permission to delete this meal.');
    $page = 'tiki-index.php';
    $access->display_error($page, 'You do not have permission to delete this meal.', '401');
    die;
}
$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$smarty->assign('loggedinuser', $user);

$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign('is_meal_admin', $is_meal_admin);

$myurl = 'coho_meals-cancel_meal.php';

if ( isset($_REQUEST["id"] ) ) {
    $mealId = $_REQUEST["id"];
    $mealtype = 'regular';
} else {
    $recurrenceId = $_REQUEST["recurrenceId"];
    $mealtype = 'recurring';
}
if ( !($mealId > 0) && !($recurrenceId > 0) ) {
    $smarty->assign('msg', 'Empty meal id.');
    $smarty->display("error.tpl");
    die;
}

$mt = $_REQUEST["mealtype"];
if ( $mealtype != $mt ) {
    $smarty->assign('msg', 'Wrong meal type requested.');
    $smarty->display("error.tpl");
    die;
}
$mealdatetime = $_REQUEST["mealdatetime"];
$allmeals = 0;
if ( isset($_REQUEST["allmeals"] ) )
    $allmeals = $_REQUEST["allmeals"];


// cancel a one-time meal
if ( $mealtype == "regular" ) {

    // refund meal, delete entered expenses (paperwork)
    $cohomeals->refund_meal( $mealId );
    $cohomeals->delete_entered_expenses( $mealId );

    // cancel meal in database
    $mealTable = $cohomeals->table('cohomeals_meal');
    $mealTable->update( ['cal_cancelled'=>1, 'paperwork_done'=>0, 'diners_charged'=>0], ['cal_id'=>$mealId] );
    
} else { //recurring

    if ( $allmeals == 1 ) {
    
        // **** Note that the recurrence ID only refers to monthly recurrences, so
        // weekly recurrences actually have 5 entries, one per week. To delete a weekly
        // recurrence, the admins must make 5 separate cancellations.
        
        // we enter a cancel date of today
        $tz = new DateTimeZone ( 'US/Pacific' );
        $todaysdate = new DateTime( "now", $tz );
        $newdate = $todaysdate->format('U');
        
        $mealTable = $cohomeals->table('cohomeals_meal_recurrence');
        $mealTable->update( ['endPeriod'=>$newdate], ['recurrenceId'=>$recurrenceId] );

    } else { // this meal only

        // activate the meal
        $newMealId = $cohomeals->create_override_from_recurrence( $recurrenceId, $mealdatetime );
        
        // cancel the activated meal
        $mealTable = $cohomeals->table('cohomeals_meal');
        $mealTable->update( ['cal_cancelled'=>1, 'paperwork_done'=>0, 'diners_charged'=>0], ['cal_id'=>$newMealId] );

    }
}




$nexturl = "tiki-calendar.php";
header("Location: $nexturl");
die;

?>