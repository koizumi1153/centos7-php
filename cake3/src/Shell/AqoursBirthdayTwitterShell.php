<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use Abraham\TwitterOAuth\TwitterOAuth; //twitter

class AqoursBlogShell extends Shell
{
  // API
  // コンシューマキー
  protected $CONSUMER_KEY = "peZGWhAOi1fpKEt1BJA2AyaFV";
  // コンシューマーシークレット
  protected $CONSUMER_SECRET = "TzNdzM8GbD3DQsRlJbYEFq9kkSAMxL1WcikYLLQ38lnngdFBxX";

  // user
  // アクセストークン
  protected $ACCESS_TOKEN = "112372639-JNtygVHiSbTPnppCw7eiCbGkqvMmOLavSD3geIUo";
  // アクセストークンシークレット
  protected $ACCESS_TOKEN_SECRET = "PnjLj63mP2Uaxux0v9Tr00ckNZ7dU6glrFhh9IYXSPtdI";

  public function main()
  {
    $twitter = new TwitterOAuth($this->CONSUMER_KEY, $this->CONSUMER_SECRET, $this->ACCESS_TOKEN, $this->ACCESS_TOKEN_SECRET);

    $result = $twitter->post(
      "statuses/update",
      array("status" => "API送信テスト。\nこんな感じで送信できていればOK。\n#test")
    );

    if($twitter->getLastHttpCode() == 200) {
      // ツイート成功
      print "tweeted\n";
    } else {
      // ツイート失敗
      print "tweet failed\n";
    }
  }

}