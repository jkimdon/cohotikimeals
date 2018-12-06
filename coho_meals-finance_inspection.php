<?php

$section = 'cohomeals';
require_once('tiki-setup.php');
include_once ('lib/cohomeals/coho_mealslib.php');

$access->check_feature('feature_cohomeals');
$access->check_permission('tiki_p_view_meals');

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$smarty->assign('loggedinuser', $user);

$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign('is_meal_admin', $is_meal_admin);
$cohomeals->set_meal_finance_admin( $is_finance_admin );
$smarty->assign( 'is_finance_admin', $is_finance_admin );

$fintable = $tikilib->table('cohomeals_financial_log');
$billingArray = array();
$cohomeals->get_billingGroups( $billingArray );
$balance = 0;

foreach ( $billingArray as $bgId=>$bgName ) {
    $last_log = $fintable->fetchOne( $fintable->max('cal_log_id'), ['cal_billing_group'=>$bgId] );
    $balance += $fintable->fetchOne( 'cal_running_balance', ['cal_billing_group'=>$bgId, 'cal_log_id'=>$last_log] );
}
$balance /= 100;
$smarty->assign('balance', $balance );


/////////// for now, from October 1, 2017 to Sept 30, 2018 ///////////

/// calculate data for each chef
// for the desired time frame, 
// count up their meals, diners, income, and expenses

$chefs = array();
$mealTable = $tikilib->table('cohomeals_meal');
$chefTable = $tikilib->table('cohomeals_meal_participant');
$allMeals = $mealTable->fetchAll(['cal_id', 'paperwork_done', 'diners_charged'], ['cal_date'=>$mealTable->between('20170930','20181001'), 'cal_cancelled'=>0]);

$allIncome = 0;
$expectedIncome = 0;
$allPantry = 0;
$allShoppers = 0;
$allFarmers = 0;
$allFlatrate = 0;
foreach ( $allMeals as $meal ) {
    $mealId = $meal['cal_id'];
    $thisChef = $chefTable->fetchOne('cal_login', ['cal_id'=>$mealId, 'cal_type'=>'H']);
    if ( !isset($chefs[$thisChef]) ) {
        $chefs[$thisChef] = ['fullName'=>$cohomeals->get_fullname($thisChef), 'numMeals'=>0,'numDiners'=>0, 'paperworkDone'=>0, 'dinersCharged'=>0, 'calc_income'=>0,'logged_income'=>0,'expenses'=>0];
    }
    $chefs[$thisChef]['numMeals']++;
    $chefs[$thisChef]['numDiners'] += $cohomeals->count_diners( $mealId, true );
    $chefs[$thisChef]['paperworkDone'] += $meal['paperwork_done'];
    $chefs[$thisChef]['mealsCharged'] += $meal['diners_charged'];
    $thisincome = $cohomeals->diner_income( $mealId, true )/100;
    $expectedThisIncome = $cohomeals->diner_income( $mealId, false )/100;
    $chefs[$thisChef]['netIncome'] += $thisincome;
    $allIncome += $thisincome;
    $expectedIncome += $expectedThisIncome;
    $shoppers = 0;
    $pantry = 0;
    $farmers = 0;
    $flatrate = 0;
    $chefs[$thisChef]['netIncome'] -= $cohomeals->get_MealExpenses( $mealId, $shoppers, $pantry, $farmers, $flatrate )/100;
    $allShoppers += $shoppers/100;
    $allPantry += $pantry/100;
    $allFarmers += $farmers/100;
    $allFlatrate += $flatrate/100;
}
$smarty->assign('chefs', $chefs);
$smarty->assign('allShoppers', $allShoppers);
$smarty->assign('allPantry', $allPantry);
$smarty->assign('allFlatrate', $allFlatrate);
$smarty->assign('allFarmers', $allFarmers);
$smarty->assign('allIncome', $allIncome);
$smarty->assign('expectedIncome', $expectedIncome);
$allExpenses = $allShoppers + $allPantry + $allFlatrate + $allFarmers;
$smarty->assign('allExpenses', $allExpenses);
$smarty->assign('netIncome', $allIncome - $allExpenses );


$smarty->assign('mid', 'coho_meals-finance_inspection.tpl');
$smarty->display("tiki.tpl");

?>