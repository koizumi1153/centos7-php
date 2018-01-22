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

    $this->out('start task');

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
            break 2;
          }
        }
      }
    }

    // Google処理


    $this->out('end task');
  }
}