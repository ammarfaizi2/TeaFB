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
	 * @var string
	 */
	private $nextReferer;

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
		$o = $this->fb->exec(
			$this->link,
			[
				CURLOPT_REFERER => $this->fb->getBaseUrl()."/home.php",
				CURLOPT_FOLLOWLOCATION => true
			]
		);

		$this->nextReferer = $o->info["url"];
		$this->setHtml($o->out);

		// // Debug only
		// $this->setHtml(file_get_contents("a.tmp"));
		return false;
	}

	/**
	 * @param string $html
	 * @throws \TeaFB\Exceptions\PostException
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
	 * @param bool $changeNextReferer
	 * @return array
	 */
	public function getReactList(bool $changeNextReferer = false): array
	{
		$o = $this->fb->exec($this->reactionLink, [CURLOPT_REFERER => $this->nextReferer]);
		if ($changeNextReferer) {
			$this->nextReferer = $o->info["url"];
		}
		if (preg_match_all("/(?:<a href=\")(\/ufi\/reaction\/.+)(?:\")(.+<\/table>)/Usi", $o->out, $m)) {
			unset($m[0]);
			$list = [];
			$removePtr = null;
			foreach ($m[2] as $k => $v) {
				if (preg_match("/(?:<span.*>)(.*)(?:<)/Usi", $v, $mm)) {
					$list[$react = strtoupper(ed($mm[1]))] = ed($m[1][$k]);
					if (preg_match("/\(remove\)/Usi", $v)) {
						$removePtr = $react;
					}
				}
			}
			unset($react, $m, $mm);
		}

		return [
			"remove_ptr" => $removePtr,
			"list" => $list
		];
	}

	/**
	 * @param string $reactEnum
	 * @throws \TeaFB\Exceptions\PostException
	 * @return int
	 */
	public function react(string $reactEnum): int
	{
		$reactEnum = strtoupper($reactEnum);

		if (
			$reactEnum !== React::LIKE &&
			$reactEnum !== React::LOVE &&
			$reactEnum !== React::HAHA &&
			$reactEnum !== React::WOW  &&
			$reactEnum !== React::SAD  &&
			$reactEnum !== React::ANGRY
		) {
			throw new PostException("Invalid reeaction {$reactEnum}");
		}

		$rl = $this->getReactList(true);

		if ($rl["remove_ptr"] === $reactEnum) {
			return React::HAS_ALREADY_BEEN_REACTED;
		}
		
		$this->exec($rl["list"][$reactEnum], [CURLOPT_REFERER => $this->nextReferer]);

		return React::OK;
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
