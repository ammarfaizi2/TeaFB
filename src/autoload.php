<?php

if (!defined("__TEA_FB_AUTOLOADER")):

define("__TEA_FB_AUTOLOADER", true);

/**
 * @param string $class
 * @return void
 */
function teaFBInternalAutoloader(string $class): void
{
	if (file_exists($file = __DIR__."/classes/".str_replace("\\", "/", $class).".php")) {
		require $file;
	}
}

spl_autoload_register("teaFBInternalAutoloader");

require __DIR__."/helpers.php";

endif;
