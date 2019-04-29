<?php

namespace TeaFB\Contracts;

use TeaFB\TeaFB;
use TeaFB\Contracts\Util as UtilContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @version 0.0.1
 * @package \TeaFB\Contracts
 */
interface SubUtil
{
	/**
	 * @param \TeaFB\TeaFB $fb
	 * @param \TeaFB\Contracts\Util
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb, UtilContract $util);
}
