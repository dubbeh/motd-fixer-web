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

require_once ("../inc/auth.php");

$_SERVER["PHP_SELF"] = "delete.php";

$auth = new MOTDAuth();
$auth->auth_run();
unset($auth);

?>
