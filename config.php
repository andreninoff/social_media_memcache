<?php
/*
 * Path of server
 */
define('RAIZ', dirname(__FILE__));
define('BASE', dirname($_SERVER['PHP_SELF']) == "/" ? "" : dirname($_SERVER['PHP_SELF']));

/*
 * Data of Instagram
 */
define('INSTAGRAM_ID_USER', 'XXXX'); // ID USER INSTAGARM
define('INSTAGRAM_USER', 'XXXX'); // SLUG USER INSTAGRAM
define('INSTAGRAM_TOKEN_SECRET_USER', 'XXXX'); // TOKEN SECRET USER INSTAGRAM
define('INSTAGRAM_MAX_POST', 10);
define('INSTAGRAM_MAX_COMMENTS', 1);
define('INSTAGRAM_URL', 'https://api.instagram.com/v1/users/'.INSTAGRAM_ID_USER.'/media/recent/?access_token='.INSTAGRAM_TOKEN_SECRET_USER.'&count='.INSTAGRAM_MAX_POST);

/*
 * Data of Twitter
 */
define('TWITTER_ID_USER', 'XXXX'); // ID USER TWITTER
define('TWITTER_USER', 'XXXX'); // SLUG USER TWITTER
define('TWITTER_CONSUMER', 'XXXX'); // CONSUMER APP TWITTER
define('TWITTER_CONSUMER_SECRET', 'XXXX'); // CONSUMER APP SECRET TWITTER
define('TWITTER_ACCESS_TOKEN', 'XXXX'); // ACCESS TOKEN TWITTER
define('TWITTER_ACCESS_TOKEN_SECRET', 'XXXX'); // ACESS TOKEN SECRET TWITTER
define('TWITTER_MAX_POST', 10);
define('TWITTER_URL', 'https://api.twitter.com/1.1/statuses/user_timeline.json');

/*
 * Data of Facebook
 */
define('FACEBOOK_APP_ID', 'XXXX'); // ID USER FACEBOOK
define('FACEBOOK_APP_SECRET', 'XXXX'); // ID APP SECRET FACEBOOK
define('FACEBOOK_PAGE_ID', 'XXXX'); // ID PAGE FACEBOOK
define('FACEBOOK_MAX_POST', 10);

/*
 * Data of Memcache
 */
define('CACHE_KEY', sha1('XXXX')); // KEY CACHE MEMCACHE
define('CACHE_TIME', 60 * 10); //600s
define('CACHE_TTL',  60); //60s
define('CACHE_LOCK_KEY', 'XXXX'); // KEY LOCK CACHE MEMCACHE
define('CACHE_TIMESTAMP_KEY', 'XXXX'); // KEY FOR TIME MEMCACHE
