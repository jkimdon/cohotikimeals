<?php

$section = 'cohomeals';
require_once('tiki-setup.php');
include_once ('lib/cohomeals/coho_mealslib.php');


$cohomeals = new CohoMealsLib;

$tz = new DateTimeZone( 'US/Pacific' );
$todaysDate = new DateTime( "now", $tz );


error_log("\n cron auto-charge");

$mealtable = $cohomeals->table('cohomeals_meal');

$conditions = array();
$conditions['cal_cancelled']= 0;
$conditions['diners_charged'] = $mealtable->coho_isNULL();
$conditions['cal_date'] = $mealtable->coho_lesserThanOrEqualTo( $todaysDate->format('Ymd') );
$uncharged = $mealtable->fetchAll( ['cal_id', 'cal_date', 'meal_title'], $conditions );
$msg = "On " . $todaysDate->format('Ymd, H:i') . ", charging...";
error_log( $msg );
$count = 0;
foreach ( $uncharged as $meal ) {
    $mealid = $meal['cal_id'];
    $cohomeals->charge_for_meal( $mealid );
    error_log( $meal['meal_title'] . ", ID = " . $meal['cal_id'] . " on " . $meal['cal_date'] );
    $count++;
}
error_log( "Charged " . $count . " meals.");

?>