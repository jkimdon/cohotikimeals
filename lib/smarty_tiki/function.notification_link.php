<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: function.notification_link.php 64630 2017-11-19 12:11:11Z rjsmelo $

function smarty_function_notification_link($params)
{
	global $user, $prefs;

	if ($prefs['monitor_enabled'] != 'y') {
		return;
	}

	if (! $user) {
		return '';
	}

	$servicelib = TikiLib::lib('service');

	$smarty = TikiLib::lib('smarty');
	$smarty->assign('monitor_link', $params);
	return $smarty->fetch('monitor/notification_link.tpl');
}
