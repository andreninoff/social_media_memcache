<?php
require ('config.php');
require ('lib/facebook/facebook.php');
require ('lib/twitter/StormTwitter.class.php');

date_default_timezone_set("Brazil/East");

function init(){
	
	$memcache = new Memcache; 
	$memcache->connect('127.0.0.1', 11211) or die ("Could not connect");
	
	$data_result = "";
	
	$timestamp = $memcache->get(CACHE_TIMESTAMP_KEY); // we get the timestamp of the data
	$data_result = $memcache->get(CACHE_KEY); // we fetch the data from memcache
	
	// if there is no timestamp or the timestamp is old enough we generate new data and try to store it
	if (!$timestamp || ($timestamp < (time() - CACHE_TTL))) {
		// we generate some random new data and try to save it
		if ($new_data = save_new_data($memcache)) {
			// we succeded storing the new data, let's use the new data
			$data_result = $new_data;
		}
	}

	echo "jsonCallback(";
	
	echo $data_result;
	
	echo ");";
	
	
}

function save_new_data($memcache) {
	
	// initialize flags and counters
	$lock = FALSE;
	$tries = 0;
	$max_tries = 10;
	
	// start trying to get a lock
	while($lock === FALSE && $tries < $max_tries) {
		// memcache->add will return false if the key exists
		$lock = $memcache->add(CACHE_LOCK_KEY, 1);
		
		if ($lock === FALSE) {
			// failed, we'll try again in a few milliseconds...
			$tries++;
			usleep(100 * ($tries % ($max_tries / 10))); // exponential backoff style of sleep
		} else {
			// succeded, we'll generate new data, store it, set the timestamp, clear the lock and return the generated data
			$json_result = request_social_media();
			$memcache->set(CACHE_KEY, $json_result, false, CACHE_TIME);
			$memcache->set(CACHE_TIMESTAMP_KEY, time()); // we set the data timestamp
			$memcache->delete(CACHE_LOCK_KEY);
			
			return $json_result;
		}
	}
	
	// we failed to obtain a lock, we return false
	
	return FALSE;
}

function request_social_media(){
	try{
		
		//INSTAGRAM
		
		$json_instagram = curl_file(INSTAGRAM_URL);
		
		$object_instagram = json_decode($json_instagram);
		
		if(isset($object_instagram->meta) && $object_instagram->meta->code == 200){
			
			foreach($object_instagram->data as $item_instagram){
				
				$result_instagram[] = array("image_post_low" => $item_instagram->images->low_resolution,
										    "image_post_thumb" => $item_instagram->images->thumbnail,
										    "image_post_standard" => $item_instagram->images->standard_resolution,
										    "likes" => $item_instagram->likes->count,
										    "link" => $item_instagram->link,
										    "caption" => isset($item_instagram->caption->text) && !empty($item_instagram->caption->text) ? $item_instagram->caption->text : "",
										    "created_time" => date("Y-m-d H:i:s", $item_instagram->created_time),
										    "type" => $item_instagram->type);
										  
			}
			
			
		}
		
		//TWITTER
		
		$config = array('key' 			=> TWITTER_CONSUMER,
						'secret' 		=> TWITTER_CONSUMER_SECRET,
						'token' 		=> TWITTER_ACCESS_TOKEN,
						'token_secret' 	=> TWITTER_ACCESS_TOKEN_SECRET,
						'screenname' 	=> TWITTER_USER,
						'cache_expire' 	=> CACHE_TIME);
		
		if($config['cache_expire'] < 1) $config['cache_expire'] = CACHE_TIME;
		$config['directory'] = RAIZ."/lib/twitter/cache";
		  
		$obj = new StormTwitter($config);
		$array_twitter = $obj->getTweets(TWITTER_MAX_POST, TWITTER_USER, array('include_rts' => true));
		
		
		if(isset($array_twitter) && !empty($array_twitter)){
			foreach($array_twitter as $item_twitter){
				$result_twitter[] = array("id" => $item_twitter['id_str'],
								  "post" => $item_twitter['text'],
								  "link" => "https://twitter.com/".TWITTER_USER."/status/".$item_twitter['id_str'],
								  "created_time" => date("Y-m-d H:i:s", strtotime($item_twitter['created_at'])));
			}
		}
		
		
		//FACEBOOK
		
		$facebook = new Facebook(array('appId'  => FACEBOOK_APP_ID,
	    							   'secret' => FACEBOOK_APP_SECRET));
		
		$data_facebook = $facebook->api(FACEBOOK_PAGE_ID.'/feed', "GET", array('fields' => array('feed', 'message', 'picture', 'link', 'type'), 'limit' => (FACEBOOK_MAX_POST*2)));
		
		if(isset($data_facebook) && !empty($data_facebook)){
			foreach($data_facebook['data'] as $item_facebook){
				if(!isset($item_facebook['message'])) continue;
				$result_facebook[] = array("post_id" => $item_facebook['id'],
										   "post" => isset($item_facebook['message']) ? $item_facebook['message'] : "",
										   "picture" => isset($item_facebook['picture']) ? $item_facebook['picture'] : "",
										   "link" => isset($item_facebook['link']) ? $item_facebook['link'] : "",
										   "created_time" => date("Y-m-d H:i:s", strtotime($item_facebook['created_time'])));
				if(count($result_facebook) == FACEBOOK_MAX_POST){
					break;
				}
			}
		}
		
		
		$json_result = json_encode(array("instagram" => $result_instagram, "twitter" => $result_twitter, "facebook" => $result_facebook));
		
		return $json_result;
		
	}catch(Exception $ex){
		echo json_encode(array("error" => $ex->getMessage()));
	}

}

function curl_file($url, $options_header="") {
      
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    if(!empty($options_header)){
    	curl_setopt($ch, CURLOPT_HTTPHEADER, $options_header);
    }
	$data = curl_exec($ch);
	curl_close($ch);
    return $data;

}

init();

