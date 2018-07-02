<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * Class ScraipingComponent
 * @package App\Controller\Component
 */
class ScraipingComponent extends Component
{

  /**
   * @param $url
   * @return array|\phpQueryObject|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery
   */
  public function getScraping($url){

    $html = self::get_curl($url);

    if(empty($html)) return array();
    // phpQueryのドキュメントオブジェクトを生成
    $doc = \phpQuery::newDocument($html);

    return $doc;
  }

  /**
   * @param $url
   * @return array|mixed
   */
  public function get_curl($url){
    $option = [
      CURLOPT_RETURNTRANSFER => true, //文字列として返す
      CURLOPT_TIMEOUT        => 3, // タイムアウト時間
    ];

    $curl = curl_init($url);
    curl_setopt_array($curl, $option);

    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_BINARYTRANSFER,1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);

    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
    $result  = curl_exec($curl);
    $info    = curl_getinfo($curl);
    $errorNo = curl_errno($curl);

    // OK以外はエラーなので空白配列を返す
    if ($errorNo !== CURLE_OK) {
      // 詳しくエラーハンドリングしたい場合はerrorNoで確認
      // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
      return [];
    }

    // 200以外のステータスコードは失敗とみなし空配列を返す
    if ($info['http_code'] !== 200) {
      return [];
    }

    return $result;
  }

  /**
   * @param $url
   * @param array $posts
   * @param string $nextUrl
   * @return array|\phpQueryObject|\QueryTemplatesParse|\QueryTemplatesSource|\QueryTemplatesSourceQuery
   */
  public function postScraping($url, $posts=[], $nextUrl=''){

    $html = self::post_curl($url, $posts, $nextUrl);

    if(empty($html)) return array();
    // phpQueryのドキュメントオブジェクトを生成
    $doc = \phpQuery::newDocument($html);

    return $doc;
  }

  /**
   * @param $url
   * @return array|mixed
   */
  public function post_curl($url, $posts=[], $nextUrl=''){
    $option = [
      CURLOPT_RETURNTRANSFER => true, //文字列として返す
      CURLOPT_TIMEOUT        => 3, // タイムアウト時間
    ];

    $curl = curl_init($url);
    curl_setopt($curl,CURLOPT_POST, TRUE);
    curl_setopt_array($curl, $option);

    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($posts));
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  //
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl,CURLOPT_COOKIEJAR,      '/tmp/post.cookie');
    curl_setopt($curl,CURLOPT_COOKIEFILE,     '/tmp/post.cookie');
    curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡

    $result  = curl_exec($curl);
    $info    = curl_getinfo($curl);
    $errorNo = curl_errno($curl);

    // OK以外はエラーなので空白配列を返す
    if ($errorNo !== CURLE_OK) {
      // 詳しくエラーハンドリングしたい場合はerrorNoで確認
      // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
      return [];
    }

    // 200以外のステータスコードは失敗とみなし空配列を返す
    if ($info['http_code'] !== 200) {
      return [];
    }
    curl_close($curl);

    if(!empty($nextUrl)){
      $curl = curl_init($nextUrl);
      curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);  // オレオレ証明書対策
      curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, FALSE);  //
      curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($curl,CURLOPT_COOKIEJAR,      '/tmp/post.cookie');
      curl_setopt($curl,CURLOPT_COOKIEFILE,     '/tmp/post.cookie');
      curl_setopt($curl,CURLOPT_FOLLOWLOCATION, TRUE); // Locationヘッダを追跡

      $result  = curl_exec($curl);
      $info    = curl_getinfo($curl);
      $errorNo = curl_errno($curl);

      // OK以外はエラーなので空白配列を返す
      if ($errorNo !== CURLE_OK) {
        // 詳しくエラーハンドリングしたい場合はerrorNoで確認
        // タイムアウトの場合はCURLE_OPERATION_TIMEDOUT
        return [];
      }

      // 200以外のステータスコードは失敗とみなし空配列を返す
      if ($info['http_code'] !== 200) {
        return [];
      }
    }
    return $result;
  }
}