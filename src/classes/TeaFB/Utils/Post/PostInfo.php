<?php

namespace TeaFB\Utils\Post;

use TeaFB\TeaFB;
use TeaFB\Exceptions\PostException;
use TeaFB\Contracts\Util as UtilContract;
use TeaFB\Contracts\SubUtil as SubUtilContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils\Post
 * @version 0.0.1
 */
final class PostInfo implements SubUtilContract
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
	 * @var string
	 */
	private $content;

	/**
	 * @var string
	 */
	private $link;

	/**
	 * @var array
	 */
	private $reactablePosts = [];

	/**
	 * @var string
	 */
	private $storyId;

	/**
	 * @var string
	 */
	private $reactionLink;

	/**
	 * @param \TeaFB\TeaFB $fb
	 * @param \TeaFB\Contracts\Util $util
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb, UtilContract $util)
	{
		$this->fb = $fb;
		$this->util = $util;
	}

	/**
	 * @param string $storyId
	 * @return void
	 */
	public function setStoryId(string $storyId): void
	{
		$this->link = $this->fb->getBaseUrl()."/".$storyId;
	}

	/**
	 * @return bool
	 */
	public function fetch(): bool
	{
		$this->setHtml(
			$this->fb->exec(
				$this->link,
				[
					CURLOPT_REFERER => $this->fb->getBaseUrl()."/home.php",
					CURLOPT_FOLLOWLOCATION => true
				]
			)->out
		);

		// // Debug only
		// $this->setHtml(file_get_contents("a.tmp"));
		return false;
	}

	/**
	 * @param string $html
	 * @throws \TeaFB\Exceptions\ProfileException
	 * @return void
	 */
	public function setHtml(string $html): void
	{
		if (preg_match("/(?:<title>)(.+)(?:<\/title>)/Usi", $html, $m)) {
			$this->content = ed($m[1]);

			if (preg_match("/(?:href=\")(\/reactions\/picker\/.+{$this->storyId}.+)(?:\")/Usi", $html, $m)) {
				$this->reactionLink = ed($m[1]);
			}
			return;
		}
		throw new PostException("Couldn't get the content");
	}

	/**
	 * @return array
	 */
	public function getReactablePosts(): array
	{
		return $this->reactablePosts;
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}
}
