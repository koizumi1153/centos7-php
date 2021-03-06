<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

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
      case BOOK_BASE:
      case CD_BASE:
      case DVD_BASE:
      case MAGAZINE_BASE:
      $baseurl = str_replace('_KIND_', $kind,RAKUTEN_BASE_URL);
        break;
      default :
        $kind = TOTAL_BASE;
        $baseurl = str_replace('_KIND_', $kind,RAKUTEN_BASE_URL);
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
  public function setRequestUrl($kind, $keyword, $page=1){
    $request = $this->getRequest($kind, $keyword);

    $requesturl = $this->getBaseUrl($kind);
    if(!empty($request)){
      foreach($request as $key => $val){
        $requesturl .= '&'.$key.'='.$val;
      }
    }

    if($page != 1){
      $requesturl .= '&page='.$page;
    }

    // api_idを付与
    $requesturl .= '&applicationId='.RAKUTEN_API_ID.'&affiliateId='.RAKUTEN_AFFILIATEID;

    return $requesturl;
  }

  /**
   * 本 リクエスト内容設定
   */
  public function setBookInfo($keyword, $sort='-releaseDate'){
    $request['title'] = urlencode($keyword);
    $request['booksGenreId'] = '001';
    $request['sort'] = urlencode($sort);
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
    //format=json&artistName=AqoursComponent&booksGenreId=002&sort=-releaseDate&applicationId=1031933732911875967
    $request['artistName'] = urlencode($artistName);
    $request['booksGenreId'] = '002';
    $request['sort'] = urlencode($sort);

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
    $request['sort'] = urlencode($sort);

    return $request;
  }

  /**
   * 雑誌 リクエスト内容設定
   */
  public function setMagazineInfo($keyword, $sort='-releaseDate'){
    $request['title'] = urlencode($keyword);
    $request['booksGenreId'] = '007';
    $request['sort'] = urlencode($sort);
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
    $request['sort'] = urlencode($sort);

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
      case BOOK_BASE:
        $request = $this->setBookInfo($keyword);
        break;
      case CD_BASE:
        $request = $this->setCdInfo($keyword);
        break;
      case DVD_BASE:
        $request = $this->setDvdInfo($keyword);
        break;
      case MAGAZINE_BASE:
        $request = $this->setMagazineInfo($keyword);
        break;
      default :
        $kind = TOTAL_BASE;
        $request = $this->setTotalInfo($keyword);
        break;
    }

    return $request;
  }
}