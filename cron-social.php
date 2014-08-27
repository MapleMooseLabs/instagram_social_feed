<?php
	error_reporting(E_ALL);
        include('_include.php');
        bootstrap_d7();
	echo "<PRE>";

	date_default_timezone_set('America/New_York');

	//Enter query to make CURL request to API
	function api_call($query){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $query);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		$result= curl_exec($curl);
		curl_close($curl);

		return $result;
	}

	/*
	 * INSTAGRAM
	 * Pull results only from $user_id account
	 * Search for results by tag name. Only one tag can be used
	 *
	 */
	function cron_instagram($tag, $access_token, $table){

		$access_token = '30805250.1fb234f.27a3c29a8e8545caa31a7be52462c2fa';

		$table = 'social_instagram';
		$instagram_query = "https://api.instagram.com/v1/tags/$tag/media/recent?access_token=$access_token";

		$instagram_feed = json_decode(api_call($instagram_query));

		 echo "<pre style='font-size:14px;'>";
		 	var_dump($instagram_feed);
		 echo "</pre>";

		 foreach($instagram_feed->data as $feed){
		 	//Check if instagram photo already exists based on unix timestamp
			$sql = "SELECT instagram_id FROM {$table} WHERE instagram_id = '{$feed->id}'";
		 	$result = db_query($sql);
			$count = $result->rowCount();

			//Comma deliminate hashtag array and return as string
			$tags = implode(",", $feed->tags);

			//If no duplicate time entries proceed
			if($count < 1){
			 	$data = array(
					'user_id' => $feed->user->id,
					'tags' => $tags,
					//Time stored in unix epoch format
					'time'=>$feed->created_time,
					'low_resolution' => $feed->images->low_resolution->url,
					'thumbnail' => $feed->images->thumbnail->url,
					'standard_resolution' => $feed->images->standard_resolution->url,
					'caption' => utf8_encode($feed->caption->text),
					'instagram_id' => $feed->id,
					'instagram_link' => $feed->link,
					'instagram_user' => $feed->user->username,
				);

				//Insert data into table
				$result = db_insert($table)->fields($data)->execute();

				$post = array(
				  'post_author'    => 1,
				  'post_content'   => $data['caption'],
				  'post_name'      => 'instagram_'.$data->time,
				  'post_parent'    => 0,
				  'post_status'    => 'publish',
				  'post_title'     => $data['caption'],
				  'post_type'      => 'instagram'
				);
			}
		 }
	 }

	function cron_twitter_hashtag($hashtag, $limit, $table) {
		$tbl = 'social_twitter';

		$settings = array(
			'oauth_access_token' => "OAUTH_ACCESS",
			'oauth_access_token_secret' => "OAUTH_ACCESS",
			'consumer_key' => "CONSUMER_ACCESS",
			'consumer_secret' => "CONSUMER_ACCESS"
		);

		$url = "https://api.twitter.com/1.1/search/tweets.json";
		$getField = "?q={$hashtag}&result_type=recent&count={$limit}&include_entities=true";
		$requestMethod = "GET";
		$twitter = new TwitterAPIExchange($settings);

		$response = $twitter->setGetfield($getField)
							->buildOauth($url,$requestMethod)
							->performRequest();

		$tweets = json_decode($response);

		//print_r($tweets);

		foreach($tweets->statuses as $feed){

			//Convert time to unix timestamp
			$time = strtotime($feed->created_at);

			//Comma deliminate hashtag array and return as string
			$tags = "";//implode(",", $feed->entities->hashtags);

			$clean_tweet = "";
			$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
			$clean_tweet = preg_replace($regexEmoticons, '', $feed->text);
			$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
			$clean_tweet = preg_replace($regexSymbols, '', $clean_tweet);
			$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
			$clean_tweet = preg_replace($regexTransport, '', $clean_tweet);

			//Check if tweet already exists based on unix timestamp
		 	$result = db_query("SELECT twitter_id FROM {$tbl} WHERE twitter_id = '{$feed->id}'");
			$count = $result->rowCount();

			//If no duplicate time entries proceed
			if($count < 1){
				$data = array(
					'user_id' => $feed->user->id,
					'user_name' => $feed->user->name,
					'tags' => $tags,
					'tweet' => $clean_tweet,
					//Time stored in unix epoch format
					'tweet_time' => $time,
					'twitter_id' => $feed->id_str,
				);

				echo "INSERTING DA TWEETER: " . $clean_tweet . PHP_EOL;
				$result = db_insert($tbl)->fields($data)->execute();
			}
		}
		echo "Fin! " . PHP_EOL . PHP_EOL . PHP_EOL;
	}

	/*
	 * TWITTER
	 * Function takes input of username, number of results to return and an (optional) #hashtag
	 */
	function cron_twitter_username($tag, $num_results, $table){
		echo "starting cron twitter" . PHP_EOL;
		$table = 'social_twitter';

	    $settings = array(
	        'oauth_access_token' => "OAUTH_ACCESS",
	        'oauth_access_token_secret' => "OAUTH_ACCESS",
	        'consumer_key' => "OAUTH_ACCESS",
	        'consumer_secret' => "OAUTH_ACCESS"
	    );

	echo "set Das Settings" . PHP_EOL;

	    $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	    $getfield = "?screen_name={$tag}&count={$num_results}&include_entities=true&include_rts=true";
	    $requestMethod = 'GET';
	    $twitter = new TwitterAPIExchange($settings);
	echo "Made Twitter object! " . PHP_EOL;
	    $response = $twitter->setGetfield($getfield)
	                 ->buildOauth($url, $requestMethod)
	                 ->performRequest();
		echo "performed getTweets" . PHP_EOL;
	    //Now we load the JSON into an associative array.  The first parameter is the response we got from the CURL session and the second boolean is for the decode to return an associative array.
	    $tweets = json_decode($response);

		print_r($tweets);

		foreach($tweets as $feed){
			//Convert time to unix timestamp
			$time = strtotime($feed->created_at);

			//Comma deliminate hashtag array and return as string
			$tags = "";//implode(",", $feed->entities->hashtags);

			//Check if tweet already exists based on unix timestamp
		 	$result = db_query("SELECT twitter_id FROM {$table} WHERE twitter_id = '{$feed->id}'");
			$count = $result->rowCount();

			//If no duplicate time entries proceed
			if($count < 1){
				$data = array(
					'user_id' => $feed->user->id,
					'user_name' => $feed->user->name,
					'tags' => $tags,
					'tweet' => $feed->text,
					//Time stored in unix epoch format
					'tweet_time' => $time,
					'twitter_id' => $feed->id_str
				);

				echo "inserting Das Tweet: " . $feed->text . PHP_EOL;

				$result = db_insert($table)->fields($data)->execute();

			}
		}

	echo "Fin! " . PHP_EOL . PHP_EOL . PHP_EOL;
	}


	//Initiate instagram cron
	cron_instagram('rmcad', 'STRINGOFNUMBERS','social_instagram');

	//Initiate the twitter cron to pull from the @RMCAD account
//	cron_twitter_username('rmcad', 25, 'social_twitter');

	//Initiate twitter cron to pull all tweets with '#rmcad' hashtag
//	cron_twitter_hashtag('%23rmcad%20OR%20%40rmcad',25,'social_twitter');

?>
