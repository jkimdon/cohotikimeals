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
$myurl = 'coho_costshare_handler.php';

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );

$my_billing_group = $cohomeals->get_billingId( $user );
if ( $my_billing_group == NULL ) {
    $smarty->assign('msg', 'Error getting billing group ID');
    $smarty->display("error.tpl");
    die;
}    

$transactionType = $_REQUEST["transactionType"];
if ( ($transactionType != "borrower") && ($transactionType != "lender") ) {
    $smarty->assign('msg', 'Error entering transaction. Please try again');
    $smarty->display("error.tpl");
    die;
} else {

    /// maybe todo: check for duplicate entries
    
    $logtable = $cohomeals->table('cohomeals_costshare_log');
    $new_person = '';
    
    if ( $transactionType == "lender" ) {
        $new_borrower = $_REQUEST["new_borrower"];
        $new_dollars = $_REQUEST["lender_dollars"];
        $new_cents = $_REQUEST["lender_cents"];
        $amount = 100*$new_dollars + $new_cents;
        $memo = $_REQUEST["lendmemo"];
        
        $insertValues = ['lender_billing_group_number'=>$my_billing_group, 'borrower_billing_group_number'=>$new_borrower, 'amount'=>$amount, 'memo'=>$memo];
        $logtable->insert( $insertValues );

        $new_person = $new_borrower;
    } elseif ( $transactionType == "borrower" ) {
        $new_lender = $_REQUEST["new_lender"];
        $new_dollars = $_REQUEST["borrower_dollars"];
        $new_cents = $_REQUEST["borrower_cents"];
        $amount = 100*$new_dollars + $new_cents;
        $memo = $_REQUEST["borrowmemo"];

        $insertValues = ['lender_billing_group_number'=>$new_lender, 'borrower_billing_group_number'=>$my_billing_group, 'amount'=>$amount, 'memo'=>$memo];
        $logtable->insert( $insertValues );

        $new_person = $new_lender;
    }

    //// update quickref
    $iowethem = 0.0;
    $im_borrower = $logtable->fetchAll( ['amount'], ['borrower_billing_group_number'=>$my_billing_group, 'lender_billing_group_number'=>$new_person] );
    foreach ($im_borrower as $row) {
        $iowethem += $row['amount'];
    }
    $im_lender = $logtable->fetchAll( ['amount'], ['lender_billing_group_number'=>$my_billing_group, 'borrower_billing_group_number'=>$new_person] );
    foreach ($im_lender as $row) {
        $iowethem -= $row['amount'];
    }

        
    $reftable = $cohomeals->table('cohomeals_costshare_quickref');
    $reftable->delete(['lender_billing_group_number'=>$my_billing_group, 'borrower_billing_group_number'=>$new_person]);
    $reftable->delete(['lender_billing_group_number'=>$new_person, 'borrower_billing_group_number'=>$my_billing_group]);
    if ($iowethem < 0) {
        $amt = -1 * $iowethem;
        $reftable->insert(['lender_billing_group_number'=>$my_billing_group, 'borrower_billing_group_number'=>$new_person, 'amount'=>$amt]);
    } else if ($iowethem > 0) { // if is 0, no entry needed
        $reftable->insert(['lender_billing_group_number'=>$new_person, 'borrower_billing_group_number'=>$my_billing_group, 'amount'=>$iowethem]);
    }

    

///////////
$nexturl = "coho_costsharing.php";
header("Location: $nexturl");
die;
}

?>