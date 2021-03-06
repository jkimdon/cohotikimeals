<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: multidomain.php 64628 2017-11-19 12:03:08Z rjsmelo $

function prefs_multidomain_list()
{
	return [
		'multidomain_active' => [
			'name' => tra('Multi-domain'),
			'description' => tra('Allows domain names to be mapped to perspectives and simulate multiple domains hosted with the same Tiki installation.'),
			'perspective' => false,
			'help' => 'Multi-Domain',
			'type' => 'flag',
			'dependencies' => [
				'feature_perspective',
			],
			'default' => 'n',
		],
		'multidomain_config' => [
			'name' => tra('Multi-domain Configuration'),
			'description' => tra('Comma-separated values mapping the domain name to the perspective ID.'),
			'perspective' => false,
			'type' => 'textarea',
			'size' => 10,
			'hint' => tra('One domain per line with a comma separating it from the perspective ID. For example: tiki.org,1'),
			'default' => '',
		],
		'multidomain_switchdomain' => [
			'name' => tra('Switch domain when switching perspective'),
			'description' => tra('Remember that different domains have different login sessions and even in the case of subdomains you need to have an understanding of session cookies to make it work'),
			'tags' => ['advanced'],
			'type' => 'flag',
			'dependencies' => [
				'feature_perspective', 'multidomain_active'
			],
			'default' => 'n',
		],
	];
}
