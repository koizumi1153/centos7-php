<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Project You Controller
 *
 * Class YouController
 * @package App\Controller
 */
class YouController
{
  public $components = ["Amazon.Pas","Line", "You", ];

  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  protected $DEVELOP_USER_ID = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';

  public function action_index(){

  }

  /**
   * amazonAPI
   * 商品List取得
   */
  public function action_list($keyword="ラブライブ サンシャイン amazon.co.jp限定", $sort="-orig-rel-date", $search="DVD"){
    $this->Pas->setLocale(LOCALE_JAPAN);

    //検索条件入力
    $parameters = array(
      'Keywords' => urlencode($keyword),
      'Sort' => $sort,
      'SearchIndex' => $search,

    );

    //AmazonAPI検索用URLを作成
    $url = $this->Pas->itemLookup($parameters);

    $xml = Xml::build($url);
    $result = Xml::toArray($xml);

    print_r($result);
  }
}