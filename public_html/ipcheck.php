<?php
require_once ("../inc/auth.php");

$motdh = new MOTDHelpers();
printf($motdh->get_real_ip());
unset($motdh);
?>
