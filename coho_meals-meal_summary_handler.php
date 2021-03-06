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
$cohomeals = new CohoMealsLib;
$cohomeals->set_meal_admin( $is_meal_admin );
$cohomeals->set_user( $user );

$myurl = 'coho_meals-meal_summary_handler.php';

$mealId = $_REQUEST["id"];
$mealdatetime = $_REQUEST["mealdatetime"];
$walkin = $_REQUEST["walkin"];
$newguest = $_REQUEST["newguest"];
$multiplier = $_REQUEST["multiplier"];
$host = $_REQUEST["host"];
$shopper = $_REQUEST["shopper"];
$dollars = $_REQUEST["dollars"];
$cents = $_REQUEST["cents"];
$vendor = $_REQUEST["vendor"];
$farmersDollars = $_REQUEST["farmersDollars"];
$farmersCents = $_REQUEST["farmersCents"];
// load pantry below

if ( $is_meal_admin ) $allowed_to_edit = true;
if ( $cohomeals->has_head_chef( $mealId ) == $user ) $allowed_to_edit = true;


/// check to see if things have been entered previously
$done = $cohomeals->paperwork_done( $mealId );
$query="SELECT cal_meal_id FROM cohomeals_food_expenditures WHERE cal_meal_id = $mealId";
if ( $cohomeals->getOne( $query ) ) $done = true;
$query="SELECT cal_meal_id FROM cohomeals_pantry_purchases WHERE cal_meal_id = $mealId";
if ( $cohomeals->getOne( $query ) ) $done = true;


if ( $done ) {
    $smarty->assign('msg', 'Paperwork already done or entries already in food_expenditures or pantry_purchases.');
    $smarty->display("error.tpl");
    die;
}


// charge the people already entered (this function checks to make sure it isn't double-charging
$cohomeals->charge_for_meal( $mealId );

$query = "SELECT cal_base_price FROM cohomeals_meal WHERE cal_id = $mealId";
$base_price = $cohomeals->getOne( $query );

$numwalkins = 0;
// enter and charge the walkins
if ( isset($_REQUEST["walkin"] ) ) {
    $mytable = $tikilib->table('cohomeals_meal_participant');
    foreach( $walkin as $wi ) {
        $multiplier = $cohomeals->get_multiplier( $wi, $cohomeals->get_mealdatetime($mealId) );
        $amount = -1 * $base_price * $multiplier;
        $fullname = $cohomeals->get_fullname( $wi );
        $description = $fullname . " dining (multiplier " . $multiplier . ")";
        $billingGroup = $cohomeals->get_billingId( $wi );
        $cohomeals->charge_person( $billingGroup, $amount, $description, $mealId, $fullname, $wi );
        $mytable->insert( ['cal_id'=>$mealId, 'cal_login'=>$wi, 'cal_type'=>'M'] );
        $numwalkins++;
    }
}

// enter and charge the walkin guests
if ( isset($_REQUEST["newguest"] ) ) {
    $i=0;
    $guesttable = $tikilib->table('cohomeals_meal_guest');
    foreach ( $newguest as $ng ) {
        if ( $host[$i] == "none" ) {
            $cur_host = $user; error_log("host was unset, so set to user filling out the form: " . $user );
        } else $cur_host = $host[$i];
        $hostname = $cohomeals->get_fullname( $cur_host );
        $hostbilling = $cohomeals->get_billingId( $cur_host );
        if ( !$hostbilling ) $hostbilling = $cohomeals->get_billingId( $user );
        $mult = $multiplier[$i];
        if ( !is_numeric( $mult ) ) $mult = 1;
        $amount = -1*$mult*$base_price;
        $description = $ng . " dining (guest of " . $hostname . "), (multiplier " . $mult . ")";
        $cohomeals->charge_person( $hostbilling, $amount, $description, $mealId, $ng, $cur_host );

        $guesttable->insert( ['cal_meal_id'=>$mealId, 'cal_fullname'=>$ng, 'cal_host'=>$cur_host, 'meal_multiplier'=>$mult, 'cal_type'=>'M'] );
        $i++;
        $numwalkins++;
    }
}


// enter the shoppers
if ( isset($_REQUEST["shopper"] ) ) {
    $i=0;
    $shoppertable = $tikilib->table('cohomeals_food_expenditures');
    foreach( $shopper as $shoppername ) {
        $amount = 100*$dollars[$i] + $cents[$i];
        $vendorname = $vendor[$i];

        $res = $shoppertable->insert( ['cal_purchaser'=>$shoppername, 'cal_amount'=>$amount, 'cal_meal_id'=>$mealId, 'cal_source'=>$vendorname] );
        if (!$res) {
            $smarty->assign('msg', 'Error entering shoppers.');
            $smarty->display("error.tpl");
            die;
        }
        $i++;
    }
}


// enter the pantry purchases, including farmers market cards and flat rate spices
if ( $maxid = $cohomeals->getOne( "SELECT MAX( cal_log_id ) FROM cohomeals_pantry_purchases" ) ) {
    $newid = $maxid + 1;
} else $newid = 1;

// farmers market 
if ( isset( $_REQUEST["farmersDollars"] ) ) {
    $sql = "SELECT cal_food_id FROM cohomeals_pantry_food " .
        "WHERE cal_description = 'farmers market'";
    $foodid = $cohomeals->getOne( $sql );

    $amount = 100*$farmersDollars + $farmersCents;
    $query = "INSERT INTO cohomeals_pantry_purchases " .
        "( cal_log_id, cal_food_id, cal_number_units, cal_total_price, cal_type, cal_meal_id ) " .
        "VALUES ( $newid, $foodid, $amount, $amount, 1, $mealId )";
    if (!$cohomeals->query($query) ) {
        $smarty->assign('msg', 'Error entering farmers market.');
        $smarty->display("error.tpl");
        die;
    }
    $newid++;
}

// flat rate
$numdiners = $cohomeals->count_diners( $mealId, false ); // head count, unweighted
$numdiners += $numwalkins;
$flatrate = $numdiners * 10; // inserted into table as cents
$query = "SELECT cal_food_id FROM cohomeals_pantry_food WHERE cal_description = 'flat rate'";
$foodid = $cohomeals->getOne( $query );
$query = "INSERT INTO cohomeals_pantry_purchases " .
            "( cal_log_id, cal_food_id, cal_number_units, cal_total_price, cal_type, cal_meal_id ) " .
            "VALUES ( $newid, $foodid, $flatrate, $flatrate, 1, $mealId )";
if (!$cohomeals->query($query) ) {
    $smarty->assign('msg', 'Error entering flat rate.');
    $smarty->display("error.tpl");
    die;
}
$newid++;

// regular pantry purchases
$allfoods = $cohomeals->load_pantry_foods();
foreach( $allfoods as $food ) {
    $foodid = $food["id"];
    $key = "amount" . $foodid;
    if ( (isset( $_REQUEST[$key] )) && ($_REQUEST[$key] != 0) ) {
        $amt = $_REQUEST[$key];
        $foodcost = $amt * $food["unitcost"];
        $query = "INSERT INTO cohomeals_pantry_purchases " .
            "( cal_log_id, cal_food_id, cal_number_units, cal_total_price, cal_type, cal_meal_id ) " .
            "VALUES ( $newid, $foodid, $amt, $foodcost, 1, $mealId )";
        if (!$cohomeals->query($query) ) {
            $smarty->assign('msg', 'Error entering pantry purchases.');
            $smarty->display("error.tpl");
            die;
        }
        $newid++;
    }
}


// set the flag that says we already did the paperwork
$query = "UPDATE cohomeals_meal SET paperwork_done=1 WHERE cal_id=$mealId";
if (!$cohomeals->query($query) ) {
    $smarty->assign('msg', 'Error with paperwork.');
    $smarty->display("error.tpl");
    die;
}


$nexturl = "coho_meals-display_meal_summary.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
header("Location: $nexturl");
die;

?>
