<?php

namespace TeaFB;

use stdClass;
use Exception;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB
 * @version 0.0.1
 */
final class TeaFB
{
	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $cookieFile;

	/**
	 * @var string
	 */
	private $baseUrl = "https://m.facebook.com";

	/**
	 * @var string
	 */
	private $userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0";

	/**
	 * @var array
	 */
	private $proxyOpt = [];

	/**
	 * @const int
	 */
	public const LOGIN_OK = (1 << 0);

	/**
	 * @const int
	 */
	public const LOGIN_FAILED = (1 << 1);

	/**
	 * @const int
	 */
	public const LOGIN_CHECKPOINT = (1 << 2);

	/**
	 * @param string $email
	 * @param string $password
	 * @param string $cookieFile
	 * @throws \Exception
	 *
	 * Constructor.
	 */
	public function __construct(string $email, string $password, string $cookieFile = null)
	{
		$this->email = $email;
		$this->password = $password;
		if (is_string($cookieFile)) {
			$this->cookieFile = $cookieFile;
		} else {
			$this->cookieFile = defined("COOKIE_DIR") ? 
				COOKIE_DIR."/".md5($this->email.$this->password) :
				getcwd()."/".md5($this->email.$this->password);
		}
		touch($this->cookieFile);
		if (!file_exists($this->cookieFile)) {
			throw new Exception("Cannot create cookie file: {$this->cookieFile}");
		}
	}

	/**
	 * @return int
	 */
	public function login(): int
	{
		$o = $this->exec("login.php");

		// // Put debug flag.
		// file_put_contents("a.tmp", $o->out);

		// // Use debug flag.
		// $o = new stdClass;
		// $o->out = file_get_contents("a.tmp");

		$m = [];
		if (preg_match("/(?:<form.+action=\")(.+)(?:\")/Usi", $o->out, $m)) {
			$action = ed($m[1]);
			$post = [];
			if (preg_match_all("/<input[^\>\<]+type=\"hidden\"[^\>\<]+>/Usi", $o->out, $m)) {
				foreach ($m[0] as $k => $v) {
					if (preg_match("/(?:name=\")(.+)(?:\")/Usi", $v, $m)) {
						$name = ed($m[1]);
						if (preg_match("/(?:value=\")(.+)(\")/Usi", $v, $m)) {
							$post[$name] = ed($m[1]);
						} else {
							$post[$name] = "";
						}
					}
				}
				$post["email"] = $this->email;
				$post["pass"] = $this->password;
				$post["login"] = "Log In";
				unset($name, $m, $k, $v);
				$this->exec(
					$action,
					[
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => http_build_query($post),
						CURLOPT_REFERER => $o->info["url"]
					]
				);
			}
		}

		$o = file_get_contents($this->cookieFile);

		if (preg_match_all("/c_user/", $o)) {
			return self::LOGIN_OK;
		}

		if (preg_match("/checkpoint/", $o)) {
			return self::LOGIN_CHECKPOINT;
		}

		return self::LOGIN_FAILED;
	}

	/**
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	/**
	 * @param string $url
	 * @return void
	 */
	public function setBaseUrl(string $url): void
	{
		$this->baseUrl = $url;
	}

	/**
	 * @param string $userAgent
	 * @return void
	 */
	public function setUserAgent(string $userAgent): void
	{
		$this->userAgent = $userAgent;
	}

	/**
	 * @param string $proxy
	 * @param string $proxyType
	 * @return void
	 */
	public function setProxy(string $proxy, int $proxyType = CURLPROXY_SOCKS5): void
	{
		$this->proxyOpt[CURLOPT_PROXY] = $proxy;
		$this->proxyOpt[CURLOPT_PROXYTYPE] = $proxyType;
	}

	/**
	 * @return void
	 */
	public function unsetProxy(): void
	{
		unset($this->proxyOpt);
	}

	/**
	 * @param string $url
	 * @param array  $opt
	 * @return \stdClass
	 */
	public function exec(string $url, array $opt = []): stdClass
	{
		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$url = $this->baseUrl."/".ltrim($url, "/");
		}
		$ch = curl_init($url);
		$optf = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_CONNECTTIMEOUT => 60,
			CURLOPT_COOKIEFILE => $this->cookieFile,
			CURLOPT_COOKIEJAR => $this->cookieFile,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_FOLLOWLOCATION => false
		];
		if (isset($this->proxyOpt) && is_array($this->proxyOpt)) {
			foreach ($this->proxyOpt as $key => $value) {
				$optf[$key] = $value;
			}
		}
		foreach ($opt as $key => $value) {
			$optf[$key] = $value;
		}
		unset($opt, $key, $value, $url);
		curl_setopt_array($ch, $optf);
		$o = new stdClass;
		$o->out = curl_exec($ch);
		$o->info = curl_getinfo($ch);
		$o->error = curl_error($ch);
		$o->errno = curl_errno($ch);
		curl_close($ch);
		return $o;
	}
}
