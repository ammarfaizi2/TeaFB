<?php

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/ammarfaizi2.php";

use TeaFB\TeaFB;
use TeaFB\Utils\Post;
use TeaFB\Utils\Profile;
use TeaFB\Utils\Post\React;

$fb = new TeaFB($email, $password, $cookieFile);
if ($fb->login() === $fb::LOGIN_OK) {
	$post = new Post($fb);
	$profile = new Profile($fb);
	// foreach($profile->visit("ammarfaizi2")->getReactablePosts() as $storyId) {
	// 	print json_encode($post->visit($storyId)->getContent())."\n";
	// }

	$posts = $profile->visit("ammarfaizi2")->getReactablePosts();
	$post->visit($posts[1])->react(React::WOW);

} else {
	printf("Login failed!\n");
}
