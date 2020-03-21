<?php

/// setup, check permissions

require_once('tiki-setup.php');
$access->check_user($user);
$access->check_permission(['tiki_p_view_meals']);


$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;
$myurl = 'coho_costshare_log.php';

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign( 'is_meal_admin', $is_meal_admin );

$my_billing_group = $cohomeals->get_billingId( $user );

// read in post variables for page number
$pageNo = 1;
if ( isset( $_REQUEST["pageNo"] ) ) $pageNo = $_REQUEST["pageNo"];
$smarty->assign( 'pageNo', $pageNo );

// read in post variables for filtering
$tmptz = TikiDate::TimezoneIsValidId($prefs['server_timezone']) ? $prefs['server_timezone'] : 'US/Pacific';
$tz = new DateTimeZone( $tmptz );
$filterstart = new DateTime( "now", $tz );
$filterend = new DateTime("now", $tz);
if ( isset($_REQUEST["finfilter_start_Month"]) ) {
    if ( isset($_REQUEST["finfilter_start_Day"]) && isset($_REQUEST["finfilter_start_Day"]) ) {
        $filterstart->setDate( $_REQUEST["finfilter_start_Year"], $_REQUEST["finfilter_start_Month"], $_REQUEST["finfilter_start_Day"] );
    } // if all is not properly set, leave as today
}
if ( isset($_REQUEST["finfilter_end_Month"]) && isset($_REQUEST["finfilter_end_Year"]) && isset($_REQUEST["finfilter_end_Day"]) ) { 
    $filterend->setDate( $_REQUEST["finfilter_end_Year"], $_REQUEST["finfilter_end_Month"], $_REQUEST["finfilter_end_Day"] );
    if ($filterend < $filterstart) {
        $filterstart = clone $filterend;
        $filterstart->modify( '-1 day' );
    }
} else {
    $filterstart->modify('-1 month');
}

$smarty->assign('filterstart', $filterstart->format('U') );
$smarty->assign('filterend', $filterend->format('U') );

/// find logs
$logtable = $cohomeals->table('cohomeals_costshare_log');
$offset = 100*($pageNo-1);
$sql = "SELECT lender_billing_group_number, borrower_billing_group_number, amount, memo, dateEntered FROM cohomeals_costshare_log " .
    "WHERE lender_billing_group_number = $my_billing_group OR borrower_billing_group_number = $my_billing_group " .
    "ORDER BY dateEntered ASC LIMIT 100 OFFSET $offset";
//$whereclause = $logtable->any(['lender_billing_group_number'=>$my_billing_group, 'borrower_billing_group_number'=>$my_billing_group]);
//$whereclause = $logtable->any(['lender_billing_group_number'=>$my_billing_group]);
//$orderclause = ['dateEntered'=>'ASC'];
//$limit = 100;
//$offset = 100*($pageNo-1);

//$logs = $logtable->fetchAll(['lender_billing_group_number', 'borrower_billing_group_number', 'amount', 'memo'], $whereclause, $limit, $offset, $orderclause);
$logs = $cohomeals->fetchAll( $sql );

$costlog = array();
foreach ( $logs as $log ) {
    if ( $log['lender_billing_group_number'] == $my_billing_group ) {
        $borrower = $cohomeals->get_billing_group_name( $log['borrower_billing_group_number'] );
        $costlog[] = array( "lender"=>"Me", "borrower"=>$borrower, "amount"=>($log['amount']/100), "memo"=>$log['memo'], "dateEntered"=>$log['dateEntered']);
    } else { // I am borrower
        $lender = $cohomeals->get_billing_group_name( $log['lender_billing_group_number'] );
        $costlog[] = array( "lender"=>$lender, "borrower"=>"Me", "amount"=>($log['amount']/100), "memo"=>$log['memo'], 'dateEntered'=>$log['dateEntered']);
    }
}
$smarty->assign( 'costlog', $costlog );


$smarty->assign('mid', 'coho_costsharing_log.tpl');
$smarty->display("tiki.tpl");

?>