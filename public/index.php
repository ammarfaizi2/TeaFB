<?php

$user = "ammarfaizi2";

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/{$user}.php";

use TeaFB\TeaFB;
use TeaFB\BrowserStream;

$fb = new TeaFB($email, $password, $cookieFile);
$browserStream = new BrowserStream($fb);
$browserStream->run();
