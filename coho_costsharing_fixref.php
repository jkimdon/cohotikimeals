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
$myurl = 'coho_meals-costshare_handler.php';

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );

if ($is_meal_admin == true) {
    $records = array();
    
    $logtable = $cohomeals->table( 'cohomeals_costshare_log' );
    $reftable = $cohomeals->table( 'cohomeals_costshare_quickref' );
    
    $rows = $logtable->fetchAll(['lender_billing_group_number', 'borrower_billing_group_number', 'amount'],[], -1, -1, ['lender_billing_group_number'=>'asc']);
    foreach( $rows as $row ) {
        $ln = $row['lender_billing_group_number'];
        $bn = $row['borrower_billing_group_number'];
        if ($ln < $bn) {
            $smaller = $ln;
            $larger = $bn;
            $amt = $row['amount'];
        } else {
            $smaller = $bn;
            $larger = $ln;
            $amt = -1 * $row['amount'];
        }
        if ( $records[$smaller][$larger]['established'] != true ) {
            $records[$smaller][$larger]['established'] = true;
            $records[$smaller][$larger]['amount'] = $amt;
        } else {
            $records[$smaller][$larger]['amount'] += $amt;
        }
    }

    foreach( $records as $sm => $val ) {
        foreach ( $val as $lar => $subval ) {
            $sum = $subval['amount'];
            $reftable->delete(['lender_billing_group_number'=>$sm, 'borrower_billing_group_number'=>$lar]);
            $reftable->delete(['lender_billing_group_number'=>$lar, 'borrower_billing_group_number'=>$sm]);
            if ($sum < 0) {
                $lender = $lar;
                $borrower = $sm;
                $enteramt = -1 * $sum;
                $reftable->insert(['lender_billing_group_number'=>$lender, 'borrower_billing_group_number'=>$borrower, 'amount'=>$enteramt]);
            } else if ($sum > 0 ) { /// if $sum==0, no entry needed
                $lender = $sm;
                $borrower = $lar;
                $enteramt = $sum;
                $reftable->insert(['lender_billing_group_number'=>$lender, 'borrower_billing_group_number'=>$borrower, 'amount'=>$enteramt]);
            }
        }
    } 
    
}

///////////
$nexturl = "coho_costsharing.php";
header("Location: $nexturl");
die;


?>