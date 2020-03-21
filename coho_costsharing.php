<?php

require_once('tiki-setup.php');
$access->check_user($user);

$access->check_permission('tiki_p_view_meals');

// Make sure user preferences uses https if set
if (! $https_mode && isset($https_login) && $https_login == 'required') {
	header('Location: ' . $base_url_https . 'tiki-user_preferences.php');
	die;
}

$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
$is_finance_admin = $mealperms->finance_meals;
if ($is_meal_admin) $is_finance_admin = true;

$in_meal_program = $mealperms->view_meals;
$smarty->assign('in_meal_program', $in_meal_program );


$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign( 'is_meal_admin', $is_meal_admin );
$cohomeals->set_meal_finance_admin( $is_finance_admin );
$smarty->assign( 'is_finance_admin', $is_finance_admin );

/////////// 
$my_billing_group = $cohomeals->get_billingId( $user );

$billingArray = array();
$cohomeals->get_billingGroups( $billingArray );
$billingArray[0]='Please select neighbor';
$smarty->assign('allBillingGroups', $billingArray);

///////////

$debt_message = '';
$lend_message = '';

$reftable = $cohomeals->table('cohomeals_costshare_quickref');
$borrowing = $reftable->fetchAll( ['lender_billing_group_number', 'amount'], ['borrower_billing_group_number'=>$my_billing_group]);
foreach( $borrowing as $row ) {
    $amt_string = $cohomeals->price_to_str( $row['amount'] );
    $bgname = $cohomeals->get_billing_group_name( $row['lender_billing_group_number'] );
    $debt_message .= "I owe " . $bgname . " " . $amt_string . "<br>";
}
$lending = $reftable->fetchAll( ['borrower_billing_group_number', 'amount'], ['lender_billing_group_number'=>$my_billing_group]);
foreach( $lending as $row ) {
    $amt_string = $cohomeals->price_to_str( $row['amount'] );
    $bgname = $cohomeals->get_billing_group_name( $row['borrower_billing_group_number'] );
    $lend_message .= $bgname . " owes me " . $amt_string . "<br>";
}


if ( $debt_message == '' ) $debt_message = "You owe nothing.";
if ( $lend_message == '' ) $lend_message = "No one owes you anything.";
$smarty->assign( 'debt_message', $debt_message );
$smarty->assign( 'lend_message', $lend_message );

///////////







$smarty->assign('mid', 'coho_costsharing.tpl');
$smarty->display("tiki.tpl");


?>