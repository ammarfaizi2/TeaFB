<?php

namespace TeaFB\Utils;

use TeaFB\TeaFB;
use TeaFB\Contracts\SubUtil as SubUtilContracts;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils\Profile
 * @version 0.0.1
 */
final class ProfileInfo implements SubUtilContracts
{
	/**
	 * @var \TeaFB\TeaFB
	 */
	private $fb;

	/**
	 * @var \TeaFB\Contracts\Util
	 */
	private $util;

	/**
	 * @param \TeaFB\TeaFB $fb
	 * @param \TeaFB\Contracts\Util $util
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb, UtilsContract $util)
	{
		$this->fb = $fb;
		$this->util = $util;
	}
}
