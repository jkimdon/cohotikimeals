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

if (isset( $_REQUEST["todo"] ) ) $todo = $_REQUEST["todo"];
else $todo = 'edit';

$mealId = $_REQUEST["id"]; 
$smarty->assign( 'mealId', $mealId );
if ( !($mealId > 0) ) {
    $smarty->assign('msg', 'Empty meal id.');
    $smarty->display("error.tpl");
    die;
}

$mealtype = $_REQUEST["mealtype"];
$mealdatetime = $_REQUEST["mealdatetime"];

if ( $todo == 'confirm' ) {
    $nexturl = "coho_meals-meal_summary_handler.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
    if ( isset($_REQUEST["walkin"] ) ) {
        $walkin = $_REQUEST["walkin"];
        foreach( $walkin as $w ) {
            $nexturl .= "&walkin[]=" . $w;
        }
    }
    if ( isset($_REQUEST["newguest"] ) ) {
        $arrayname = $_REQUEST["newguest"];
        $multiplier = $_REQUEST["multiplier"];                
        $host = $_REQUEST["host"];
        $i=0;
        foreach( $arrayname as $arrayval ) {
            if ( $arrayval != '' ) {
                $gueststring = preg_replace('/\s+/', '+', $arrayval);
                $nexturl .= "&newguest[]=" . $gueststring;
                $mult = $multiplier[$i];
                if ( !is_numeric( $mult ) ) $mult = 1;
                $nexturl .= "&multiplier[]=" . $mult;
                $thishost = $host[$i];
                if ( !is_string( $thishost ) ) {
                    $smarty->assign('msg', 'Error saving host.');
                    $smarty->display("error.tpl");
                    die;
                }
                $nexturl .= "&host[]=" . $thishost;                
            }
            $i++;
        }
    }
    if ( isset($_REQUEST["shopper"] ) ) {
        $dollars = $_REQUEST["dollars"];
        $cents = $_REQUEST["cents"];
        $vendor = $_REQUEST["vendor"];
        $i=0;
        $arrayname = $_REQUEST["shopper"];
        foreach( $arrayname as $arrayval ) {
            if ( $arrayval != 'none' ) {
                $nexturl .= "&shopper[]=" . $arrayval;
                $nexturl .= "&dollars[]=" . $dollars[$i];
                $nexturl .= "&cents[]=" . $cents[$i];
                $vendorstring = preg_replace('/\s+/', '+', $vendor[$i]);
                $nexturl .= "&vendor[]=" . $vendorstring;
            }
            $i++;
        }
    }
    if ( isset($_REQUEST["farmersDollars"]) ) {
        $nexturl .= "&farmersDollars=" . $_REQUEST["farmersDollars"];
        $nexturl .= "&farmersCents=" . $_REQUEST["farmersCents"];
    }
    
    $pantryfoods = $cohomeals->load_pantry_foods(); 
    $amounts = array();
    $i=0;
    foreach( $pantryfoods as $food ) {
        $key = "amount" . $food["id"];
        if ( (isset( $_REQUEST[$key] )) && ($_REQUEST[$key] != 0) ) {
            $amt = $_REQUEST[$key];
           $nexturl .= "&" . $key . "=" . $amt;
        }
        $i++;
    }
    header("Location: $nexturl");
    die;
} elseif ( $todo != 'edit' ) {
    $smarty->assign('msg', 'Error editing meal summary.');
    $smarty->display("error.tpl");
    die;
}


// if recurring, make a new overriding meal with same as recurring and then recall this handler on the new meal to make the changes
if ( $mealtype == "recurring" ) {
    $newMealId = $cohomeals->create_override_from_recurrence( $mealId, $mealdatetime );
    if (!$newMealId) {
        $smarty->assign('msg', 'Error creating meal.');
        $smarty->display("error.tpl");
        die;
    }
    header("Location: " . $myurl . "?id=" . $newMealId . "&mealdatetime=" . $mealdatetime . "&mealtype=regular");
    die;
}

$allowed_to_edit = false;
//if ( $is_meal_admin ) $allowed_to_edit = true;
//if ( $cohomeals->has_head_chef( $mealId ) == $user ) $allowed_to_edit = true;
$allowed_to_edit = true; // let's let anybody with a meal account do the meal summary
$smarty->assign( 'allowed_to_edit', $allowed_to_edit );

// if already non-recurring meal, we can get on with it
if ( $mealtype == "regular" ) {
    $paperwork_done = $cohomeals->paperwork_done( $mealId );
    $smarty->assign( 'paperwork_done', $paperwork_done );
    if ( !$paperwork_done && $allowed_to_edit ) {

        $mealinfo = array();
        $cohomeals->load_meal_info( "regular", $mealId, $mealinfo );
        $smarty->assign( 'meal', $mealinfo );
        $smarty->assign( 'mealdatetime', $mealinfo["mealdatetime"]->format('U') );

        if (isset($_REQUEST["walkin"])) $walkins = $_REQUEST["walkin"]; 
        else $walkins = [];
        $mealpeople = array();
        $allusers = $cohomeals->getAllMealUsers();
        foreach ($allusers as $person) {
            $person_realname = $person["realName"];
            $person_username = $person["username"];
            $status = '';
            if ($cohomeals->is_dining( $person_username, $mealId )) $status = "disabled";
            else {
                $iswalkin = false;
                foreach ( $walkins as $walkin ) { // assuming not many walkins so no great expense to loop
                    if ( $walkin == $person_username ) $iswalkin = true;
                }
                if ( $iswalkin ) $status = "checked";
            }
            $mealpeople[] = array( "username" => $person_username, "realName" => $person_realname, "status" => $status );
        }
        $smarty->assign('mealpeople', $mealpeople);
        $smarty->assign('formfiller', $user);
        
        $guest_diners = $cohomeals->load_guests( $mealId );
        $smarty->assign( 'guest_diners', $guest_diners );

        $newguest = $_REQUEST["newguest"];
        $multiplier = $_REQUEST["multiplier"];
        $host = $_REQUEST["host"];
        $i = 0;
        $confirmingguests = array();
        foreach( $newguest as $ng ) {
            $confirmingguests[] = array("name"=>$ng, "multiplier"=>$multiplier[$i], "host"=>$host[$i]);
            $i++;
        }
        $smarty->assign( 'confirmingguests', $confirmingguests );

        $newshopper = $_REQUEST["shopper"];
        $newdollars = $_REQUEST["dollars"];
        $newcents = $_REQUEST["cents"];
        $newvendor = $_REQUEST["vendor"];
        $confirmingshoppers = array();
        $i=0;
        foreach( $newshopper as $ns ) {
            $confirmingshoppers[] = array("username"=>$newshopper[$i], "dollars"=>$newdollars[$i], "cents"=>$newcents[$i], "vendor"=>$newvendor[$i]);
            $i++;
        }
        $smarty->assign( 'confirmingshoppers', $confirmingshoppers );

        $farmersDollars = "";
        if (isset($_REQUEST["farmersDollars"])) $farmersDollars = $_REQUEST["farmersDollars"];
        $farmersCents = "";
        if (isset($_REQUEST["farmersCents"])) $farmersCents = $_REQUEST["farmersCents"];
        $smarty->assign( 'farmersDollars', $farmersDollars );
        $smarty->assign( 'farmersCents', $farmersCents );

        $allfoods = $cohomeals->load_pantry_foods(); 
        // enter amounts if confirming
        $pantryfoods = array();
        foreach( $allfoods as $food ) {
            $key = "amount" . $food["id"];
            if (isset( $_REQUEST[$key] ) ) $amt = $_REQUEST[$key];
            else $amt = 0;
            if ( $amt != 0 ) {
                $pantryfoods[] = array("amount"=>$amt, "id"=>$food["id"], "name"=>$food["name"], "category"=>$food["category"], "unit"=>$food["unit"] );
            }
            else {
                $pantryfoods[] = array("id"=>$food["id"], "name"=>$food["name"], "category"=>$food["category"], "unit"=>$food["unit"] );
            }
        }
        $smarty->assign( 'pantryfoods', $pantryfoods );        
        
    }

    $smarty->assign('mid', 'coho_meals-edit_meal_summary.tpl');
    $smarty->display("tiki.tpl");
}
else { // something went wrong
    $nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
    header("Location: $nexturl");
    die;
}

?>