<?php

if (!defined("__TEA_FB_HELPERS")):

define("__TEA_FB_HELPERS", true);

/**
 * @param string $str
 * @return string
 */
function ed(string $str): string
{
	return html_entity_decode($str, ENT_QUOTES, "UTF-8");
}

endif;
