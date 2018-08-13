<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\RakutenComponent;
use App\Controller\Component\ScraipingComponent;
use App\Controller\Component\YouComponent;
use App\Controller\Component\LineComponent;

class AqoursShell extends Shell
{

  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  // 管理者ID
  protected $ADMIN_USER = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';
  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->Scraping = new ScraipingComponent(new ComponentRegistry());
    $this->You = new YouComponent(new ComponentRegistry());
    $this->Line = new LineComponent(new ComponentRegistry());
  }

  public function main()
  {
    $url = "https://www.asmart.jp/Form/Product/ProductDetail.aspx?shop=0&cat=600618&swrd=&pid=10017691&vid=";
    $doc = $this->Scraping->getScraping($url);

    $file = "/home/yohane/aika.txt";
    $base = file_get_contents($file);
    if($doc != $base){
      file_put_contents($file, $doc);

      $text = $url."\n\n更新されました。";
      $messageData = $this->Line->setTextMessage($text);
      // PUSH
      $this->Line->sendPush(LINE_API_PUSH_URL, $this->ACCESS_TOKEN, $this->ADMIN_USER, $messageData);
    }
  }
}