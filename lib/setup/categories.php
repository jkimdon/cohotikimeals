<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: categories.php 64633 2017-11-19 12:25:47Z rjsmelo $

if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
	die('This script may only be included.');
}

if ($prefs['feature_categories'] == 'y' && $prefs['categories_used_in_tpl'] == 'y') {
	$categlib = TikiLib::lib('categ');
	// pick up the objectType from cat_type is set or from section
	if (! empty($section) && ! empty($sections) && ! empty($sections[$section])) {
		$here = $sections[$section];
		if (isset($_REQUEST[$here['key']])) {
			if (is_array($_REQUEST[$here['key']])) { // tiki-upload_file uses galleryId[]
				$key = $_REQUEST[$here['key']][0];
			} else {
				$key = $_REQUEST[$here['key']];
			}
		}
		if (isset($here['itemkey']) && isset($_REQUEST[$here['itemkey']]) && isset($here['itemObjectType'])) {
			if (strstr($here['itemObjectType'], '%') && isset($_REQUEST[$here['key']])) {
				$objectType = sprintf($here['itemObjectType'], $key);
			} else {
				$objectType = $here['itemObjectType'];
			}
		} elseif (isset($here['key']) && isset($_REQUEST[$here['key']]) && isset($here['objectType'])) {
			$objectType = $here['objectType'];
		}
	}
	$objectCategoryIds = [];
	$objectCategoryIdsNoJail = [];
	if (! empty($objectType)) {
		if (isset($here['itemkey']) && isset($_REQUEST[$here['itemkey']]) && isset($here['itemObjectType'])) {
			$objectCategoryIds = $categlib->get_object_categories($objectType, $_REQUEST[$here['itemkey']]);
			$objectCategoryIdsNoJail = $categlib->get_object_categories($objectType, $_REQUEST[$here['itemkey']], -1, false);
		} elseif (isset($here['key']) && isset($_REQUEST[$here['key']])) {
			if ($objectType === 'wiki page' && $prefs['wiki_url_scheme'] !== 'urlencode') {
				$key = TikiLib::lib('wiki')->get_page_by_slug($key);
			}
			$objectCategoryIds = $categlib->get_object_categories($objectType, $key);
			$objectCategoryIdsNoJail = $categlib->get_object_categories($objectType, $key, -1, false);
		}
	}
	$smarty->assign_by_ref('objectCategoryIds', $objectCategoryIds);
	// use in smarty {if isset($objectCategoryIds) and in_array(54, $objectCategoryIds)} My stuff ..{/if}
}
