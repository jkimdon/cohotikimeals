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

$myurl = 'coho_meals-edit_meal.php';

$mealId = $_REQUEST["id"]; 
$smarty->assign( 'mealId', $mealId );
if ( !($mealId > 0) ) {
    $smarty->assign('msg', 'Empty meal id.');
    $smarty->display("error.tpl");
    die;
}

$mealtype = $_REQUEST["mealtype"];
$mealdatetime = $_REQUEST["mealdatetime"];
$smarty->assign( 'mealdatetime', $mealdatetime );

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


if ( $cohomeals->is_working( $mealId, $user ) || $is_meal_admin ) {

    $smarty->assign('allowed_to_edit', true);
    
    $mealinfo = array();
    $cohomeals->load_meal_info( $mealtype, $mealId, $mealinfo );
    $smarty->assign( 'meal', $mealinfo );
    // mealdatetime assigned earlier

    $crew_filled = array();
    $crew_open = array();
    $crew = $cohomeals->load_crew( $mealId );
    foreach ( $crew as $cm ) { 
        if ( $cm["has_volunteer"] ) {
            $crew_filled[] = array( "job"=>$cm["job"], "person"=>$cm["fullname"]);
        } else {
            $crew_open[] = array( "job"=>$cm["job"], "jobID"=>str_replace(' ','-',$cm["job"]), "id"=>$cm["username"]);
        }
    }
    $smarty->assign('crew_filled', $crew_filled);
    $smarty->assign('crew_open', $crew_open);
    
    
    $smarty->assign('mid', 'coho_meals-edit_meal.tpl');
    $smarty->display("tiki.tpl");

} else {
    $smarty->assign('msg', 'Not authorized to edit meal.');
    $smarty->display("error.tpl");
    die;
}

$nexturl = "coho_meals-view_entry.php?id=" . $mealId . "&mealdatetime=" . $mealdatetime;
header("Location: $nexturl");
die;

?>