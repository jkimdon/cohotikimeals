<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}


// to be inserted at the end of the set_user_preference list in tiki-user_preferences.php

     if (isset($_REQUEST["billingGroup"]) ) {
       $tikilib->set_user_preference($userwatch, 'billingGroup', $_REQUEST["billingGroup"]);
     }
     if (isset($_REQUEST["birth_Month"]) && isset($_REQUEST["birth_Day"]) && isset($_REQUEST["birth_Year"]) ) {
       $new_birthdate = TikiLib::make_time(0,0,0, $_REQUEST['birth_Month'], 
					   $_REQUEST['birth_Day'],
					   $_REQUEST['birth_Year']);

       $tikilib->set_user_preference($userwatch, 'birthdate', $new_birthdate);
     }
     if (isset($_REQUEST["unitNumber"]) ) {
       $tikilib->set_user_preference($userwatch, 'unitNumber', $_REQUEST["unitNumber"]);
     }
     if (isset($_REQUEST["in_meal_program"]) ) {
       $tikilib->set_user_preference($userwatch, 'in_meal_program', $_REQUEST["in_meal_program"]);
     }

?>