<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}


// to be inserted at the end of the set_user_preference list in tiki-user_preferences.php

     if (isset($_REQUEST["unitNumber"]) ) {
       $tikilib->set_user_preference($userwatch, 'unitNumber', $_REQUEST["unitNumber"]);
     }

// deal with billingGroup (only set name not billing group id)

// deal with meal_multiplier

?>