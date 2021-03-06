<?php

function get_user_tweets($user_ids) {
    
    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);
    $connection->host = 'https://api.twitter.com/1.1/';

    $user_tweets = array();
    foreach ($user_ids as $user_id) {
        $params = array(
            'user_id' => $user_id,
            'since_id' => TWEET_START_ID,
            'max_id' =>TWEET_END_ID,
//            'count' => 10,
        );
        //var_dump($params);
        $query = 'statuses/user_timeline';
        $res = $connection->get($query, $params);
//        var_dump($res);
        if (isset($res->errors)) {
            var_dump($res->errors);
            break;
        }
        if (!count($res)) {
            continue;
        }
        $user_tweets[$user_id] = array();
        foreach ($res as $st) {
            if (!isset($st->geo)) {
                continue;
            }
            $st->timestamp = strtotime($st->created_at);
            $st->time = date('Hi', $st->timestamp);
            $user_tweets[$user_id][] = $st;
        }
    }
    return $user_tweets;
}
