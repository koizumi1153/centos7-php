<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Project You Controller
 *
 * Class YouController
 * @package App\Controller
 */
class YouController extends AppController
{
  public $components = ["Line", "Rakuten" ];

  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  protected $DEVELOP_USER_ID = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';

  public function index(){
    $this->autoRender = false;
  }

  /**
   * amazonAPI
   * 商品List取得
   */
  public function line($keyword="ラブライブ!サンシャイン!!", $sort="-orig-rel-date", $kind=DVD_BASE){
    $this->autoRender = false;

    //RakutenAPI検索用URLを作成
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

    print_r($result['Items']);exit;
  }
}