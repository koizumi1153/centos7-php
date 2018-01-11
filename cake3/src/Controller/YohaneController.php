<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Project Yohane Controller
 *
 * Class YohaneController
 * @package App\Controller
 */
class YohaneController extends AppController
{
    public $components = ["Line", "Yohane", "Lottery"];

    // アクセストークン
    protected $ACCESS_TOKEN = '13bTCNWZihALuDoUD+FbAEd35wTdQFeoUweuyq2o48I3MRbuNDOi+eEYBcOYUnouKnETI2dAcy9LomTbRfL9tNEzy1P0SQweaFn2ZxfiFUBg/HGQaPsMbEp99QrJkEtbPRyuTnGaVDaYm++Ktg0b4wdB04t89/1O/w1cDnyilFU=';
    protected $DEVELOP_USER_ID = 'U25b5b3882f340670c21f6e3e74551b61';

   public function index()
    {
      $this->autoRender = false;

      $messageData = array();
      $request = $this->request->data;
      $uri = 'https://api.line.me/v2/bot/message/reply';

      // ユーザーから送られてきたデータ
      $event = $request['events'][0];
      $type  = $event['type'];
      $replyToken = $event['replyToken'];

      $userId = $event['source']['userId'];

      if($type == 'follow'){
        $userName = $this->Line->getProfileName($this->ACCESS_TOKEN, $userId);

        $this->Yohane->setUsers($userId,$userName);

        $text = <<<EOT
フフフ。
これであなたもヨハネのリトルデーモンになったわね。
一緒に堕天しよっ。
EOT;
        if($userName) $text = str_replace('あなた', $userName, $text);
        $messageData = $this->Line->setTextMessage($text, $messageData);
      }elseif($type == 'unfollow'){
        $this->Yohane->deleteUser($userId);
      }elseif($type == "message"){
        $messageType = $event['message']['type'];
        if($messageType == 'text') {
          // text
          $text = $event['message']['text'];

          // 大分類取得 複数ある場合はランダム
          $kindMaster = $this->Yohane->getKinds();
          $kind = $this->Lottery->lotteryJson($kindMaster, $text);

          switch($kind){
            case FORTUNE:
              $messageData = $this->Yohane->getFortuneMessage();
              break;

            case WEATHERS:
              $messageData = $this->Yohane->getWeathersMessage();
              break;

            case MAPS:
              $messageData = $this->Yohane->getMapsMessage();
              break;

            case WORDS:
              $messageData = $this->Yohane->getWordsMessage();
              break;
          }


        }else{
          // text以外
        }
      }


      // 返信可能な場合に処理する
      if(!empty($messageData)) {
        $response = $this->Line->setResponse($replyToken, $messageData);
        $this->Line->sendMessage($uri, $response, $this->ACCESS_TOKEN);
      }

      echo 200;
    }
}