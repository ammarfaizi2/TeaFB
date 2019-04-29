<?php

namespace TeaFB\Utils;

use TeaFB\TeaFB;
use TeaFB\Utils\Post\PostInfo;
use TeaFB\Exceptions\PostException;
use TeaFB\Contracts\Util as UtilContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils
 * @version 0.0.1
 */
final class Post implements UtilContract
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
	 * @param string $storyId
	 * @return \TeaFB\Utils\Post\PostInfo
	 */
	public function visit(string $storyId): PostInfo
	{
		$post = new PostInfo($this->fb, $this);
		$post->setStoryId($storyId);
		$post->fetch();
		return $post;
	}
}
