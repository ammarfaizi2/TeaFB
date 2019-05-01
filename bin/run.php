<?php

if (!isset($argv[1])) {
	print "argv[1] is needed!\n";
}

$user = $argv[1];
$workerAmount = 5;

require __DIR__."/../src/autoload.php";
require __DIR__."/../config/{$user}.php";

use TeaFB\TeaFB;
use TeaFB\Utils\Post;
use TeaFB\Utils\Profile;
use TeaFB\Utils\Post\React;
use TeaFB\Exceptions\PostException;
use TeaFB\Exceptions\ProfileException;

pcntl_signal(SIGCHLD, SIG_IGN);

$userDir = __DIR__."/../storage/{$user}";
$targetFile = "{$userDir}/target.json";
$stateDir = "{$userDir}/state";

if (!file_exists($targetFile)) {
	print "Target file does not exist: {$targetFile}!\n";
	exit(1);
}

is_dir($stateDir) or mkdir($stateDir);

while(true):

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

        unset($fb);

        $cookiePointer = 0;
        $cookies = [];
        for ($i=1; $i <= $workerAmount; $i++) { 
            file_exists($cookieFile.".worker.{$i}") and unlink($cookieFile.".worker.{$i}");
            copy($cookieFile, $cookies[] = $cookieFile.".worker.{$i}");
        }

    	// Load targets.
    	$target = json_decode(preg_replace("/\/\/.+\n/", "", file_get_contents(__DIR__."/../storage/{$user}/target.json")), true);
        
        $pids = [];
        
    	// React targets.
    	foreach ($target as $username => $v) {
    		if (!($pid = pcntl_fork())) {

                $fb = new TeaFB($email, $password, $cookies[$cookiePointer % $workerAmount]);
                $post = new Post($fb);
                $profile = new Profile($fb);

    		    cli_set_process_title("worker --target {$username}");
    		    sleep(1);
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
    
        		try {
        			foreach($profile->visit($username)->getReactablePosts() as $storyId) {
        				if (isset($state["reacted"][$storyId])) {
        					print "Skipping {$storyId}, it has already been reacted.\n";
        				} else {
        					try {
        						print "Visiting target's post: {$storyId}...\n";
        						$postInfo = $post->visit($storyId);
        						// $react = "skipped";
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
        						$state["last_reacted"] = date("Y-m-d H:i:s");
        						file_put_contents($stateFile, json_encode($state, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        					} catch (PostException $e) {
        						print "Got post exception for {$username}_{$storyId}!\n";
        						print $e->getMessage()."\n";
        					}
        				}
        			}
        		} catch (ProfileException $e) {
        			print "Got profile exception for {$username}!\n";
        			print $e->getMessage()."\n";
        		}
        		exit;
    		}

            $cookiePointer++;
    
    		$pids[] = $pid;
		    while (count($pids) >= $workerAmount) {
		        foreach ($pids as $k => $pid) {
		            if (pcntl_waitpid($pid, $status, WNOHANG) == -1) {
		                unset($pids[$k]);
		            }
		        }
		        sleep(2);
		    }
    	}
    	
    	unset($target, $fb, $post, $profile, $reactChooser, $username, $k);
    } else {
    	printf("Login failed!\n%s\n", date("Y-m-d H:i:s"));
    	exit(1);
    }
    
    // Clean up.
    while (count($pids) > 0) {
        foreach ($pids as $k => $pid) {
            if (pcntl_waitpid($pid, $status, WNOHANG) == -1) {
                unset($pids[$k]);
            }
        }
        sleep(1);
    }

    unset($pids);

    print "Cleaning up worker's cookies...\n";
    foreach ($cookies as $cookie_) {
        file_exists($cookie_) and unlink($cookie_) and print "Removed {$cookie_}\n";
    }
    unset($cookies, $cookie_);
    
    print "Sleeping 60 seconds";
    for ($i=0; $i < 60; $i++) { 
    	sleep(1);
    	print ".";
    }
    print "\n";

endwhile;
