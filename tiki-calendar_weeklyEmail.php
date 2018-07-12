<?php

$section = 'calendar';
require_once ('tiki-setup.php');
$access->check_feature('feature_calendar');

include_once ('lib/calendar/calendarlib.php');
include_once ('lib/calendar/calrecurrence.php');
include_once ('lib/webmail/tikimaillib.php');

$access->check_permission('tiki_p_view_calendar');

$startM = $_REQUEST['weekly_email_start_Month'];
$startD = $_REQUEST['weekly_email_start_Day'];
$startY = $_REQUEST['weekly_email_start_Year'];
$start_date = TikiLib::make_time(0,0,0, $startM, $startD, $startY);
$end_date = TikiLib::make_time(0,0,0, $startM, $startD+7, $startY);
$subject_end_date = TikiLib::make_time(0,0,0, $startM, $startD+6, $startY);
$smarty->assign('start_date', $start_date);
$smarty->assign('end_date', $end_date);
$smarty->assign('subject_end_date', $subject_end_date);

$printDay = array();
for ( $i = 0; $i < 7; $i++ ) {
  $current_date = TikiLib::make_time(0,0,0, $startM, $startD+$i, $startY);
  $printDay[$i]['day'] = $current_date;
}


/// birthdays are id 5
$calids = array();
$calids[0] = 5;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $end_date, 0, -1);
for ( $i=0; $i < 7; $i++ ) {
  $dday = $printDay[$i]['day'];

  if (isset($listevents["$dday"])) {
    foreach ($listevents["$dday"] as $le) {
      $printDay[$i]['birthdays'][] = $le['name'];
    }
  }
}




/// events/activities are id 8, meetings are id 3, meal program is id 1, LUV activities are 10, SAT are 11
$calids = array();
$calids[0] = 3;
$calids[1] = 8;
$calids[2] = 10;
$calids[3] = 11;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $end_date, 0, -1);
for ( $i=0; $i < 7; $i++ ) {
  $dday = $printDay[$i]['day'];
  $daysevents = array();
  if (isset($listevents["$dday"])) {
    $idnum = 0;
    foreach ($listevents["$dday"] as $le) {
      $event_info = array();
      $event_info['name'] = $le['name'];
      $event_info['description'] = $le['description'];
      $event_info['start'] = $le['startTimeStamp'];
      $event_info['end'] = $le['endTimeStamp'];
      $event_info['location'] = $le['location'];
      $id = $le['startTimeStamp'] . 'n' . $idnum;
      $daysevents[$id] = $event_info;
      $idnum++;
    }
  }
  ksort($daysevents);
  foreach ($daysevents as $de) {
    $printDay[$i]['events'][] = $de;
  }
}


/// meal program is id 1
$listevents = array();
$calendarlib->add_coho_meal_items($listevents, 1, $user, $start_date, $end_date);
for ( $i=0; $i < 7; $i++ ) {
  $dday = $printDay[$i]['day'];
  $daysevents = array();
  if (isset($listevents["$dday"])) {
    $idnum = 0;
    foreach ($listevents["$dday"] as $le) {
      $event_info = array();
      $event_info['name'] = $le['name'];
      $event_info['description'] = $le['description'];
      $event_info['start'] = $le['startTimeStamp'];
      $event_info['end'] = $le['startTimeStamp']; //$le['endTimeStamp']; want no end time displayed
      $event_info['location'] = $le['location'];
      $id = $le['startTimeStamp'] . 'n' . $idnum;
      $daysevents[$id] = $event_info;
      $idnum++;
    }
  }
  ksort($daysevents);
  foreach ($daysevents as $de) {
    $printDay[$i]['events'][] = $de;
  }
}




/// guest room is id 2
$calids = array();
$calids[0] = 2;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $end_date, 0, -1);
for ( $i=0; $i < 7; $i++ ) {
  $dday = $printDay[$i]['day'];
  if (isset($listevents["$dday"])) {
    foreach ($listevents["$dday"] as $le) {

      // only display visitors who will occupy the room on the night following the given day
      $display = false;
      $guest_start = $le['startTimeStamp'];
      $guest_end = $le['endTimeStamp'];
      if ($guest_start >= $printDay[$i]['day']) {
	$display = true;
      } else {
	if ($i<6) {
	  $tomorrow = $printDay[$i+1]['day'];
	} else {
	  $tomorrow = TikiLib::make_time(0,0,0, $startM, $startD+7, $startY);
	}
	if ($guest_end >= $tomorrow) {
	  $display = true;
	}
      }

      if ($display == true) {

	$event_info = array();
	$visitor = $le['name'];
	$host = "???";
	if (isset($le['result']['organizers'])) {
	  $host = $le['result']['organizers_realname'];
	}
	
	$event_info['visitor'] = $visitor;
	$event_info['host'] = $host;
	$event_info['start'] = $guest_start;
	$event_info['end'] = $guest_end;

	$printDay[$i]['guestroom'][] = $event_info;
      }
    }
  }
}


/// camping is id 12
$calids = array();
$calids[0] = 12;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $end_date, 0, -1);
for ( $i=0; $i < 7; $i++ ) {
  $dday = $printDay[$i]['day'];
  if (isset($listevents["$dday"])) {
    foreach ($listevents["$dday"] as $le) {

      // only display visitors who will occupy the campsite on the night following the given day
      $display = false;
      $guest_start = $le['startTimeStamp'];
      $guest_end = $le['endTimeStamp'];
      if ($guest_start >= $printDay[$i]['day']) {
	$display = true;
      } else {
	if ($i<6) {
	  $tomorrow = $printDay[$i+1]['day'];
	} else {
	  $tomorrow = TikiLib::make_time(0,0,0, $startM, $startD+7, $startY);
	}
	if ($guest_end >= $tomorrow) {
	  $display = true;
	}
      }

      if ($display == true) {

	$event_info = array();
	$visitor = $le['name'];
	$host = "???";
	if (isset($le['result']['organizers'])) {
	  $host = $le['result']['organizers_realname'];
	}
	
	$event_info['visitor'] = $visitor;
	$event_info['host'] = $host;
	$event_info['start'] = $guest_start;
	$event_info['end'] = $guest_end;

	$printDay[$i]['camping'][] = $event_info;
      }
    }
  }
}



$smarty->assign('printDay', $printDay);


/// visitors are id 6
// we look 5 weeks in the future for this category
$long_end_date = TikiLib::make_time(0,0,0, $startM, $startD+35, $startY);

$calids = array();
$calids[0] = 6;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $long_end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $long_end_date, 0, -1);
$visitors_tmp = array();
$i=0;
foreach ($listevents as $leDay) {
  foreach ($leDay as $le) {
    $id = 'c' . $le['calitemId'];
    if ( $id == 'c0' ) $id = 'r' . $le['recurrenceId'];

    if ( !array_key_exists($id,$visitors_tmp) ) {
      $visitors_tmp[$id]['startTimeStamp'] = $le['startTimeStamp'];
      $visitors_tmp[$id]['endTimeStamp'] = $le['endTimeStamp'];      
      $visitors_tmp[$id]['name'] = $le['name'];
      $visitors_tmp[$id]['description'] = $le['description'];
    }
  }
}
$visitors = array();
foreach ($visitors_tmp as $vi) {
  $visitors[] = $vi;
}
$smarty->assign('visitors', $visitors);


/// people who are or will be away are id 7
// we look 2 weeks in the future for this category
$calids = array();
$calids[0] = 7;
$listevents = $calendarlib->list_items($calids, $user, $start_date, $long_end_date, 0, -1);
$calendarlib->add_coho_recurrence_items($listevents, $calids, $user, $start_date, $long_end_date, 0, -1);
$absent_tmp = array();
$i=0;
foreach ($listevents as $leDay) {
  foreach ($leDay as $le) {
    $id = 'c' . $le['calitemId'];
    if ( $id == 'c0' ) $id = 'r' . $le['recurrenceId'];

    if ( !array_key_exists($id,$absent_tmp) ) {
      $absent_tmp[$id]['startTimeStamp'] = $le['startTimeStamp'];
      $absent_tmp[$id]['endTimeStamp'] = $le['endTimeStamp'];      
      $absent_tmp[$id]['name'] = $le['name'];
      $absent_tmp[$id]['description'] = $le['description'];
    }
  }
}
$absent = array();
foreach ($absent_tmp as $vi) {
  $absent[] = $vi;
}
$smarty->assign('absent', $absent);




$mail = new TikiMail();
$mail_data = $smarty->fetch("mail/weekly_calendar_subject.tpl");
$mail->setSubject($mail_data);
$mail_data = $smarty->fetch("mail/weekly_calendar_email.tpl");
$mail->setHtml($mail_data, strip_tags($mail_data));
//$mail->send(array($prefs['weekly_calendar_to_email']));
//$mail->send(array('jkimdon@gmail.com'));
//$smarty->display("mail/weekly_calendar_subject.tpl");
$smarty->display("mail/weekly_calendar_email.tpl");

header('Location: tiki-calendar.php?todate=' . $start_date);
exit;

?>