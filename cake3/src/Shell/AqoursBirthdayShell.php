<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;
use Abraham\TwitterOAuth\TwitterOAuth; //twitter

class AqoursBirthdayShell extends Shell
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

  // LINE アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';

  public function initialize() {
    // component
    $this->Aqours = new AqoursComponent(new ComponentRegistry());
    $this->Line   = new LineComponent(new ComponentRegistry());
    $this->You    = new YouComponent(new ComponentRegistry());
  }


  public function main()
  {
    $year = date('Y');
    $date = date('md');
    $day  = date("n月j日");

    $birthdays = $this->Aqours->getBirthday($date);
    if(!empty($birthdays)) {
      foreach($birthdays as $birthday) {
        $str = '';
        $name = $birthday['name'];
        $tag = $name."生誕祭".$year;
        if($birthday['kind'] == 1){
          $str = "{$day}は{$name}さんの誕生日！！\nおめでとうございます！\n#{$tag}";
        }else{
          //キャラ
          $str = "{$day}は{$name}の誕生日！！\nおめでとう！\n#{$tag}";
        }

        $twitter = new TwitterOAuth($this->TWITTER_CONSUMER_KEY, $this->TWITTER_CONSUMER_SECRET, $this->TWITTER_ACCESS_TOKEN, $this->TWITTER_ACCESS_TOKEN_SECRET);

        $result = $twitter->post(
          "statuses/update",
          array("status" => "{$str}")
        );
      }
    }
  }

  /**
   * line
   */
  public function line()
  {
    $time = date('H'); //時間取得
    $year = date('Y');
    $date = date('md');
    $day = date("n月j日");

    $birthdays = $this->Aqours->getBirthday($date);
    if (!empty($birthdays)) {
      foreach ($birthdays as $birthday) {
        $str = '';
        $name = $birthday['name'];
        $tag = $name . "生誕祭" . $year;
        if ($birthday['kind'] == 1) {
          $str = "{$day}は{$name}さんの誕生日！！\nおめでとうございます！";
        } else {
          //キャラ
          $str = "{$day}は{$name}の誕生日！！\nおめでとう！";
        }
        $messageData = $this->Line->setTextMessage($str);

        $this->You->sendMessage($messageData, $this->ACCESS_TOKEN);
      }
    }
  }
}