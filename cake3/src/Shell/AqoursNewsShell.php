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

          // 含まれていないidだけ配列に入れる
          if(!in_array($id, $categoryNewsIds)) {
            $contents[$i]['category'] = $category;
            $contents[$i]['id'] = $id;
            $contents[$i]['title'] = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());
            $contents[$i]['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
            $contents[$i]['html_body'] = htmlspecialchars($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html());
            $contents[$i]['body'] = htmlspecialchars($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text());
          }
        }
      }
    }

    // blukinsert処理
    if(!empty($contents)) $this->Aqours->setNews($contents);
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
              $contents[$i]['category'] = $category;
              $contents[$i]['id'] = ($dom["#contents"]->find(".infobox:eq($i)")->attr("id"));
              $contents[$i]['title'] = ($dom["#contents"]->find(".infobox")->find(".titlebase")->find(".title:eq($i)")->text());
              $contents[$i]['publish_date'] = ($dom["#contents"]->find(".infobox")->find(".date:eq($i)")->text());
              $contents[$i]['html_body'] = htmlspecialchars($dom["#contents"]->find(".infobox:eq($i)")->find("p")->html());
              $contents[$i]['body'] = htmlspecialchars($dom["#contents"]->find(".infobox:eq($i)")->find("p")->text());
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