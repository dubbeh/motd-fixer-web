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
 */

require_once ("inc/auth.php");

$db = new MOTDDB();
$server = new MOTDServer($db, null);

$server->cleanup_db();

unset($server);
unset($db);
?>