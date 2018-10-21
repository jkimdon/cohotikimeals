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
  $mealId = $_REQUEST["id"];
} elseif ( isset($_REQUEST["recurrenceId"])) {
    $mealId = $_REQUEST["recurrenceId"];
    $mealtype = "recurring";
} else {
    $smarty->assign('msg', 'Invalid entry id.');
    $smarty->display("error.tpl");
    die;
}
$smarty->assign('mealtype', $mealtype);

if ( empty ( $mealId ) || $mealId <= 0 || ! is_numeric ( $mealId ) ) {
  $smarty->assign('msg', 'Invalid entry id.');
  $smarty->display("error.tpl");
  die;
} 

$smarty->assign('mealId', $mealId);

if ( isset($_REQUEST["mealdatetime"]) ) $mealdatetime = $_REQUEST["mealdatetime"];
else {
  $smarty->assign('msg', 'Invalid meal date.');
  $smarty->display("error.tpl");
  die;
}

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign('is_meal_admin', $is_meal_admin);

$smarty->assign('loggedinuser', $user);

/// load meal info
$meal = array();
if ( !$cohomeals->load_meal_info($mealtype, $mealId, $meal) ) {
    $smarty->assign('msg', 'Could not find meal.');
    $smarty->display("error.tpl");
    die;
}

$smarty->assign('mealmenu', $meal["menu"]);
$smarty->assign('mealnotes', $meal["notes"]);

$smarty->assign('mealdatetime', $mealdatetime );
$smarty->assign('mealcancelled', $meal["cancelled"]);
$tmpsignupdatetime = strtotime("-".$meal["signup_deadline"]." days",$mealdatetime);
$deadline = new DateTime();
$deadline->setTimestamp( $tmpsignupdatetime );
$tz = TikiDate::TimezoneIsValidId($prefs['server_timezone']) ? $prefs['server_timezone'] : 'US/Pacific';
$deadline->setTimezone( new DateTimeZone( $tz ) );
$deadline->setTime( 23, 59 );
$signupdatetime = $deadline->format('U');
$smarty->assign('signup_deadline', $signupdatetime );

$past_deadline = false;
if ( $signupdatetime < time() ) $past_deadline = true; 
$can_signup = !$past_deadline || $is_meal_admin; 
if ( $mealtype == "recurring") {
    $paperwork_done = false;
    $is_charged = false;
} else {
    $paperwork_done = $cohomeals->paperwork_done( $mealId );
    $is_charged = $cohomeals->is_charged( $mealId );
}
if ( $paperwork_done ) $can_signup = false;
if ( $is_charged ) $can_signup = false;
$smarty->assign('past_deadline', $past_deadline);
$smarty->assign('can_signup', $can_signup);

$smarty->assign('adult_price', $cohomeals->price_to_str($cohomeals->get_adjusted_price($meal["base_price"], "A")));
$smarty->assign('kid_price', $cohomeals->price_to_str($cohomeals->get_adjusted_price($meal["base_price"], "K")));

if ( $mealtype == "recurring" ) $chefusername = $cohomeals->recurring_head_chef( $mealId );
else $chefusername = $cohomeals->has_head_chef( $mealId );
if ( $chefusername == "" ) {
  $smarty->assign('has_head_chef', '0');
  $smarty->assign('mealheadchef', 'No head chef');
}  
else {
  $smarty->assign('has_head_chef', '1');
  $mealheadchef=array();
  $mealheadchef["username"] = $chefusername;
  $mealheadchef["realName"] = $cohomeals->get_user_preference($chefusername, 'realName', $chefusername);
  $smarty->assign('mealheadchef', $mealheadchef);
  $smarty->assign('headchefbuddy', $cohomeals->is_signer($chefusername, $user));
}

if ( $mealtype == "recurring" ) $crew = $cohomeals->load_recurring_crew($mealId);
else $crew = $cohomeals->load_crew($mealId);
$smarty->assign('crew', $crew);


$diners = $cohomeals->load_diners($mealId, $mealtype, $user);
$smarty->assign('diners', $diners);

if ( $mealtype == "regular" ) {
    $income = $cohomeals->diner_income( $mealId, false ) / 100;
    $smarty->assign( 'income', $income );
    $numdiners = $cohomeals->count_diners( $mealId, false );
    $smarty->assign( 'numdiners', $numdiners );
    $wtddiners = $cohomeals->count_diners( $mealId, true );
    $smarty->assign( 'wtddiners', $wtddiners );
}

$guest_diners = $cohomeals->load_guests($mealId, 'M', $mealtype); // for now not ready to have recurring guests
$smarty->assign('guest_diners', $guest_diners); 

$buddies = $cohomeals->load_buddies_signees($user, $is_meal_admin, true); //true for including self
$smarty->assign('buddies', $buddies);

$foodlimits = $cohomeals->load_food_restrictions_by_meal($mealId);
$smarty->assign('foodlimits', $foodlimits);

$smarty->assign( 'paperwork_done', $paperwork_done );

$smarty->assign('mid', 'coho_meals-view_entry.tpl');
$smarty->display("tiki.tpl");



?>