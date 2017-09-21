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
// Set AUTH_REGISTRATION for token based authentication
// Set AUTH_IP for IP based authentication
define("AUTH_TYPE", AUTH_REGISTRATION);

// Set the allowed IP's here when using:
// define("AUTH_TYPE", AUTH_IP);
// This array is checked for the allowed IP's to to use the script
// Make sure to have PHP7+ to support defined arrays
// To check your external/internal server IP - Run "motdf_serverip" inside the SourceMod plugin and put it here
define("AUTH_ALLOWED_IPS", array (
	"127.0.0.1",
	"192.168.0.1"));

// Enable debug logging to motdf_log.txt in the base directory
define("MOTDF_DEBUG", false);
?>
