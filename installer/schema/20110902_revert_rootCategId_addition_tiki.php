<?php
// (c) Copyright 2002-2016 by authors of the Tiki Wiki CMS Groupware Project
//
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: 20110902_revert_rootCategId_addition_tiki.php 64614 2017-11-17 23:30:13Z rjsmelo $

if (strpos($_SERVER["SCRIPT_NAME"], basename(__FILE__)) !== false) {
	header("location: index.php");
	exit;
}

/**
 * @param $installer
 */
function upgrade_20110902_revert_rootCategId_addition_tiki($installer)
{
	$result = $installer->fetchAll("SHOW COLUMNS FROM `tiki_categories` WHERE `Field`='rootCategId'");
	if ($result) {
		$result = $installer->query("ALTER TABLE `tiki_categories` DROP COLUMN `rootCategId`;");
	}
}
