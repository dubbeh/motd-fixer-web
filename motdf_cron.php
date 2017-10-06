<?php

/*
 * MOTD Fixer Server Script
 *
 * Fixes the MOTD loading of data under Counter-Strike : Global Offensive
 *
 * Coded by dubbeh - www.dubbeh.net
 *
 * Licensed under the GPLv3
 *
 * Add this to your crontab using the PHP-CLI on a 5 minute cycle
 *
 * This will keep the database clean and free from bloating
 *
 * WARNING: KEEP THIS OUTSIDE YOUR ACCESSIBLE WEBSPACE FOLDER
 *
 * Make sure to set permissions to 0755
 *
 */

require_once ("config.php");
require_once ("inc/db.php");


$db = new MOTDDB();
printf("Running the MOTD Fixer database clean up.\r\n");

// Cleanup old link entries - Anything older than 5 minutes
$count = $db->query("DELETE FROM ".LINKS_TABLE_NAME.
	" WHERE".
	" created_at <= :created_at")
	->bind (":created_at", time() + 300)
    ->executeRows();

printf("Deleted %d old link entries from the database\r\n", $count);

unset($db);
?>