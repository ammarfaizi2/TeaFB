<?php

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/ammarfaizi2.php";

$fb = new TeaFB\TeaFB($email, $password, $cookieFile);
if ($fb->login()) {
	
} else {
	printf("Login failed!\n");
}
