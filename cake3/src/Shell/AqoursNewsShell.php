<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;

require_once('/var/www/cake/cake3/vendor/phpQuery-onefile.php');

class AqoursNewsShell extends Shell
{
  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  protected $ADMIN_USER = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';
  public function initialize() {
    // component
    $this->Aqours = new AqoursComponent(new ComponentRegistry());
    $this->Line   = new LineComponent(new ComponentRegistry());
    $this->You    = new YouComponent(new ComponentRegistry());
  }

  /**
   * @return bool|int|null|void
   */
  public function main()
  {
    $list = SCRAPING_URL_SUNSHINE_LIST;

    $newsIds = array();
    // 最新300件に含まれていなければOKとする
    $newsData = $this->Aqours->getNewsLimit( 0, 300);
    if(!empty($newsData)) {
      $newsIds = array_column($newsData, 'id');
    }

    // 通常処理
    $contents = array();
    $cnt=0;
    foreach($list as $category => $url) {

      $categoryNewsIds = array();
      // 最新100件に含まれていなければOKとする
      $categoryData = $this->Aqours->getNewsFromCategory($category, 0, 20);
      if(!empty($categoryData)) {
        $categoryNewsIds = array_column($categoryData, 'id');
      }

      $html = file_get_contents(SCRAPING_URL_SUNSHINE_BASE . $url);
      $dom = \phpQuery::newDocument($html);

      if(!empty($dom["#contents .infobox"])) {
        // 1ページ5データ
        for ($i = 0; $i < 5; $i++) {
          $id = ($dom["#contents"]->find(".infobox:eq($i)")->attr("id"));
          // 不要なデータは入れない
          if(empty($id)) break;

          // 含まれていないidだけ配列に入れる
          if(!in_array($id, $newsIds) && !in_array($id, $categoryNewsIds)) {
            $contents[$cnt]['category'] = $category;
            $contents[$cnt]['id'] = $id;
            $contents[$cnt]['title'] = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());
            $contents[$cnt]['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
            $contents[$cnt]['html_body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html()));
            $contents[$cnt]['body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text()));
            $cnt++;
          }
        }
      }
    }

    // blukinsert処理
    if(!empty($contents)){
      $categoryName = SCRAPING_CATEGORY_NAME;
      $this->Aqours->setNews($contents);

      $categorys = array();
      // PUSH
      foreach($contents as $data){
        $categorys[$data['category']][] = $data['title'];
      }

      $messageData = array();
      foreach($categorys as $category => $news){
        $url = SCRAPING_URL_SUNSHINE_BASE .$list[$category];
        $text = "[".$categoryName[$category]."]のニュースが追加されました。";
        foreach($news as $title){
          $text .= "\n\n".$title;
        }

        $text .= "\n\n".$url;
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }

      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN);
    }
  }

  /**
   *
   */
  public function init()
  {
    // 存在チェック true=データがある
    $isNews = $this->Aqours->checkNewsInit();
    if($isNews === false){
      $list = SCRAPING_URL_SUNSHINE_LIST;

      $contents = array();
      // カテゴリ毎にinsert
      foreach($list as $category => $url) {

        // データがあればinsertして初期化
        if(!empty($contents)) $this->Aqours->setNews($contents);
        $contents = array();
        $cnt = 0;
        // サイトへの負荷軽減の為 1秒待つ
        sleep(1);

        $offsetUrl = "?offset=0";
        for($page=1;$page<=20;$page++) {
          $offset = ($page - 1) * 5;
          $offsetUrl = "?offset=".$offset;

          $html = file_get_contents(SCRAPING_URL_SUNSHINE_BASE . $url . $offsetUrl);
          $dom = \phpQuery::newDocument($html);

          if (!empty($dom["#contents .infobox"])) {
            // 1ページ5データ
            for ($i = 0; $i < 5; $i++) {
              // 不要なデータは入れない
              $id = ($dom["#contents"]->find(".infobox:eq($i)")->attr("id"));
              $title = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());

              if(!empty($id) && !empty($title)) {
                $contents[$cnt]['category'] = $category;
                $contents[$cnt]['id'] = ($dom["#contents"]->find(".infobox:eq($i)")->attr("id"));
                $contents[$cnt]['title'] = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());
                $contents[$cnt]['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
                $contents[$cnt]['html_body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html()));
                $contents[$cnt]['body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text()));
                $cnt++;
              }
            }
            // 0.5秒待つ
            usleep(500000);
          }else{
            // 0.5秒待つ
            usleep(500000);
            break;
          }
        }
      }

      // 最終カテゴリ用
      if(!empty($contents)) $this->Aqours->setNews($contents);
    }
  }

  public function club(){
    $contents = array();
    $clubNews = array();
    // 最新10件に含まれていなければOKとする
    $clubNews = $this->Aqours->getClubNews2017(0, 10);

    $url = "https://lovelive-aqoursclub.jp/mob/index.php";
    $html = file_get_contents($url);
    $dom = \phpQuery::newDocument($html);
    $cnt = 0;
    // 1ページ5データ
    for ($i = 0; $i < 5; $i++) {
      $date  = ($dom["#infoSelectorCnt"]->find(".infoSelectorItemLi1:eq($i)")->text());
      $title = ($dom["#infoSelectorCnt"]->find(".infoSelectorItemLi2:eq($i)")->text());
      if($this->Aqours->checkNews($date, $title, $clubNews)){
        //存在していたらtrue
        continue;
      }else{
        $contents[$cnt]['publish_date']  = $date;
        $contents[$cnt]['title'] = $title;
        $cnt++;
      }
    }

    if(!empty($contents)) {
      //bulkinsert
      $this->Aqours->setClubNews2017($contents);

      //push
      $text = '';
      foreach($contents as $cnt => $data) {
        if($cnt != 0) $text .= "\n\n";
        $text .= "{$data['title']}";
      }

      $text .= "\n\n{$url}";

      $messageData = $this->Line->setTextMessage($text);

      #$this->Line->sendPush(LINE_API_PUSH_URL, $this->ACCESS_TOKEN, $this->ADMIN_USER, $messageData);
      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN);
    }
  }

}