<?php

namespace TeaFB\Utils\Profile;

use TeaFB\TeaFB;
use TeaFB\Exceptions\ProfileException;
use TeaFB\Contracts\Util as UtilContract;
use TeaFB\Contracts\SubUtil as SubUtilContract;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils\Profile
 * @version 0.0.1
 */
final class ProfileInfo implements SubUtilContract
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
	private $name;

	/**
	 * @var string
	 */
	private $link;

	/**
	 * @var array
	 */
	private $reactablePosts = [];

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
	 * @param string $username
	 * @return void
	 */
	public function setUsername(string $username): void
	{
		$this->link = $this->fb->getBaseUrl()."/".$username;
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
			$this->name = ed($m[1]);
			if (preg_match_all("/(?:id=\"like_)(\d+)(?:\")/Usi", $html, $m)) {
				foreach ($m[1] as $storyId) {
					$this->reactablePosts[] = $storyId;
				}
			}
			return;
		}
		throw new ProfileException("Couldn't get the profile name");
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
	public function getName(): string
	{
		return $this->name;
	}
}
