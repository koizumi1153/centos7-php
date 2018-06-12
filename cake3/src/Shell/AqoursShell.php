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

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->Rakuten = new RakutenComponent(new ComponentRegistry());
    $this->Scraping = new ScraipingComponent(new ComponentRegistry());
    $this->You = new YouComponent(new ComponentRegistry());
    $this->Line = new LineComponent(new ComponentRegistry());
  }

  public function main()
  {

    #$this->out('start task');

    // 楽天処理
    $keywords=AQOURS_KEYWORDS;
    $rakuten_kind=AQOURS_RAKUTEN_KIND;
    foreach($keywords as $keyword){
      foreach($rakuten_kind as $kind){
        for($page=1;$page<=10;$page++) {
          $url = $this->Rakuten->setRequestUrl($kind, $keyword, $page);
          if (!empty($url)) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列>で返す
            $response = curl_exec($curl);
            $result = json_decode($response, true);

            curl_close($curl);
          }

          if (!empty($result['Items'])){
            $this->Aqours->setRakutenEvent($result['Items'], $kind, $keyword);
          }else{
            break;
          }
        }
      }
    }

    // Google処理


    #$this->out('end task');
  }

  /**
   * radio関連情報取得
   */
  public function radio(){
    $now = date('Y-m-d H:i:s');
    $data = array();
    $category = AQOURS_KIND_RADIO;
#    $list = SCRAPING_RADIO_URL;
    // 取得情報はdb管理
    $list = $this->Aqours->getRadio();

    $info = array();
    foreach($list as $key=> $row){
      $url = $row['url'];
      $title = $row['title'];
      $discription = $row['discription'];
      $weekday = $row['weekday'];
      if(!empty($weekday)) {
        $nextday = date('Y-m-d', strtotime($weekday));
      }

      $number = 0;
      $media = $this->Aqours->getMediaFromTitle($title);
      if(!empty($media)) $number = $media['number'];

      if($url == AQOURS_URA_RADIO_URL){
        $html = file_get_contents($url);
        $dom = \phpQuery::newDocument($html);
        // 不要文字削除
        $text1 = trim($dom["#introductionWrap.programCont"]->find("p")->text());
        $serch = array('「ラブライブ！」公式Twitter：@LoveLive_staff',
          'ハッシュタグ：#lovelive');
        $text1 = str_replace($serch,'',$text1);

        $text2 = trim($dom["#personalityWrap.programCont"]->find("h1")->text());

        $text3 = trim($dom["#personalityWrap.programCont"]->find("h2")->text());
        $text3 = str_replace(array("\t", "  "), '', $text3);

        $text = "{$text1}\n\n{$text2}\n\n{$text3}";
        foreach(AQOURS_URA_RADIO_URLS as $links) {
          $text .= "\n\n";
          $text .= $links;
        }

        // タイトルに 回数追加
        if($number != 0){
          $number++;
          $title .= " 第".$number."回";
        }
        $data['kind']  = $category;
        $data['title'] = $title;
        $data['discription'] = $text;
        $data['price'] = '';
        $data['jan']='';
        $data['img']='';
        $data['date'] = date('Y年m月d日', strtotime($nextday));
        $data['push'] = PUSH_READY;
        $data['created'] = $now;

        $info[] = $data;
        $this->Aqours->updateMedia($media['id'], $number);
      }else{
        if($number != 0) $number++;

        // まるごとりかこ処理
        if($url == AIDA_MARUGOTO_RIKAKO){
          $week = $this->Aqours->getWeek($nextday);
          if($week >= 5){
            continue;
          }else{
            $title .= " 第".$number."回";
          }
        }

        // ふわさた処理
        if($url == FUWA_SATA_URL) {
          $title .= " 第" . $number . "回目～♪♪";
        }

        $text = str_replace('%_NUMBER_%',$number,$row['discription']);
        $text .= "\n\n".$url;

        $data['kind']  = $category;
        $data['title'] = $title;
        $data['discription'] = $text;
        $data['price'] = '';
        $data['jan']='';
        $data['img']='';
        $data['date'] = date('Y年m月d日', strtotime($nextday));
        $data['push'] = PUSH_READY;
        $data['created'] = $now;

        $info[] = $data;
        $this->Aqours->updateMedia($media['id'], $number);
      }
    }

    // 追加する情報があれば追加
    if(!empty($info)) $this->Aqours->setInfo($info);
  }

  /**
   * ニコ生取得 月1回取得
   */
  public function niconico(){
    $now = date('Y-m-d H:i:s');
    $data = array();
    $category = AQOURS_KIND_RADIO;

    $url = AQOURS_NICONICO_URL;
    $html = file_get_contents($url);
    $dom = \phpQuery::newDocument($html);

    if(!empty(trim($dom["#channel-main"]->find(".g-live-airtime:eq(0)")->text()))) {
      $dayText = trim($dom["#channel-main"]->find(".g-live-airtime:eq(0)")->text());
      $text = str_replace(array("\n", "\r", "\t", "  ", "放送予定"), '', $dayText);
      $date = substr($text, 0, 5);
      $hour = substr($text, 5, 2);
      $text = "放送予定 " . $date . " " . $hour."時から";
      $year = date('Y');
      $month = date('m');
      if ($month == 12) {
        $year += 1;
      }
      $date = $year . "/" . $date;
      $date = date('Y年m月d日', strtotime($date));

      // title
      $title = trim($dom["#channel-main"]->find("a:eq(1)")->text());
/*
      $text .= "\n\n";
      // 説明
      $text .= trim($dom["#channel-main"]->find(".g-contents:eq(0)")->text());
      $text .= "\n\n";
      // url
      $text .= trim($dom["#channel-main"]->find("a:eq(0)")->attr('href'));
*/
      $data['kind'] = $category;
      $data['title'] = $title;
      $data['discription'] = $text;
      $data['price'] = '';
      $data['jan'] = '';
      $data['img'] = '';
      $data['date'] = $date;
      $data['push'] = PUSH_READY;
      $data['created'] = $now;

      $info[] = $data;

      // 追加する情報があれば追加
      if (!empty($title)) $this->Aqours->setInfo($info);
    }
  }

  /**
   * 毎月　１週目の月〜木曜日に放送
   */
  public function sol(){
    $now = date('Y-m-d H:i:s');
    $yearMonth = date('Ym', strtotime($now . '1 month'));
    $day = $this->Aqours->getFirstMonday($yearMonth);

    $title = "Aqours LOCKS!";
    $text = "毎月1週目はラブライブ!サンシャイン!!より、我が校のスクールアイドルの講師Aqours先生が登場！\nAqours LOCKS!では\"何かのアイドルになるために頑張っている生徒\"からのメッセージを、いつでも待っているぞ！\n\n22時15分ごろから\n\n逢田梨香子\n高槻かなこ\nhttp://www.tfm.co.jp/lock/aqours/";

    $day = date('Y-m-d',strtotime($day));
    $info = array();
    $category = AQOURS_KIND_RADIO;
    for($i=0;$i<4;$i++) {
      $data = array();
      $date = date('Y年m月d日', strtotime($day . "+{$i} day"));
      $data['kind'] = $category;
      $data['title'] = $title;
      $data['discription'] = $text;
      $data['price'] = '';
      $data['jan'] = '';
      $data['img'] = 'al/profile.jpg';
      $data['date'] = $date;
      $data['push'] = PUSH_READY;
      $data['created'] = $now;

      $info[] = $data;
    }

    // 追加する情報があれば追加
    if (!empty($info)) $this->Aqours->setInfo($info);
  }

  /**
   * キャストの生放送番組取得
   */
  public function castnico(){
    $list = $this->Aqours->getNico();

    $now = date('Y-m-d H:i:s');
    $category = AQOURS_KIND_RADIO;
    $info = array();
    $messages = array();
    foreach($list as $data){
      $url = $data['url'];
      $doc = $this->Scraping->getScraping($url);
      if(empty($doc)) continue;

      $title= trim( $doc["section.contents_list.live"]->find("section.sub.future")->find("li")->find("div.item_right")->find("h2")->text() );
      // データ存在チェック
      if($this->Aqours->checkInfoTitle($title)){
        continue;
      }

      $link = trim( $doc["section.contents_list.live"]->find("section.sub.future")->find("li")->find("div.item_right")->find("a")->attr("href") );

      // 日付チェック
      $dateStr = trim( $doc["section.contents_list.live"]->find("section.sub.future")->find("li")->find("div.item_right")->find(".date")->text() );
      if(empty($dateStr)) continue;
      preg_match('/\d{1,2}月\d{1,2}日/', $dateStr, $date);

      if(isset($date[0])) {
        $date = $date[0];
      }

      $year  = date('Y');
      $month = date('m');
      if($month == "11" || $month == "12"){
        if(strpos($date,'01月')){
          $year += 1;
        }
      }

      $discription = $data['discription'];
      if(!empty($link)) $discription .= "\n\n".$link;
#      if(!empty($data['twitter'])) $discription .= "\n\nhttps://twitter.com/".$data['twitter'];

      $day = $year."年".$date;

      $data = array();
      $data['kind'] = $category;
      $data['title'] = $title;
      $data['discription'] = $discription;
      $data['price'] = '';
      $data['jan'] = '';
      $data['img'] = '';
      $data['date'] = $day;
      $data['push'] = PUSH_READY;
      $data['created'] = $now;
      $info[] = $data;

      $message = $title."が".$dateStr."に予定されました。\n\n".$link;
      $messages[] = $message;
    }

    // 追加する情報があれば追加
    if (!empty($info)){
      $this->Aqours->setInfo($info);

      foreach($messages as $text){
        $messageData = $this->Line->setTextMessage($text);
        $this->You->sendMessage($messageData, $this->ACCESS_TOKEN);
      }
    }
  }
}