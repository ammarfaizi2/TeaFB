<?php

namespace TeaFB\Contracts;

use TeaFB\TeaFB;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \TeaFB\Contracts
 */
interface Util
{
	/**
	 * @param \TeaFB\TeaFB $fb
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb);
}
