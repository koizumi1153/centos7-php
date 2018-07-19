<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;
use App\Controller\Component\EmailComponent;
use App\Controller\Component\ScraipingComponent;

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
    $this->Scraiping = new ScraipingComponent(new ComponentRegistry());
    $this->Email= new EmailComponent(new ComponentRegistry());
  }

  /**
   * @return bool|int|null|void
   */
  public function main()
  {
    $list = SCRAPING_URL_SUNSHINE_LIST;

    $newsIds = array();
    $newsBefor = array();
    // 全件取得
    $newsData = $this->Aqours->getNewsAll();
    if(!empty($newsData)) {
      $newsIds = array_column($newsData, 'id');
      foreach($newsData as $data){
        $newsBefor[$data['id']] = $data['publish_date'];
      }
    }

    // 通常処理
    $contents = array();
    $update = array();
    $cnt=0;
    foreach($list as $category => $url) {

      $html = file_get_contents(SCRAPING_URL_SUNSHINE_BASE . $url);
      $dom = \phpQuery::newDocument($html);

      if(!empty($dom["#contents .infobox"])) {
        // 1ページ5データ
        for ($i = 0; $i < 5; $i++) {
          $id = ($dom["#contents"]->find(".infobox:eq($i)")->attr("id"));
          // 不要なデータは入れない
          if(empty($id)) break;

          // 含まれていないidだけ配列に入れる
          if(!in_array($id, $newsIds)) {
            $contents[$cnt]['category'] = $category;
            $contents[$cnt]['id'] = $id;
            $contents[$cnt]['title'] = ($dom["#contents"]->find(".infobox")->find(".title:eq($i)")->text());
            $contents[$cnt]['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
            $contents[$cnt]['html_body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html()));
            $contents[$cnt]['body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text()));
            $cnt++;
          }else{
            $data = array();
            $publish_date = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
            if(date('Y/m/d', strtotime($newsBefor[$id])) < $publish_date){
              //update
              $data['category'] = $category;
              $data['id'] = $id;
              $data['title'] = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());
              $data['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
              $data['html_body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html()));
              $data['body'] = htmlspecialchars(trim($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text()));
              $update[] = $data;
            }
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

      $text="";
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

     sleep(1);
    //update
    if(!empty($update)){
      $categoryName = SCRAPING_CATEGORY_NAME;

      $categorys = array();
      foreach($update as $data){
        $this->Aqours->updateNews($data);
        $categorys[$data['category']][] = $data['title'];
      }

      $text="";
      $messageData = array();
      foreach($categorys as $category => $news){
        $url = SCRAPING_URL_SUNSHINE_BASE .$list[$category];
        $text = "[".$categoryName[$category]."]のニュースが更新されました。";
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

  /**
   * Aqours CLUB 2017→2018 お知らせ取得
   */
  public function club(){
    $contents = array();
    $clubNews = array();
    // 最新10件に含まれていなければOKとする
    $clubNews = $this->Aqours->getClubNews(0, 10);

    #2018用
    $pushUrl = "https://lovelive-aqoursclub.jp/";
    $url="https://lovelive-aqoursclub.jp/mob/form/ajaxLogin.php";
    $nextUrl = "https://lovelive-aqoursclub.jp/mob/news/newsLis.php";
    $posts = array(
      'loginUser' => 'koizumi1153@gmail.com',
      'loginPass' => 'sakura3939');

    $dom = $this->Scraiping->postScraping($url, $posts, $nextUrl);
    if(!empty($dom)) {
      $cnt=0;
      for ($i = 0; $i < 5; $i++) {
        $date  = ($dom["#container"]->find(".items-item:eq($i)")->find(".info-date")->text());
        $title = ($dom["#container"]->find(".items-item:eq($i)")->find(".info-desc")->text());
        if($this->Aqours->checkNews($date, $title, $clubNews)){
          //存在していたらtrue
          continue;
        }else{
          $contents[$cnt]['publish_date']  = $date;
          $contents[$cnt]['title'] = $title;
          $cnt++;
        }
      }

    }
/*
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
*/
    if(!empty($contents)) {
      //bulkinsert
      $this->Aqours->setClubNews($contents);

      //push
      $text = '';
      foreach($contents as $cnt => $data) {
        if($cnt != 0) $text .= "\n\n";
        $text .= "{$data['title']}";
      }

      $text .= "\n\n{$pushUrl}";

      $messageData = $this->Line->setTextMessage($text);

      #$this->Line->sendPush(LINE_API_PUSH_URL, $this->ACCESS_TOKEN, $this->ADMIN_USER, $messageData);
      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, PUSH_SELL_OFFICIAL);
      $this->Twitter->post($messageData);
    }
  }

  public function lantis(){
    $lantisNews = $this->Aqours->getLantis(0, 10);

    $baseurl = "https://www.lantis.jp";
    $url = "https://www.lantis.jp/title/lovelive_sunshine/";
    $html = file_get_contents($url);
    $dom = \phpQuery::newDocument($html);

    $cnt = 0;
    // 1ページ5データ
    for ($i = 0; $i < 3; $i++) {
      $href   = trim( $dom["#title-left"]->find("li:eq($i)")->find("a")->attr("href"));
      $title = trim( $dom["#title-left"]->find("li:eq($i)")->text() );
      $date  = trim( $dom["#title-left"]->find("li:eq($i)")->find(".news-date")->text() );

      if($this->Aqours->checkNews($date, $title, $lantisNews)){
        //存在していたらtrue
        continue;
      }else{
        $contents[$cnt]['publish_date'] = $date;
        $contents[$cnt]['title']        = $title;
        $contents[$cnt]['url']          = $baseurl.$href;
        $cnt++;
      }
    }

    if(!empty($contents)) {
      //bulkinsert
      $this->Aqours->setLantis($contents);

      //push
      $text = '';
      foreach($contents as $cnt => $data) {
        if($cnt != 0) $text .= "\n\n";
        $text .= "{$data['title']}";
        $text .= "\n\n".$data['url'];
      }

      $messageData = $this->Line->setTextMessage($text);

      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, PUSH_KIND_SELL);
    }
  }

  /**
   * 特定のページを取得して行く。
   *
   */
  public function livePage(){
    $links = $this->Aqours->getScraping(SCRAPING_KIND_LIVE);
    if(!empty($links)){
      $newUrl = [];

      foreach($links as $link){
        $updateFlg = false;
        $contentsUpdateFlg = false; //内容変更
        $linkNumUpdateFlg  = false; //リンク数変更
        $scrapingData = [];
        $contentsUpdate = [];

        $scrapingId = $link['id'];
        $title = $link['title'];
        $baseUrl = $url = $link['url'];
        $base = $this->You->getUrlPath($url);

        $linkFlg = $link['link_flg'];
        $data = $this->Aqours->getScrapingData($scrapingId);

        $linkData = $this->Aqours->checkUrlData($url, $data);
        if(empty($linkData)) {
          $linkData = $this->Aqours->initUrlData($scrapingId, $url, $title);
        }

        // スクレイピングで取得
        $doc = $this->Scraiping->getScraping($url);
        if(!empty($doc)){
          $urls=[];
          $contents = $doc["#contents"]->text();
          $cnt=0;
          for($i=0;$i<10;$i++){
            $link = $doc["#contents"]->find("a:eq($i)")->attr("href");
            if(!empty($link)){
              $cnt++;
              if($link != $url) $urls[] = $link;
            }
          }

          // リンク数チェック
          if($linkFlg) {
            if ($linkData['link_num'] != $cnt) {
              $linkData['link_num'] = $cnt;
              $linkNumUpdateFlg = true;
            }
          }

          if($linkData['contents_data'] != $contents){
            $linkData['contents_data'] = $contents;
            $contentsUpdateFlg = true;
          }

          $scrapingData[] = $linkData;

          // 更新チェック
          if($linkNumUpdateFlg || $contentsUpdateFlg){
            $contentsUpdate[] = $linkData;
            $updateFlg = true;
          }
        }

        // linkチェック
        if(!empty($urls)) {
          foreach ($urls as $url){
            $str = substr($url, 0, 4);
            if($str != "http" && !empty($base)){
              // 内部リンク
              $url = $base. $url;
              if($baseUrl == $url) continue;
              if(in_array($url, $newUrl)) continue;

              // スクレイピングで取得
              $doc = $this->Scraiping->getScraping($url);

              if(!empty($doc)){
                $linkData = $this->Aqours->checkUrlData($url, $data);
                if(empty($linkData)) {
                  $linkData = $this->Aqours->initUrlData($scrapingId, $url, $title);
                  $newUrl[] = $url;
                }

                $contents = $doc["#contents"]->text();
                $cnt=0;
                for($i=0;$i<10;$i++){
                  $link = $doc["#contents"]->find("a:eq($i)")->attr("href");
                  if(!empty($link)){
                    $cnt++;
                  }
                }

                // リンク数チェック
                if($linkFlg) {
                  if ($linkData['link_num'] != $cnt) {
                    $linkData['link_num'] = $cnt;
                    $linkNumUpdateFlg = true;
                  }
                }

                if($linkData['contents_data'] != $contents){
                  $linkData['contents_data'] = $contents;
                  $contentsUpdateFlg = true;
                }

                $scrapingData[] = $linkData;

                // 更新チェック
                if($updateFlg == false){
                  $contentsUpdate[] = $linkData;
                }
              }

            }else{
              continue;
            }
          }
        }

        if(!empty($scrapingData)){
          $this->Aqours->setScrapingData($scrapingData);
        }

        $text = '';

        if(!empty($contentsUpdate)){
          foreach($contentsUpdate as $value){
            if(!empty($text)){
              $text .= "\n\n". $value['url'];
            }else {
              $text .= "[" . $value['title'] . "]が更新されました。\n" . $value['url'];
            }
          }
        }

        if(!empty($text)) {
          // 更新送信
          $messageData = $this->Line->setTextMessage($text);
          $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, PUSH_KIND_PERFORMANCE);
        }

      }
    }
  }

  /**
   * 公式通販サイト処理
   */
  public function shopPage(){
    $links = $this->Aqours->getScraping(SCRAPING_KIND_SHOP);
    if(!empty($links)) {
      $blogTitle = "";
      $blogBody  = "";
      $scrapingData = [];
      $contentsUpdate = [];
      foreach($links as $link) {
        $scrapingId = $link['id'];
        $BaseTitle = $link['title'];
        $url = $link['url'];

        $data = $this->Aqours->getScrapingData($scrapingId);

        // スクレイピングで取得
        $doc = $this->Scraiping->getScraping($url);
        for($i=0;$i<=10;$i++) {
          $newFlg = false;
          $contents = str_replace("\t", '', trim($doc["main"]->find("ul")->find("li")->find("div.left:eq($i)")->text()));
          if (!empty($contents)) {
            $contentsData = explode("\n", $contents);
            if(!empty($contentsData[0])){
              $title = $contentsData[0];
              $linkData = $this->Aqours->checkTitleData($title, $data);
              if(empty($linkData)) {
                $linkData = $this->Aqours->initUrlData($scrapingId, $url, $title);
                $newFlg = true;
              }

              if($linkData['contents_data'] != $contents){
                $linkData['contents_data'] = $contents;
                // データ更新用
                $scrapingData[] = $linkData;

                // 通知用
                $linkData['is_new'] = $newFlg;
                $contentsUpdate[] = $linkData;
              }
            }
          }else{
            break;
          }
        }

        if(!empty($scrapingData)){
          $this->Aqours->setScrapingData($scrapingData);
        }

        $text = '';

        if(!empty($contentsUpdate)){
          foreach($contentsUpdate as $value){
            if(!empty($text)){
              $text .= "\n\n";
            }else{
              $text .= $BaseTitle."からのお知らせです。\n". $value['url']."\n";
              $blogTitle = $BaseTitle."に新商品が追加されました。";
            }

            if(!empty($blogBody)) $blogBody .= "\n\n";

            if($value['is_new']){
              $text .= "[" . $value['title'] . "]が追加されました。" ;
              $blogBody .= "[" . $value['title'] . "]が追加されました。" ;
            }else {
              $text .= "[" . $value['title'] . "]が更新されました。";
            }

          }
        }

        if(!empty($blogBody)) $blogBody .= "\n\n{$url}";

        //送信
        if(!empty($text)) {
          // 更新送信
          $messageData = $this->Line->setTextMessage($text);
          $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, PUSH_KIND_SELL);
        }

        if(!empty($blogTitle) && !empty($blogBody)) {
          // はてなブログ用
          $this->Email->send(HATENA_BLOG_MAIL, HATENA_BLOG_MAIL_NAME, HATENA_SEND_MAIL, $blogTitle, $blogBody);
        }
      }
    }
  }

}