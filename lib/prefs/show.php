<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: show.php 64628 2017-11-19 12:03:08Z rjsmelo $

function prefs_show_list()
{
	return [
		'show_available_translations' => [
			'name' => tra('Display available translations'),
			'description' => tra('Display list of available languages and offer to switch languages or translate. This appears on wiki pages and articles action buttons.'),
			'type' => 'flag',
			'default' => 'y',
		],
	];
}
