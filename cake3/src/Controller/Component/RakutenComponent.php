<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

//baseurl
define('BASE_URL', 'https://app.rakuten.co.jp/services/api/_KIND_/Search/20170404?format=json');

//総合
define('TOTAL_BASE', 'BooksTotal');

//楽天ブックス書籍検索
define('BOKK_BASE','BooksBook');

//楽天ブックスCD検索
define('CD_BASE','BooksCD');

//楽天ブックスDVD Blu-Ray検索
define('DVD_BASE','BooksDVD');


/**
 * Rakuten APIに関するコンポーネント
 *
 * Class RakutenComponent
 * @package App\Controller\Component
 */
class RakutenComponent extends Component
{

  /**
   * @param string $kind
   * @return string
   */
  public function getBaseUrl($kind=TOTAL_BASE){
    $baseurl = '';
    switch($kind){
      case BOKK_BASE:
      case CD_BASE:
      case DVD_BASE:
      $baseurl = str_replace('_KIND_', $kind,BASE_URL);
        break;
      default :
        $kind = TOTAL_BASE;
        $baseurl = str_replace('_KIND_', $kind,BASE_URL);
        break;
    }

    return $baseurl;
  }

  /**
   *
   * @param $kind
   * @param $request
   * @return string
   */
  public function setRequestUrl($kind, $keyword){
    $request = $this->getRequest($kind, $keyword);

    $requesturl = $this->getBaseUrl($kind);
    if(!empty($request)){
      foreach($request as $key => $val){
        $requesturl .= '&'.$key.'='.$val;
      }
    }

    // api_idを付与
    $requesturl .= '&applicationId='.RAKUTEN_API_ID;

    return $requesturl;
  }

  /**
   * 不使用
   */
  public function setBookInfo($title){
    $request = [];
    return $request;
  }

  /**
   * CD リクエスト内容設定
   * (アーティスト検索)
   *
   * @param $artistName
   * @param string $sort
   * @return mixed
   */
  public function setCdInfo($artistName, $sort='-releaseDate'){
    //format=json&artistName=Aqours&booksGenreId=002&sort=-releaseDate&applicationId=1031933732911875967
    $request['artistName'] = urlencode($artistName);
    $request['booksGenreId'] = '002';
    $request['sort'] = $sort;

    return $request;
  }

  /**
   * DVD リクエスト内容設定
   * (タイトル検索)
   *
   * @param $title
   * @param string $sort
   * @return mixed
   */
  public function setDvdInfo($title, $sort='-releaseDate'){
    $request['title'] = urlencode($title);
    $request['booksGenreId'] = '003';
    $request['sort'] = $sort;

    return $request;
  }

  /**
   * @param $keyword
   * @param string $sort
   * @return mixed
   */
  public function setTotalInfo($keyword, $sort='-releaseDate'){
    $request['keyword'] = urlencode($keyword);
    $request['booksGenreId'] = '000';
    $request['sort'] = $sort;

    return $request;
  }

  /**
   * @param $kind
   * @param $keyword
   * @return array|mixed
   */
  public function getRequest($kind, $keyword){
    $request = [];
    switch($kind){
      case BOKK_BASE:
        $request = $this->setBookInfo($keyword);
        break;
      case CD_BASE:
        $request = $this->setCdInfo($keyword);
        break;
      case DVD_BASE:
        $request = $this->setDvdInfo($keyword);
        break;
      default :
        $kind = TOTAL_BASE;
        $request = $this->setTotalInfo($keyword);
        break;
    }

    return $request;
  }
}