<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;
use App\Controller\Component\TwitterComponent;
use App\Controller\Component\EmailComponent;

class AqoursBirthdayShell extends Shell
{
  // LINE アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';

  public function initialize() {
    // component
    $this->Aqours = new AqoursComponent(new ComponentRegistry());
    $this->Line   = new LineComponent(new ComponentRegistry());
    $this->You    = new YouComponent(new ComponentRegistry());
    $this->Twitter= new TwitterComponent(new ComponentRegistry());
    $this->Email= new EmailComponent(new ComponentRegistry());
  }


  public function main()
  {
    $year = date('Y');
    $date = date('md');
    $day  = date("n月j日");

    $birthdays = $this->Aqours->getBirthday($date);
    if(!empty($birthdays)) {
      foreach($birthdays as $birthday) {
        $name = $birthday['name'];
        $tag = $name."生誕祭".$year;
        $title = $tag;

        if($birthday['kind'] == 1){
          $str = "{$day}は{$name}さんの誕生日！！\nおめでとうございます！\n#{$tag}";

          $body = "{$day}は{$name}さんの誕生日！！\nおめでとうございます！";
        }else{
          //キャラ
          $str = "{$day}は{$name}の誕生日！！\nおめでとう！\n#{$tag}";
          $body = "{$day}は{$name}の誕生日！！\nおめでとう！";
        }

        // twitter送信
        $result = $this->Twitter->post($str);

        // はてなブログ用
        $this->Email->send(HATENA_BLOG_MAIL, HATENA_BLOG_MAIL_NAME, HATENA_SEND_MAIL, $title, $body);
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