<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: webmail.php 64628 2017-11-19 12:03:08Z rjsmelo $

function prefs_webmail_list()
{
	return [
		'webmail_view_html' => [
			'name' => tra('Allow viewing HTML emails?'),
			'type' => 'flag',
			'default' => 'y',
		],
		'webmail_max_attachment' => [
			'name' => tra('Maximum size for each attachment'),
			'type' => 'list',
			'options' => [
				'500000' => tra('500Kb'),
				'1000000' => tra('1Mb'),
				'1500000' => tra('1.5Mb'),
				'2000000' => tra('2Mb'),
				'2500000' => tra('2.5Mb'),
				'3000000' => tra('3Mb'),
				'100000000' => tra('Unlimited'),
			],
			'default' => 1500000,
		],
		'webmail_quick_flags' => [
			'name' => tra('Include a flag by all messages to quickly flag/unflag them?'),
			'type' => 'flag',
			'default' => 'n',
		],
	];
}
