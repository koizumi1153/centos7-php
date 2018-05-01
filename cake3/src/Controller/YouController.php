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
  public $components = ["Amazon","Aqours","Line","Lottery", "Rakuten","You" ];

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

      if($userName === false) {
        $userName = '';
      }
      $this->You->setUsers($userId, $userName);

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
            $messageData = $this->You->getWeathersMessage($userId);
            break;

          case WORDS:
            $messageData = $this->You->getWordsMessage();
            break;

          case PUSH:
            // action を2つ定義
            $actions[] = $this->Line->confirmAction('設定する', '通知設定する');
            $actions[] = $this->Line->confirmAction('設定しない', '通知設定しない');

            $template = $this->Line->setConfirm('通知設定変更', $actions);
            $messageData = $this->Line->setTemplate($template, "通知設定変更できません");
            break;

          case PUSHON:
            $messageData = $this->You->setPushFlg($userId, 1);
            break;
            
          case PUSHOFF:
            $messageData = $this->You->setPushFlg($userId, 0);
            break;

          case PUSHTIME:

            $template = $this->Line->setButton('通知時間変更','通知時間を変更します',[ACTION_DATE_TIME]);
            $messageData = $this->Line->setTemplate($template, "通知時間変更できません");
            break;

            #            $messageData = $this->Line->setDatetimepicker('PUSH時間変更',POSTBACK_SELECT_PUSH_TIME, SELECT_TIME,'23:00','00:00','09:00');
            break;

          case AQOURS_LIVE_URL:
            $messageData = $this->You->getAqoursLiveUrl($userId);
              break;
          default:
            $messageData = $this->You->getWordsMessage($kind);
            break;
        }
      }elseif($messageType== 'location') {
        // 位置情報
        $latitude  = $event['message']['latitude'];
        $longitude = $event['message']['longitude'];
        $this->You->setLocation($userId, $latitude, $longitude);

        $text = "位置情報を登録したよ。";
        $messageData = $this->Line->setTextMessage($text);
      }else{
        // text以外
        $messageData = $this->You->getWordsMessage();
      }
    }elseif($type == "postback"){
        $postback = $event['postback'];
        $messageData = $this->You->getPostBackMessage($userId, $postback);
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

  public function amazon(){
    $this->autoRender = false;
    $searchIndex="DVD";
    $keywords = "ラブライブ！サンシャイン！！";

    $url = $this->Amazon->setRequest($searchIndex, $keywords);
    if(!empty($url)){
      $result = simplexml_load_string(file_get_contents($url));
    }

    print_r($result);exit;
  }

  public function weather($userId){

  }

  public function test($saleDate='2018-03-20'){
    echo $this->Aqours->dateCheck($saleDate);
    exit;
  }

  /**
   * add メソッド
   */
  public function add(){
    $post = $this->request->getData();
    if(!empty($post)){
      //処理記述
      if(!empty($post['title'])) {
        $data = array();

        $data['kind'] = $post['kind'];
        $data['title'] = $post['title'];
        $data['discription'] = $post['discription'];
        $data['date'] = $post['date'];

        $data['price'] = "";
        $data['jan'] = "";
        $data['img'] = "";
        $data['push'] = PUSH_NONE;
        $data['created'] = date('Y-m-d H:i:s');

        $lists[] = $data;
        $this->Aqours->setInfo($lists);
      }
    }
  }

}