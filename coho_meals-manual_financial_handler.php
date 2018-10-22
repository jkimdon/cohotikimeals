<?php

$section = 'cohomeals';
require_once ('tiki-setup.php');
include_once ('lib/calendar/calendarlib.php');
include_once ('lib/cohomeals/coho_mealslib.php');
$access->check_feature('feature_cohomeals');

$access->check_permission('tiki_p_finance_meals'); //fixme: get more appropriate permissions

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
$is_finance_admin = $mealperms->finance_meals;

if ( $is_meal_admin || $is_finance_admin ) {

    $cohomeals = new CohoMealsLib;

    if ( !isset($_REQUEST["addentry-billinggroup"]) || $_REQUEST["addentry-billinggroup"] <= 0 || !isset($_REQUEST["addentry-dollars"]) || !isset($_REQUEST["addentry-cents"]) || !isset($_REQUEST["addentry-type"]) || !isset($_REQUEST["addentry-description"]) ) {
        $smarty->assign('msg', 'Incorrect post variables in manual financial handler.');
        $smarty->display("error.tpl");
        die;
    }
    
    $billingGroup = $_REQUEST["addentry-billinggroup"];
    $dollars = $_REQUEST["addentry-dollars"];
    $cents = $_REQUEST["addentry-cents"];
    $type = $_REQUEST["addentry-type"];
    $description = "Manually added " . $type . ". ";
    $description .= $_REQUEST["addentry-description"];
    $mealId = 0;
    $notes = '';
    if ( isset($_REQUEST["addentry-mealId"]) ) $mealId = $_REQUEST["addentry-mealId"];
    if ($mealId == '') $mealId = 0;
    if ( isset($_REQUEST["addentry-notes"]) ) $notes = $_REQUEST["addentry-notes"];

    $amount = 100*$dollars + $cents;
    if ( $type == 'debit' ) $amount *= -1;
    else if ( $type != 'credit' ) {
        $smarty->assign('msg', 'Incorrect type (credit/debit).');
        $smarty->display("error.tpl");
        die;
    }
    $cohomeals->enter_finlog( $billingGroup, $amount, $description, $mealId, '', $notes ); // no user associated with this, just a billing group.
    
} else {
    $smarty->assign('msg', 'Not authorized.');
    $smarty->display("error.tpl");
    die;
}

$nexturl = "coho_meals-user_info.php#contentcoho_meals_user_preference-6";
header("Location: $nexturl");
die;


?>