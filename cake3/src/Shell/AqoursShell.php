<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\RakutenComponent;

class AqoursShell extends Shell
{

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->Rakuten = new RakutenComponent(new ComponentRegistry());
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
    $list = SCRAPING_RADIO_URL;

    $info = array();
    foreach($list as $url => $title){
      $html = file_get_contents($url);
      $dom = \phpQuery::newDocument($html);

      $number = 0;
      $media = $this->Aqours->getMediaFromTitle($title);
      if(!empty($media)) $number = $media['number'];

      if($url == AQOURS_URA_RADIO_URL){
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
        $data['date'] = date('Y年m月d日', strtotime('next wednesday'));
        $data['push'] = PUSH_READY;
        $data['created'] = $now;

        $info[] = $data;
        $this->Aqours->updateMedia($media['id'], $number);
      }elseif($url == AIDA_MARUGOTO_RIKAKO){
        $nextday = date('Y-m-d', strtotime('next thursday'));
        $week = $this->Aqours->getWeek($nextday);

        // 5週目の更新はないので…
        if($week < 5){
          // タイトルに 回数追加
          if($number != 0){
            $number++;
            $title .= " 第".$number."回";
          }

          $text = "逢田梨香子の良いトコロも悪いトコロもまるごとお届け！\nリスナーの皆様からのレシピにより、逢田梨香子がおいしく料理されちゃうラジオ番組！\n\n";
          $text .= $url;

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

      $this->Aqours->setInfo($info);
    }

  }
}