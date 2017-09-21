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

$_SERVER["PHP_SELF"] = "redirect.php";

echo "<html>".
"<head>".
"<meta http-equiv=\"Pragma\" content=\"no-cache\">".
"<meta http-equiv=\"Expires\" content=\"-1\">".
"<title>MOTD Redirection Service</title>".
"</head>".
"<body>".
"<h1>MOTD Redirection Service</h1></br></br>";

$auth = new MOTDAuth();
$auth->auth_run();
unset($auth);

echo "</body>".
"</html>";
?>
