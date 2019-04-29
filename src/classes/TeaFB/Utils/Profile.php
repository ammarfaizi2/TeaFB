<?php

namespace TeaFB\Utils;

use TeaFB\TeaFB;
use TeaFB\Contract\Util as UtilContracts;
use TeaFB\Utils\ProfileVisitor\ProfileInfo;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils
 * @version 0.0.1
 */
final class Profile implements UtilContracts
{
	/**
	 * @var \TeaFB\TeaFB
	 */
	private $fb;

	/**
	 * @param \TeaFB\TeaFB $fb
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb)
	{
		$this->fb = $fb;
	}

	/**
	 * @param string $username
	 * @return \TeaFB\Utils\ProfileVisitor\ProfileInfo
	 */
	public function visit(string $username): ProfileInfo
	{
		$o = $this->fb->exec(
			$username,
			[
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_REFERER => $this->fb->getBaseUrl()."/home.php"
			]
		);
		$profileInfo = new ProfileInfo($this->fb, $this);
		$profileInfo->setHtml($o->out);
		return $profileInfo;
	}
}
