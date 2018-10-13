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

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;

if (!$is_meal_admin) {
    $smarty->assign('msg', 'Error refunding meal.');
    $smarty->display("error.tpl");
    die;
}

$mealId = $_REQUEST["id"];

if ( !isset( $_REQUEST["id"] ) || empty ( $mealId ) || $mealId <= 0 || ! is_numeric ( $mealId ) ) {
  $smarty->assign('msg', 'Invalid entry id.');
  $smarty->display("error.tpl");
  die;
} 

$cohomeals = new CohoMealsLib;
$cohomeals->set_meal_admin( $is_meal_admin );

$mealdatetime = $cohomeals->get_mealdatetime( $mealId );
$cohomeals->charge_for_meal( $mealId, true );

$nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
header("Location: $nexturl");
die;

?>