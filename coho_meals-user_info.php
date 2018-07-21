<?php
/// used in the meal account menu

////
//// top stuff copied from tiki-user_preferences.php
////

require_once('tiki-setup.php');
$access->check_user($user);

// Make sure user preferences uses https if set
if (! $https_mode && isset($https_login) && $https_login == 'required') {
	header('Location: ' . $base_url_https . 'tiki-user_preferences.php');
	die;
}
if (! empty($_REQUEST['userId'])) {
	$userwatch = $tikilib->get_user_login($_REQUEST['userId']);
} elseif (! empty($_REQUEST["view_user"])) {
	$userwatch = $_REQUEST["view_user"];
} else {
	$userwatch = $user;
}

if ($userwatch != $user) {
	$access->check_permission('tiki_p_admin_users');
	if (empty($userwatch) || empty($userlib->user_exists($userwatch))) {
		$smarty->assign('msg', tra("Unknown user"));
		$smarty->display("error.tpl");
		die;
	}
}


$mealperms = Perms::get(array( 'type' => 'meals' ));
$is_meal_admin = $mealperms->admin_meals;

$cohomeals = new CohoMealsLib;
$cohomeals->set_user( $user );
$cohomeals->set_meal_admin( $is_meal_admin );
$smarty->assign( 'is_meal_admin', $is_meal_admin );

// find the billing group
$billingId = $cohomeals->get_billingId( $user );
if (!$billingId) {
    $smarty->assign('msg', 'Bad billing group ID.');
    $smarty->display("error.tpl");
    die;
}
$billingName = $cohomeals->get_billing_group_name( $billingId );
$smarty->assign('billingName', $billingName);

// legacy billing group is to write the name not the number, so we support both
$billing_sql = "cal_billing_group='$billingId' OR cal_billing_group='$billingName'";

/// for now, just show the last 100 entries instead of allowing searching

$sql = "SELECT cal_login, cal_description, cal_meal_id, cal_amount, cal_running_balance, cal_text, cal_timestamp " .
    "FROM cohomeals_financial_log WHERE " . $billing_sql .
    " ORDER BY cal_timestamp DESC LIMIT 100"; 
$finlog = $cohomeals->fetchAll($sql);
$smarty->assign('finlog', $finlog);

$sql = "SELECT cal_login, cal_description, cal_meal_id, cal_amount, cal_running_balance, " .
    "cal_text, cal_timestamp, cal_billing_group " .
    "FROM cohomeals_financial_log ORDER BY cal_timestamp DESC LIMIT 100"; 
$allrows = $cohomeals->fetchAll($sql);
$adminfinlog = array();
foreach( $allrows as $row ) {
    // in case the billing group is not yet set
    $bgid = $row["cal_billing_group"];
    if ( !is_numeric($bgid) || $bgid<=0 ) {
        $bgid = $cohomeals->make_new_billingGroup( $row["cal_login"] );
    }
    $bgname = $cohomeals->get_billing_group_name( $bgid );
    $adminfinlog[] = array( "cal_timestamp"=>$row["cal_timestamp"], "billingGroup"=>$bgname, "cal_description"=>$row["cal_description"], "cal_meal_id"=>$row["cal_meal_id"], "cal_text"=>$row["cal_text"], "cal_amount"=>$row["cal_amount"], "cal_running_balance"=>$row["cal_running_balance"]);
}
$smarty->assign('adminfinlog', $adminfinlog);


$smarty->assign('mid', 'coho_tiki-user_info.tpl');
$smarty->display("tiki.tpl");










?>