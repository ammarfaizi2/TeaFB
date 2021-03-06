<?php

namespace TeaFB\Utils;

use TeaFB\TeaFB;
use TeaFB\Utils\Profile\ProfileInfo;
use TeaFB\Exceptions\ProfileException;
use TeaFB\Contracts\Util as UtilContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils
 * @version 0.0.1
 */
final class Profile implements UtilContract
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
		$profileInfo = new ProfileInfo($this->fb, $this);
		$profileInfo->setUsername($username);
		$profileInfo->fetch();
		return $profileInfo;
	}
}
