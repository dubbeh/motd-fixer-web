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
 */

// MySQL database info
define("MYSQL_DB_HOST", "localhost");
define("MYSQL_DB_NAME", "motdfix");
define("MYSQL_DB_USER", "user");
define("MYSQL_DB_PASS", "pass");

define("SERVERS_TABLE_NAME", "servers");
define("LINKS_TABLE_NAME", "links");

// Auth defines
define("AUTH_TOKEN_SIZE", "32");
define("AUTH_REGISTRATION", "1");
define("AUTH_IP", "2");

// Auth type
define("AUTH_TYPE", AUTH_REGISTRATION);

// Allowed IP's
define("AUTH_ALLOWED_IPS", array (
	"127.0.0.1",
	"192.168.0.1"));
?>
