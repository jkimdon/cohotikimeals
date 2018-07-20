<?php

$section = 'cohomeals';
require_once('tiki-setup.php');
include_once ('lib/cohomeals/coho_mealslib.php');

$access->check_permission('tiki_p_view_meals');

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$smarty->assign('loggedinuser', $user);

$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign('is_meal_admin', $is_meal_admin);

$myurl = 'coho_meals-edit_meal_summary.php';

$confirmOrDisplay = $_REQUEST["confirmOrDisplay"]; 
$mealId = $_REQUEST["id"]; 
$smarty->assign( 'mealId', $mealId );
if ( !($mealId > 0) ) {
    $smarty->assign('errortype', 'Empty meal id.');
    $smarty->display("error.tpl");
    die;
}
$smarty->assign( 'mealId', $mealId );

$mealinfo = array();
$cohomeals->load_meal_info( "regular", $mealId, $mealinfo );
$smarty->assign( 'meal', $mealinfo );

$paperwork_done = $cohomeals->paperwork_done( $mealId );
$smarty->assign( 'paperwork_done', $paperwork_done );

if ( !$paperwork_done ) { // getting values from the form not the database

    $presignup_income = $cohomeals->diner_income( $mealId, false ); 
    $smarty->assign( 'presignup_income', $presignup_income );

    $walkin_income = 0.00;
    $numwalkins = 0;
    $weighted_diners = 0.00;
    $weighted_diners += $cohomeals->count_diners( $mealId, true );
    $preweighted = $weighted_diners; // used later too
    $smarty->assign( 'preweighted', $preweighted );
    
    // walkin meal plan participants
    if ( isset( $_REQUEST["walkin"] ) ) $walkins = $_REQUEST["walkin"];
    else $walkins = array('');
    foreach( $walkins as $walkin ) { 
        if ( $walkin != '' ) {
            $walkin_income += $cohomeals->person_cost( $mealId, $walkin );
            $numwalkins++;
            $weighted_diners += $cohomeals->get_multiplier( $walkin );
        }
    } 
    // walkin guests
    if (isset( $_REQUEST["newguest"] ) ) $newguests = $_REQUEST["newguests"];
    else $newguests = [];
    if (isset( $_REQUEST["multiplier"] ) ) $newguests = $_REQUEST["multipliers"];
    else $multipliers = [];
    $i=0;
    foreach ( $newguests as $newguest ) {
        $walkin_income += ( ( $multiplier[$i] * $mealinfo["base_price"] ) / 100 );
        $numwalkins++;
        $weighted_diners += $multiplier[$i];
        $i++;
    }
    $smarty->assign( 'walkinweighted', $weighted_diners - $preweighted );
    $smarty->assign( 'walkin_income', $walkin_income );
    $smarty->assign( 'numwalkins', $numwalkins );

    $totalincome = $presignup_income + $walkin_income;
    $smarty->assign( 'totalincome', $totalincome );
    
    $totalexpenses = 0;

    $shoppers = $_REQUEST["shopper"];
    $shoppers_dollars = $_REQUEST["dollars"];
    $shoppers_cents = $_REQUEST["cents"];
    $i=0;
    $shoppercost = 0;
    foreach( $shoppers as $shopper ) {
        $shoppercost += $shoppers_dollars[$i] + $shoppers_cents[$i]/100;
        $i++;
    }
    $smarty->assign( 'shoppercost', $shoppercost );
    $totalexpenses += $shoppercost;
    
    $farmdollars = $_REQUEST["farmersDollars"];
    $farmcents = $_REQUEST["farmersCents"]; 
    $farmercost = $farmdollars + $farmcents/100;
    $smarty->assign( 'farmercost', $farmercost );
    $totalexpenses += $farmercost/100;
    
    $numdiners = $cohomeals->count_diners( $mealId, false ); // unweighted
    $numdiners += $numwalkins; // calculated above
    $smarty->assign( 'numdiners', $numdiners );
    $flatrate = $numdiners * 0.1;
    $smarty->assign( 'flatrate', $flatrate );
    $totalexpenses += $flatrate/100;
    $smarty->assign( 'numdiners', $numdiners );
    
    $pantrycost = 0;
    $pantry_details = array();
    $pantry_passthrough = array();
    $allfoods = $cohomeals->load_pantry_foods();
    foreach( $allfoods as $food ) {
        $key = "amount" . $food["id"];
        if ( isset( $_REQUEST[$key] ) && ($_REQUEST[$key] != 0)) {
            $amt = $_REQUEST[$key];
            $foodcost = $amt * $food["unitcost"];
            $pantrycost += $foodcost;
            $pantry_details[] = array( "numunits"=>$amt, "units"=>$food["unit"], "food"=>$food["name"], "cost"=>$foodcost/100.00 );
            $pantry_passthrough[] = array( "key"=>$key, "amt"=>$amt );
        }
    }
    $smarty->assign( 'pantrycost', $pantrycost/100.00 );
    $smarty->assign( 'pantry_details', $pantry_details );
    $totalexpenses += $pantrycost/100;

    $smarty->assign( 'totalexpenses', $totalexpenses );
    $smarty->assign( 'profit', $totalincome-$totalexpenses );
    $smarty->assign( 'per_person', $totalexpenses/$weighted_diners );

    // passthrough values
    $smarty->assign( 'passthroughwalkin', $_REQUEST["walkin"] );
    $smarty->assign( 'passthroughnewguest', $_REQUEST["newguest"] );
    $smarty->assign( 'passthroughmultiplier', $_REQUEST["multiplier"] );
    $smarty->assign( 'passthroughhost', $_REQUEST["host"] );
    $smarty->assign( 'passthroughshopper', $_REQUEST["shopper"] );
    $smarty->assign( 'passthroughdollars', $_REQUEST["dollars"] );
    $smarty->assign( 'passthroughcents', $_REQUEST["cents"] );
    $smarty->assign( 'passthroughvendor', $_REQUEST["vendor"] );
    $smarty->assign( 'passthroughfarmersdollars', $_REQUEST["farmersDollars"] );
    $smarty->assign( 'passthroughfarmerscents', $_REQUEST["farmersCents"] );
    $smarty->assign( 'passthroughpantry', $pantry_passthrough);

    
} else { // getting data from the database

    $presignup_income = $cohomeals->diner_income( $mealId, true );
    $smarty->assign( 'presignup_income', $presignup_income );

    $totalincome = $presignup_income;
    $smarty->assign( 'totalincome', $totalincome );

    $totalexpenses = 0;
    
    $sql = "SELECT cal_amount FROM cohomeals_food_expenditures WHERE cal_meal_id = $mealId";
    $shoppercost = 0;
    $shoppers = $cohomeals->fetchAll($sql);
    foreach( $shoppers as $shopper ) {
        $shoppercost += $shopper["cal_amount"]/100;
    }
    $smarty->assign( 'shoppercost', $shoppercost );    
    $totalexpenses += $shoppercost;
    
    $farmercost = $cohomeals->get_food_cost_for_meal( $mealId, 'farmers market' );
    $smarty->assign( 'farmercost', $farmercost/100 );        
    $totalexpenses += $farmercost;
    
    $flatrate = $cohomeals->get_food_cost_for_meal( $mealId, 'flat rate' );
    $smarty->assign( 'flatrate', $flatrate/100 );        
    $totalexpenses += $flatrate;
    
    $pantry_description = '';
    $pantry_omit = array( 'farmers market', 'flat rate' );
    $pantrycost = $cohomeals->get_pantry_purchases( $pantry_details, $mealId, $pantry_omit );
    $smarty->assign( 'pantrycost', $pantrycost/100 );
    $smarty->assign( 'pantry_details', $pantry_details ); 
    $totalexpenses += $pantrycost;
    $totalexpenses /= 100;
    
    $smarty->assign( 'totalexpenses', $totalexpenses );

    $smarty->assign( 'profit', $totalincome-$totalexpenses );

    $weighted_diners = $cohomeals->count_diners( $mealId, true );
    $smarty->assign( 'preweighted', $weighted_diners );
    $smarty->assign( 'per_person', $totalexpenses/$weighted_diners );

    $numdiners = $cohomeals->count_diners( $mealId, false );
    $smarty->assign( 'numdiners', $numdiners );
}


$smarty->assign('mid', 'coho_meals-display_meal_summary.tpl');
$smarty->display("tiki.tpl");

?>