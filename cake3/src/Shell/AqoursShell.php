<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\Component;

class AqoursShell extends Shell
{
  public $components = ["Aqours","Rakuten" ];

  public function main()
  {
    parent::initialize();
    $this->out('start task');

    // 楽天処理
    $keywords=AQOURS_KEYWORDS;
    $rakuten_kind=AQOURS_RAKUTEN_KIND;
    foreach($keywords as $keyword){
      foreach($rakuten_kind as $kind){
        $url = $this->Rakuten->setRequestUrl($kind, $keyword);
        if(!empty($url)){
          $curl = curl_init();

          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列>で返す
          $response = curl_exec($curl);
          $result = json_decode($response, true);

          curl_close($curl);
        }
        if(!empty($result['Items'])) $this->Aqours->setRakutenEvent($result['Items'], $kind);
      }
    }

    // Google処理


    $this->out('end task');
  }
}