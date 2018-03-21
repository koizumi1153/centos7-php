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

    // 通常処理
    $contents = array();
    $cnt=0;
    foreach($list as $category => $url) {

      $categoryNewsIds = array();
      // 最新100件に含まれていなければOKとする
      $categoryData = $this->Aqours->getNewsFromCategory($category, 0, 100);
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
          if(!in_array($id, $categoryNewsIds)) {
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
        $text = "[".$categoryName[$category]."]のニュースが追加されました。";
        foreach($news as $title){
          $text .= "\n".$title;
        }

        $messageData = $this->Line->setTextMessage($text, $messageData);
      }

      if(!empty($messageData)) {
        // ユーザー取得
        $userCount = $this->You->getPushUsersCount();
        if ($userCount > 0) {
          $allPage = ceil($userCount / LINE_MULTI_USER);
          for ($page = 1; $page <= $allPage; $page++) {
            $user = $this->You->getPushUsers($page);
            $userIds = array_column($user, 'user_id');

            // PUSH
            if (count($messageData) > LINE_MESSAGE_COUNT) {
              $messages = array_chunk($messageData, LINE_MESSAGE_COUNT);
              foreach ($messages as $message) {
                $this->Line->sendPush(LINE_API_MULTI_URL, $this->ACCESS_TOKEN, $userIds, $message);
              }
            } else {
              $this->Line->sendPush(LINE_API_MULTI_URL, $this->ACCESS_TOKEN, $userIds, $messageData);
            }
          }
        }
      }
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

}