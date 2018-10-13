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

// read in post variables for filtering
$filterstart = new DateTime();
$filterend = new DateTime($today);
if ( isset($_REQUEST["finfilter_start_Month"]) && isset($_REQUEST["finfilter_end_Month"]) ) {
    $filterstart->setDate( $_REQUEST["finfilter_start_Year"], $_REQUEST["finfilter_start_Month"], $_REQUEST["finfilter_start_Day"] );
    $filterend->setDate( $_REQUEST["finfilter_end_Year"], $_REQUEST["finfilter_end_Month"], $_REQUEST["finfilter_end_Day"] );
    if ($filterend < $filterstart) {
        $filterstart = clone $filterend;
        $filterstart->modify( '-1 day' );
    }
} else {
    $filterstart->modify('-1 month');
}
$filterstart->modify('+6 hours'); // to avoid day changes due to timezones
$filterend->modify('+6 hours');
$smarty->assign('filterstart', $filterstart->format('U') );
$smarty->assign('filterend', $filterend->format('U') );
$sortbymeal=false;
if (isset($_REQUEST["sortbymeal"]) ) {
    $sortbymeal=$_REQUEST["sortbymeal"];
}
$smarty->assign('sortbymeal', $sortbymeal);

// find the billing group
$billingId = $cohomeals->get_billingId( $user );
if (!$billingId) {
    $smarty->assign('msg', 'Bad billing group ID.');
    $smarty->display("error.tpl");
    die;
}
$billingName = $cohomeals->get_billing_group_name( $billingId );
$smarty->assign('billingName', $billingName);
$smarty->assign('whichbillingId', $billingId );
$billingArray = array();
$cohomeals->get_billingGroups( $billingArray );
$smarty->assign('allBillingGroups', $billingArray );

$adminShowBG = 0;
$BGterms = "";
if (isset($_REQUEST["showbillinggroup"])) {
    $adminShowBG = $_REQUEST["showbillinggroup"];
    if ( $adminShowBG > 0 ) {
        $BGterms = " cal_billing_group = " . $adminShowBG . " ";
    }
}
$smarty->assign('adminShowBG', $adminShowBG );

// legacy billing group is to write the name not the number, so we support both
$billing_sql = "WHERE (cal_billing_group='$billingId' OR cal_billing_group='$billingName')";

if ( $sortbymeal ) {

    $query = "SELECT cal_id FROM cohomeals_meal WHERE (cal_date <= " . $filterend->format('Ymd') . ") AND (cal_date >= " . $filterstart->format('Ymd') . ")";
    $allrows = $cohomeals->fetchAll($query);
    $idselectors = '';
    if ($allrows) {
        $idselectors = " cal_meal_id IN (";
        $first = true;
        foreach( $allrows as $meals ) { 
            if (!$first) $idselectors .= ", ";
            $idselectors .= $meals["cal_id"];
            $first = false;
        }
        $idselectors .= ")";
    }

    // individual log tab
    $whereclause = $billing_sql;
    if ( $idselectors != "" ) $whereclause .= " AND " . $idselectors;
    $query2 = "SELECT cal_login, cal_meal_id, cal_description, cal_amount, cal_running_balance, cal_text, cal_timestamp FROM cohomeals_financial_log " . $whereclause . " ORDER BY cal_timestamp DESC LIMIT 100";
    $newrows = $cohomeals->fetchAll($query2);
    $finlog = array();
    foreach( $newrows as $row ) {
        $mealtitle = $cohomeals->get_mealtitle( $row["cal_meal_id"] );
        $mealdatetime = $cohomeals->get_mealdatetime( $row["cal_meal_id"] );
        $finlog[] = array( "cal_timestamp"=>$row["cal_timestamp"], "cal_description"=>$row["cal_description"], "cal_meal_id"=>$row["cal_meal_id"], "mealtitle"=>$mealtitle, "mealdatetime"=>$mealdatetime, "cal_text"=>$row["cal_text"], "cal_amount"=>$row["cal_amount"], "cal_running_balance"=>$row["cal_running_balance"]);
    }
    $smarty->assign('adminfinlog', $adminfinlog);
    $smarty->assign('finlog', $finlog);
    
    // admin log tab
    if ( $is_meal_admin ) {

        $whereclause = "WHERE ";
        if ($BGterms != "") {
            $whereclause .= $BGterms;
            if ( $idselectors != "" ) $whereclause .= " AND " . $idselectors;
        } else if ( $idselectors != "" ) $whereclause = "WHERE " . $idselectors;
        else $whereclause = "";
        $query2 = "SELECT cal_login, cal_meal_id, cal_description, cal_amount, cal_running_balance, cal_text, cal_timestamp FROM cohomeals_financial_log " . $whereclause . " ORDER BY cal_timestamp DESC, cal_billing_group LIMIT 100";
        $newrows = $cohomeals->fetchAll($query2);
        $adminfinlog = array();
        foreach( $newrows as $row ) {
            // in case the billing group is not yet set
            $bgid = $row["cal_billing_group"];
            if ( !is_numeric($bgid) || $bgid<=0 ) {
                $bgid = $cohomeals->make_new_billingGroup( $row["cal_login"] );
            }
            $bgname = $cohomeals->get_billing_group_name( $bgid );
            $mealtitle = $cohomeals->get_mealtitle( $row["cal_meal_id"] );
            $mealdatetime = $cohomeals->get_mealdatetime( $row["cal_meal_id"] );
            $adminfinlog[] = array( "cal_timestamp"=>$row["cal_timestamp"], "billingGroup"=>$bgname, "cal_description"=>$row["cal_description"], "cal_meal_id"=>$row["cal_meal_id"], "mealtitle"=>$mealtitle, "mealdatetime"=>$mealdatetime, "cal_text"=>$row["cal_text"], "cal_amount"=>$row["cal_amount"], "cal_running_balance"=>$row["cal_running_balance"]);
        }
        $smarty->assign('adminfinlog', $adminfinlog);
    }
} else { // sort by transaction date

    // individual log tab
    $whereclause = $billing_sql;
    $whereclase .= " AND (cal_timestamp <= FROM_UNIXTIME(" . $filterend->format('U') . ")) AND (cal_timestamp >= FROM_UNIXTIME(" . $filterstart->format('U') . ")) ";
    $query2 = "SELECT cal_login, cal_description, cal_meal_id, cal_amount, cal_running_balance, cal_text, cal_timestamp FROM cohomeals_financial_log " . $whereclause . " ORDER BY cal_timestamp DESC LIMIT 100"; 
    $newrows = $cohomeals->fetchAll($query2);
    $finlog = array();
    foreach( $newrows as $row ) {
        $mealtitle = $cohomeals->get_mealtitle( $row["cal_meal_id"] );
        $mealdatetime = $cohomeals->get_mealdatetime( $row["cal_meal_id"] );
        $finlog[] = array( "cal_timestamp"=>$row["cal_timestamp"], "cal_description"=>$row["cal_description"], "cal_meal_id"=>$row["cal_meal_id"], "mealtitle"=>$mealtitle, "mealdatetime"=>$mealdatetime, "cal_text"=>$row["cal_text"], "cal_amount"=>$row["cal_amount"], "cal_running_balance"=>$row["cal_running_balance"]);
    }
    $smarty->assign('adminfinlog', $adminfinlog);
    $smarty->assign('finlog', $finlog);

    // admin log tab
    if ( $is_meal_admin) {
        $whereclause = " WHERE (cal_timestamp <= FROM_UNIXTIME(" . $filterend->format('U') . ")) AND (cal_timestamp >= FROM_UNIXTIME(" . $filterstart->format('U') . ")) ";
        if ( $BGterms != "" ) $whereclause .= " AND " . $BGterms;
        
        $sql = "SELECT cal_login, cal_description, cal_meal_id, cal_amount, cal_running_balance, " .
            "cal_text, cal_timestamp, cal_billing_group " .
            "FROM cohomeals_financial_log " . $whereclause . "ORDER BY cal_timestamp DESC, cal_billing_group LIMIT 100";
        $allrows = $cohomeals->fetchAll($sql);
        $adminfinlog = array();
        foreach( $allrows as $row ) {
            // in case the billing group is not yet set
            $bgid = $row["cal_billing_group"];
            if ( !is_numeric($bgid) || $bgid<=0 ) {
                $bgid = $cohomeals->make_new_billingGroup( $row["cal_login"] );
            }
            $bgname = $cohomeals->get_billing_group_name( $bgid );
            $mealtitle = $cohomeals->get_mealtitle( $row["cal_meal_id"] );
            $mealdatetime = $cohomeals->get_mealdatetime( $row["cal_meal_id"] );
            $adminfinlog[] = array( "cal_timestamp"=>$row["cal_timestamp"], "billingGroup"=>$bgname, "cal_description"=>$row["cal_description"], "cal_meal_id"=>$row["cal_meal_id"], "mealtitle"=>$mealtitle, "mealdatetime"=>$mealdatetime, "cal_text"=>$row["cal_text"], "cal_amount"=>$row["cal_amount"], "cal_running_balance"=>$row["cal_running_balance"]);
        }
        $smarty->assign('adminfinlog', $adminfinlog);
    }
}
    
$smarty->assign('mid', 'coho_tiki-user_info.tpl');
$smarty->display("tiki.tpl");










?>