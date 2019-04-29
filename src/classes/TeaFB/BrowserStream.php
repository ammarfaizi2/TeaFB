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
final class BrowserStream
{	
	/**
	 * @const
	 */
	private const ROUTER_QUERY_STRING = (1 << 0);

	/**
	 * @const
	 */
	private const ROUTER_URI = (1 << 1);

	/**
	 * @var \TeaFB\TeaFB
	 */
	private $fb;

	/**
	 * @var int
	 */
	private $routerType = self::ROUTER_QUERY_STRING;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var array
	 */
	private $opt = [];

	/**
	 * @var int
	 */
	private $bufPtr = 0;

	/**
	 * @var array
	 */
	private $headers = [];

	/**
	 * @var string
	 */
	private $contentType;

	/**
	 * @param \TeaFB\TeaFB $fb
	 *
	 * Constructor.
	 */
	public function __construct(TeaFB $fb)
	{
		if (preg_match("/^\/sem_pixel/USsi", $_SERVER["REQUEST_URI"])) {
			http_response_code(400);
			exit;
		}
		$this->fb = $fb;
		$this->opt[CURLOPT_HEADER] = true;
	}

	/**
	 * @return void
	 */
	public function run(): void
	{
		$this->getRequest();
		$this->forwardRequest();
	}

	/**
	 * @return void
	 */
	private function getRequest(): void
	{
		if ($this->routerType === self::ROUTER_QUERY_STRING) {
			if (isset($_GET["url"]) && is_string($_GET["url"])) {
				$this->url = rawurldecode($_GET["url"]);
			} else {
				$this->url = "";
			}

			if ($_SERVER["REQUEST_METHOD"] !== "GET") {
				$this->opt[CURLOPT_CUSTOMREQUEST] = $_SERVER["REQUEST_METHOD"];
				$this->opt[CURLOPT_POSTFIELDS] = file_get_contents("php://input");
			}
		}
	}

	/**
	 * @param string &$res
	 * @return void
	 */
	private function buildHeader(string &$res): void
	{
		$res = explode("\r\n\r\n", $res);
		$this->headers = explode("\r\n", trim($res[0]));
		$headersToBeSend = [
			"location",
			"x-fb-debug",
			"connection",
			"content-type"
		];
		$this->contentType = "text/html";
		foreach ($this->headers as $k => $v) {
			$v = explode(":", $v, 2);
			if (in_array($vs = strtolower($v[0]), $headersToBeSend)) {
				$v[1] = trim($v[1]);
				if ($this->routerType === self::ROUTER_QUERY_STRING && $vs === "location") {
					$me = $_GET;
					unset($me["url"]);
					$v[1] = "?url=".urlencode(rawurlencode($v[1]))."&".http_build_query($me);
				}
				if ($vs === "content-type") {
					if (preg_match("/text\/html/", $v[1])) {
						$this->contentType = "text";
					} else {
						$this->contentType = "bin";
					}
				}
				header("{$v[0]}: {$v[1]}");
			}
		}
		$res = $res[1];
	}

	/**
	 * @return void
	 */
	private function forwardRequest(): void
	{
		$o = $this->fb->exec($this->url, $this->opt);
		$this->buildHeader($o->out);
		$this->replacer($o->out);
		echo $o->out;
	}

	/**
	 * @param string &$res
	 * @return void
	 */
	private function replacer(string &$res): void
	{
		$me = $_GET;
		unset($me["url"]);
		$upquery = "&".http_build_query($me);
		if (strlen($upquery) === 1) {
			$upquery = "";
		}
		if (preg_match_all("/(?:href=\")(.+)(?:\")/Usi", $res, $m)) {
			$r1 = $r2 = [];
			foreach ($m[1] as $url) {
				$r1[] = "href=\"".$url."\"";
				$r2[] = "href=\"?url=".urlencode(rawurlencode(ed($url))).$upquery."\"";
			}
			$res = str_replace($r1, $r2, $res);
		}

		if (preg_match_all("/(?:action=\")(.+)(?:\")/Usi", $res, $m)) {
			$r1 = $r2 = [];
			foreach ($m[1] as $url) {
				$r1[] = "action=\"".$url."\"";
				$r2[] = "action=\"?url=".urlencode(rawurlencode(ed($url))).$upquery."\"";
			}
			$res = str_replace($r1, $r2, $res);
		}

		if (preg_match_all("/(?:src=\")(.+)(?:\")/Usi", $res, $m)) {
			$r1 = $r2 = [];
			foreach ($m[1] as $url) {
				$r1[] = "src=\"".$url."\"";
				$r2[] = "src=\"?url=".urlencode(rawurlencode(ed($url))).$upquery."\"";
			}
			$res = str_replace($r1, $r2, $res);
		}
	}
}
