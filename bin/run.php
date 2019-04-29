<?php

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/ammarfaizi2.php";

use TeaFB\TeaFB;
use TeaFB\Utils\Profile;

$fb = new TeaFB($email, $password, $cookieFile);
if ($fb->login() === $fb::LOGIN_OK) {
	$profileVisitor = new Profile($fb);
	$o = $profileVisitor->visit("ammarfaizi2");
} else {
	printf("Login failed!\n");
}
