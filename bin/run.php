<?php

if (!isset($argv[1])) {
	print "argv[1] is needed!\n";
}

$user = $argv[1];

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/{$user}.php";

use TeaFB\TeaFB;
use TeaFB\Utils\Post;
use TeaFB\Utils\Profile;
use TeaFB\Utils\Post\React;

$userDir = __DIR__."/../storage/{$user}";
$targetFile = "{$userDir}/target.json";
$stateDir = "{$userDir}/state";

if (!file_exists($targetFile)) {
	print "Target file does not exist: {$targetFile}!\n";
	exit(1);
}

is_dir($stateDir) or mkdir($stateDir);

$fb = new TeaFB($email, $password, $cookieFile);
if ($fb->login() === $fb::LOGIN_OK) {

	/**
	 * Init.
	 */
	$reactChooser = function ($inputReacts) {
		$reacts = [];
		foreach ($inputReacts as $k => $v) {
			for ($i=0; $i < $v; $i++) { 
				$reacts[] = $k;
			}
		}
		return $reacts[rand(0, count($reacts) - 1)];
	};
	$post = new Post($fb);
	$profile = new Profile($fb);


	// Load targets.
	$target = json_decode(file_get_contents(__DIR__."/../storage/{$user}/target.json"), true);

	// React targets.
	foreach ($target as $username => $v) {

		print "Visiting target profile: {$username}...\n";

		// Load state.
		$stateFile = "{$stateDir}/{$username}.json";
		if (!file_exists($stateFile)) {
			$state = [];
		} else {
			$state = json_decode(file_get_contents($stateFile), true);
			if (!is_array($state)) {
				$state = [];
			}
		}
		$state["username"] = $username;
		$state["last_reacted"] = null;
		foreach($profile->visit($username)->getReactablePosts() as $storyId) {
			if (isset($state["reacted"][$storyId])) {
				print "Skipping {$storyId}, it has already been reacted.\n";
			} else {
				print "Visiting target's post: {$storyId}...\n";
				$postInfo = $post->visit($storyId);
				$react = $reactChooser($v);
				print "Decided to use {$react} react.\n";
				print "Reacting {$storyId}...";
				$postInfo->react($react);
				print "OK\n";
				$state["reacted"][$storyId] = [
					"react" => $react,
					"content" => $postInfo->getContent(),
					"reacted_at" => date("Y-m-d H:i:s")
				];
			}
		}
		$state["last_reacted"] = date("Y-m-d H:i:s");
		file_put_contents($stateFile, json_encode($state, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}
} else {
	printf("Login failed!\n");
}
