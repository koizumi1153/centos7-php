<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * LINE Message APIに関するコンポーネント
 *
 * Class LineComponent
 * @package App\Controller\Component
 */
class LineComponent extends Component
{

    /**
     * @param $text
     * @param array $messageData
     * @return array
     */
    public function setTextMessage($text, $messageData = array()){
      $text = str_replace("n", "%0D%0A", $text);
      $message = [
        'type' => 'text',
        'text' => $text,
      ];

      $messageData[] = $message;
      return $messageData;
    }


    /**
     * @param $replyToken
     * @param $messageData
     * @return array
     */
    public function setResponse($replyToken, $messageData){
      $response = [
        'replyToken' => $replyToken,
        'messages'   => $messageData
      ];

      return $response;
    }

    /**
     * メッセージ送信
     *
     * @param $api
     * @param $response
     * @param $ch
     */
    public function sendMessage($uri, $response, $accessToken){
      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);

      curl_close($ch);

      return $result;
    }


    /**
     * プッシュ通知
     *
     * @param $uri
     * @param $accessToken
     * @param $toUser
     * @param $messageData
     */
    public function sendPush($uri, $accessToken, $toUser, $messageData){
      $response = [
        'to' => $toUser,
        'messages' => $messageData
      ];

      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);
    }


    /**
     * プロフィール取得
     *
     * @param $accessToken
     * @param $userId
     * @return bool
     */
    public function getProfileName($accessToken, $userId){
      return 'koizumi';

      $uri = "https://api.line.me/v2/bot/profile/{$userId}";
      $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charser=UTF-8',
        'Authorization: Bearer ' . $accessToken
      ));
      $result = curl_exec($ch);

      if(isset($result['displayName'])) return $result['displayName'];

      return false;
    }


  /**
   * 画像
   *
   * @param $image
   * @param $preview
   * @param array $messageData
   * @return array
   */
  public function setImgMessage($image, $preview, $messageData = array()){
    $message = [
      'type' => 'image',
      'originalContentUrl' => $image,
      'previewImageUrl'    => $preview,
    ];

    $messageData[] = $message;
    return $messageData;
  }

  /**
   * 位置情報
   *
   * @param $location
   * @return array
   */
  public function setLocationMessage($location, $messageData = array()){
    $message = [
      'type' => 'location',
      'title'     => $location['name'],
      'address'   => $location['address'],
      'latitude'  => $location['latitude'],
      'longitude' => $location['longitude'],
    ];

    $messageData[] = $message;
    return $messageData;
  }
}