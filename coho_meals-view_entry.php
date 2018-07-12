<?php
// (c) Copyright 2002-2012 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: tiki-calendar.php 42203 2012-07-03 19:02:27Z jonnybradley $

$section = 'cohomeals';
require_once ('tiki-setup.php');
include_once ('lib/calendar/calendarlib.php');
include_once ('lib/cohomeals/coho_mealslib.php');
$access->check_feature('feature_calendar');
$access->check_feature('feature_cohomeals');

$access->check_permission('tiki_p_view_calendar');
$access->check_permission('tiki_p_view_meals');
// related permissions might be:
//  tiki_p_view_calendar
//  tiki_p_view_events
//  tiki_p_change_events
//  tiki_p_add_events
//  tiki_p_admin_calendar
/*
$calperms = Perms::get(array( 'type' => 'calendar', 'object' => 1 )); // meal program is cal id 1
// format: $calperms->view_calendar true/false
*/
$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;

$myurl = 'coho_meals-view_entry.php';

$mealtype = "regular";
if ( isset($_REQUEST["id"] )) {
  $mealid = $_REQUEST["id"];
} elseif ( isset($_REQUEST["recurrenceId"])) {
    $mealid = $_REQUEST["recurrenceId"];
    $mealtype = "recurring";
} else {
    $smarty->assign('errortype', 'Invalid entry id.');
    $smarty->display("error.tpl");
    die;
}

if ( empty ( $mealid ) || $mealid <= 0 || ! is_numeric ( $mealid ) ) {
  $smarty->assign('errortype', 'Invalid entry id.');
  $smarty->display("error.tpl");
  die;
} 

$smarty->assign('mealid', $mealid);

if ( isset($_REQUEST["mealdatetime"]) ) $mealdatetime = $_REQUEST["mealdatetime"];
else {
  $smarty->assign('errortype', 'Invalid meal date.');
  $smarty->display("error.tpl");
  die;
}

$cohomeals = new CohoMealsLib;

/// load meal info
$meal = array();
if ( !$cohomeals->load_meal_info($mealtype, $mealid, $meal) ) {
    $smarty->assign('errortype', 'Could not find meal.');
    $smarty->display("error.tpl");
    die;
}

$smarty->assign('mealmenu', $meal["menu"]);
$smarty->assign('mealnotes', $meal["notes"]);

$smarty->assign('mealdatetime', $mealdatetime );
$smarty->assign('mealcancelled', $meal["cancelled"]);
$signup_datetime = strtotime("-".$meal["signup_deadline"]." days",$mealdatetime);
$smarty->assign('signup_deadline', $signup_datetime);

$past_deadline = false;
if ( $signup_deadline < time() ) $past_deadline = true;
$can_signup = !$past_deadline || $is_meal_admin; 
$smarty->assign('past_deadline', $past_deadline);
$smarty->assign('can_signup', $can_signup);

$smarty->assign('adult_price', $cohomeals->price_to_str($cohomeals->get_adjusted_price($meal["base_price"], "A")));
$smarty->assign('kid_price', $cohomeals->price_to_str($cohomeals->get_adjusted_price($meal["base_price"], "K")));

if ( $mealtype == "recurring" ) $chefusername = $cohomeals->recurring_head_chef( $mealid );
else $chefusername = $cohomeals->has_head_chef( $mealid );
if ( $chefusername == "" ) {
  $smarty->assign('has_head_chef', '0');
  $smarty->assign('mealheadchef', 'No head chef');
}  
else {
  $smarty->assign('has_head_chef', '1');
  $smarty->assign('mealheadchef', $cohomeals->get_user_preference($chefusername, 'realName', $chefusername));
}

if ( $mealtype == "recurring" ) $crew = $cohomeals->load_recurring_crew($mealid);
else $crew = $cohomeals->load_crew($mealid);
$smarty->assign('crew', $crew);

/*
$diners = $cohomeals->load_diners($mealid);
$smarty->assign('diners', $diners);
//begin debug
echo "diners: <br>";
foreach( $diners as $diner ){
  echo $diner["username"] . ": " . $diner["realName"] . ", " . $diner["dining"] . "<br>";
}
// end debug
*/


$smarty->assign('mid', 'coho_meals-view_entry.tpl');
$smarty->display("tiki.tpl");



?>