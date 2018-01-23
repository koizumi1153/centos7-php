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
  public $components = ["Aqours","Line", "Rakuten","You" ];

  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  protected $DEVELOP_USER_ID = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';

  public function index()
  {
    $this->autoRender = false;

    $messageData = array();
    $request = $this->request->data;

    // ユーザーから送られてきたデータ
    $event = $request['events'][0];
    $type  = $event['type'];
    $replyToken = $event['replyToken'];

    $userId = $event['source']['userId'];

    if($type == 'follow'){
      $userName = $this->Line->getProfileName($this->ACCESS_TOKEN, $userId);

      $this->You->setUsers($userId,$userName);

      $text = <<<EOT
YOUだよ、よろしくね。
CD販売情報とか、イベント情報をPUSHするよ。
位置情報を教えてくれたら天気予報もするかもね。
EOT;
      $messageData = $this->Line->setTextMessage($text, $messageData);
    }elseif($type == 'unfollow'){
      $this->You->deleteUser($userId);
    }elseif($type == "message"){
      $messageType = $event['message']['type'];
      if($messageType == 'text') {
        // text
        $text = $event['message']['text'];

        // 大分類取得 複数ある場合はランダム
        $kindMaster = $this->You->getKinds();
        $kind = $this->Lottery->lotteryJson($kindMaster, $text);

        switch ($kind) {

          case WEATHERS:
            $messageData = $this->You->getWeathersMessage();
            break;

          case WORDS:
            $messageData = $this->You->getWordsMessage();
            break;

          default:
            $messageData = $this->You->getWordsMessage($kind);
            break;
        }if($messageType== 'location') {
          // 位置情報
          $latitude  = $event['message']['latitude'];
          $longitude = $event['message']['longitude'];
          $this->You->setLocation($userId, $latitude, $longitude);
        }

      }else{
        // text以外
        $messageData = $this->You->getWordsMessage();
      }
    }


    // 返信可能な場合に処理する
    if(!empty($messageData)) {
      $response = $this->Line->setResponse($replyToken, $messageData);
      $this->Line->sendMessage(LINE_API_URL, $response, $this->ACCESS_TOKEN);
    }

    echo 200;
  }

  /**
   * amazonAPI
   * 商品List取得
   */
  public function line($keyword="ラブライブ!サンシャイン!!", $kind=DVD_BASE){
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

  public function info($dbKind=AQOURS_KIND_CD){
    $information = $this->Aqours->getDataFromKind($dbKind);
    $this->set(compact('information'));

    $this->set('title', $this->Aqours->getTitle($dbKind));
    $this->render('info');
  }

}