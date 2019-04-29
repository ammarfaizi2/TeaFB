<?php

namespace TeaFB\Utils\Post;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaFB\Utils\Post
 * @version 0.0.1
 */
final class React
{
	public const LIKE = "LIKE";
	public const LOVE = "LOVE";
	public const HAHA = "HAHA";
	public const WOW = "WOW";
	public const SAD = "SAD";
	public const ANGRY = "ANGRY";

	public const HAS_ALREADY_BEEN_REACTED = (1 << 0);
	public const OK = (1 << 0);
}
