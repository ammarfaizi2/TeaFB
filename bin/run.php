<?php

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/ammarfaizi2.php";

use TeaFB\TeaFB;
use TeaFB\Utils\Post;
use TeaFB\Utils\Profile;
use TeaFB\Utils\Post\React;

$fb = new TeaFB($email, $password, $cookieFile);
if ($fb->login() === $fb::LOGIN_OK) {
	$reactChooser = function ($reacts) {
		$reacts = [];
		foreach ($react as $k => $v) {
			for ($i=0; $i < $v; $i++) { 
				$reacts[] = $v;
			}
		}
		return $reacts[rand(0, count($reacts) - 1)];
	};
	$post = new Post($fb);
	$profile = new Profile($fb);
	$target = json_decode(file_get_contents(__DIR__."/storage/{$username}/target.json"), true);
	foreach ($target as $username => $v) {
		print "Visiting target profile: {$username}...\n";
		foreach($profile->visit($username)->getReactablePosts() as $storyId) {
			print "Visiting target's post: {$storyId}...\n";
			$postInfo = $post->visit($storyId);
			$react = $reactChooser($v);
			print "Decided to use {$react} react.\n";
			print "Reacting {$storyId}...";
			$postInfo->react($react);
			print "OK\n";
		}
	}

} else {
	printf("Login failed!\n");
}
