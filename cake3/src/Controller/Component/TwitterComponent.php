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

  public function initialize(array $config)
  {
    $this->Base = TableRegistry::get('TwitterBotBase');
    $this->Date = TableRegistry::get('TwitterBotDate');
    $this->Word = TableRegistry::get('TwitterBotWord');
  }

    /**
     * @param string $consumer_key
     * @param string $consumer_secret
     * @param string $access_token
     * @param string $access_token_secret
     * @return TwitterOAuth
     */
  public function twitterOAuth($consumer_key='', $consumer_secret='', $access_token='', $access_token_secret=''){
      if(empty($access_token)) $access_token = $this->TWITTER_ACCESS_TOKEN;
      if(empty($access_token_secret)) $access_token_secret = $this->TWITTER_ACCESS_TOKEN_SECRET;
      if(empty($consumer_key)) $consumer_key = $this->TWITTER_CONSUMER_KEY;
      if(empty($consumer_secret)) $consumer_secret = $this->TWITTER_CONSUMER_SECRET;

      $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
      return $connection;
  }

  /**
   * @param $str
   * @return mixed
   */
  public function post($str){
    #$connection = new TwitterOAuth($this->TWITTER_CONSUMER_KEY, $this->TWITTER_CONSUMER_SECRET, $this->TWITTER_ACCESS_TOKEN, $this->TWITTER_ACCESS_TOKEN_SECRET);

    $connection = $this->twitterOAuth();
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
    #$connection = new TwitterOAuth($this->TWITTER_CONSUMER_KEY, $this->TWITTER_CONSUMER_SECRET, $this->TWITTER_ACCESS_TOKEN, $this->TWITTER_ACCESS_TOKEN_SECRET);
    $connection = $this->twitterOAuth();

    $content = $connection->get("statuses/user_timeline", array(
      "screen_name" => "{$screen_name}",
      "count" => "{$count}",
      "trim_user" => "true",
      "exclude_replies" => "true",
      "include_rts" => "false",
    ));

    return $content;
  }

    /**
     * @param $str
     * @param $img
     * @param $consumer_key
     * @param $consumer_secret
     * @param string $access_token
     * @param string $access_token_secret
     * @return array|object
     */
  public function setImgPost($str, $img='', $consumer_key='', $consumer_secret='', $access_token='', $access_token_secret=''){
      if(empty($access_token)) $access_token = $this->TWITTER_ACCESS_TOKEN;
      if(empty($access_token_secret)) $access_token_secret = $this->TWITTER_ACCESS_TOKEN_SECRET;
      if(empty($consumer_key)) $consumer_key = $this->TWITTER_CONSUMER_KEY;
      if(empty($consumer_secret)) $consumer_secret = $this->TWITTER_CONSUMER_SECRET;

#      $connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
      $connection = $this->twitterOAuth($consumer_key='', $consumer_secret='', $access_token='', $access_token_secret='');


      if(!empty($img)) {
          $media = $connection->upload('media/upload', ['media' => '/var/www/cake/cake3/webroot/img/' . $img]);
      }
      // ツイートの内容を設定
      $params = [
          'status' => "{$str}",
          'media_ids' => implode(',', [$media->media_id_string])
      ];

      $result = $connection->post("statuses/update", $params);

      return $result;
  }

    /**
     * @param $id
     * @return mixed
     */
  public function getBase($id){
      $query = $this->Base->find();
      $query->where(['id' => $id]);
      $query->where(['deleted IS NULL']);

      $base = $query->first();

      if(!empty($base)) $base->toArray();
      return $base;
  }

    /**
     * @param $baseId
     * @param $date
     * @return mixed
     */
  public function getDate($baseId, $date){
      $query = $this->Date->find();
      $query->where(['base_id' => $baseId]);
      $query->where(['tweet_date' => $date]);
      $query->where(['deleted IS NULL']);

      $date = $query->first();
      if(!empty($date)) $date->toArray();
      return $date;
  }

    /**
     * @param $ids
     * @return mixed
     */
  public function getWords($ids){
      $query = $this->Word->find();
      $query->where(['id IN' => [$ids]]);
      $query->where(['deleted IS NULL']);
      $query->order(['use_count' => 'ASC']);

      $words = $query->hydrate(false)->toArray();

      return $words;
  }

    /**
     * @param $id
     * @param $count
     */
  public function updateCount($id, $count){
      $now = date('Y-m-d H:i:s');
      $query = $this->Word->query();

      $query->update()
          ->set(['use_count' => $count + 1])
          ->where(['id' => $id])
          ->where(['deleted IS NULL'])
          ->execute();
  }

    /**
     * リツイート処理
     * @param $id
     * @param string $consumer_key
     * @param string $consumer_secret
     * @param string $access_token
     * @param string $access_token_secret
     */
  public function retweet($id, $consumer_key='', $consumer_secret='', $access_token='', $access_token_secret=''){
      $connection = $this->twitterOAuth($consumer_key='', $consumer_secret='', $access_token='', $access_token_secret='');
      // リツイート
      $result = $connection->post("statuses/retweet/{$id}");
  }

}