<?php // to be inserted at the end of the get_user_preference list in tiki-user_preferences.php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}
include_once('lib/cohomeals/coho_mealslib.php');
$meals = new CohoMealsLib;

$bg = $tikilib->get_user_preference($userwatch, 'billingGroup', '');
if ($bg) {
    $bgname = $meals->get_billing_group_name($bg);
} else $bgname = "UNASSIGNED. PLEASE FIX!!";
$smarty->assign('billingGroup', $bgname);

$userGroups = $userlib->get_user_groups_inclusion($userwatch);
if (array_key_exists('CoHo owners', $userGroups) || array_key_exists('on-site renters', $userGroups)) {
  $tikilib->get_user_preference($userwatch, 'unitNumber', '');  
  $smarty->assign('showUnit', 'y');
} else {
  $smarty->assign('showUnit', 'n');
}

$tikilib->get_user_preference($userwatch, 'meal_multiplier', 1);
if (!$user_preferences[$userwatch]['meal_multiplier']) $user_preferences[$userwatch]['meal_multiplier'] = 1;


// food preferences




?>