<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;
use Abraham\TwitterOAuth\TwitterOAuth; //twitter

class TwitterComponent extends Component
{
  // API
  // コンシューマキー
  protected $TWITTER_CONSUMER_KEY = "peZGWhAOi1fpKEt1BJA2AyaFV";
  // コンシューマーシークレット
  protected $TWITTER_CONSUMER_SECRET = "TzNdzM8GbD3DQsRlJbYEFq9kkSAMxL1WcikYLLQ38lnngdFBxX";
  // user
  // twitterアクセストークン
  protected $TWITTER_ACCESS_TOKEN = "112372639-JNtygVHiSbTPnppCw7eiCbGkqvMmOLavSD3geIUo";
  // twitterアクセストークンシークレット
  protected $TWITTER_ACCESS_TOKEN_SECRET = "PnjLj63mP2Uaxux0v9Tr00ckNZ7dU6glrFhh9IYXSPtdI";

  /**
   * @param $str
   * @return mixed
   */
  public function post($str){
    $connection = new TwitterOAuth($this->TWITTER_CONSUMER_KEY, $this->TWITTER_CONSUMER_SECRET, $this->TWITTER_ACCESS_TOKEN, $this->TWITTER_ACCESS_TOKEN_SECRET);

    $result = $connection->post(
      "statuses/update",
      array("status" => "{$str}")
    );

    return $result;
  }

  /**
   * @param $screen_name
   * @param int $count
   * @return mixed
   */
  public function getUserTimeline($screen_name, $count=10){
    $connection = new TwitterOAuth($this->TWITTER_CONSUMER_KEY, $this->TWITTER_CONSUMER_SECRET, $this->TWITTER_ACCESS_TOKEN, $this->TWITTER_ACCESS_TOKEN_SECRET);

    $content = $connection->get("statuses/user_timeline", array(
      "screen_name" => "{$screen_name}",
      "count" => "{$count}",
      "trim_user" => "true",
      "exclude_replies" => "true",
      "include_rts" => "false",
    ));

    return $content;
  }
}