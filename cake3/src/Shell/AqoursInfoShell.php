<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;

class AqoursInfoShell extends Shell
{
  // アクセストークン
  protected $ACCESS_TOKEN = 'Fi3v81mkVQooM1wF9l2P4+aSWaYJFumNi4Vr3DwwMU1wSETxbTPn9HPDc64WCHujPM1XqLsPyN0oZuaIsJ6oqEYWsOl9U3gZXbbgJss8tfqPi0B/afR0kIt1pTmvM+kYCvAZEwqz5Cg7g5ecZ0hCBAdB04t89/1O/w1cDnyilFU=';
  // 管理者ID
  protected $ADMIN_USER = 'Ub0d8aab0fefa54f6dbb51a7a3543899e';

  public function initialize() {
    // component
    $this->Aqours = new AqoursComponent(new ComponentRegistry());
    $this->Line   = new LineComponent(new ComponentRegistry());
    $this->You    = new YouComponent(new ComponentRegistry());
  }

  /**
   * 当日情報PUSH
   */
  public function main()
  {
    $time = date('H'); //時間取得

    // 日付取得
    $day = date('Y年m月d日');
#    $day = '2018年01月26日'; //test用
    $data = $this->Aqours->getInformationDate($day);
    $kinds = [];
    $kindMembers = [];

    if(!empty($data)) {
      foreach($data as $row){
        $pushKind = $this->Aqours->getPushKind($row['kind']);

        //member指定がある場合
        if(!empty($row['member_ids'])){
          $kindMembers[$pushKind][] = $row;
        }else {
          // 種別のみ
          $kinds[$pushKind][] = $row;
        }
      }

      // 指定処理なし
      if(!empty($kinds)) {
        foreach ($kinds as $kind => $val) {
          $messageData = $this->You->setPushMessage($val);
          $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, $kind);
        }
      }

      // member指定処理
      if(!empty($kindMembers)){
        foreach ($kindMembers as $kind => $val) {
          foreach($val as $key => $row) {
            $messageData = $this->You->setPushMessage(array($row));
            $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, $kind, $row['member_ids']);
          }
        }
      }

    }
  }

  /**
   * 週間情報PUSH
   * 取得日から7日間のデータを取得する
   * 返すのは1メッセージ(量が多くなっても見ずらくなりそうなので
   *
   * ここを修正するか悩みどころ
   */
  public function week(){
    $days = [];
    // 日付取得
    $today = date('Y-m-d 00:00:00');
    for($i=0;$i<7;$i++){
      $days[] = date('Y年m月d日', strtotime('+'.$i.' day',strtotime($today)));
    }

    $data = $this->Aqours->getInformationWeek($days);
    if(!empty($data)) {

      $messageData = $this->You->setPushMessageWeek($data);
      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN);
    }
  }

  /**
   * 全員にPUSH
   */
  public function sell(){
    $data = $this->Aqours->getInformationPush();
    if(!empty($data)) {

      // 登録されてPUSHされていないモノ
      $messageData = $this->You->setPushMessageWeek($data, true);
      // PUSH(管理者のみ)
      //$this->Line->sendPush(LINE_API_PUSH_URL, $this->ACCESS_TOKEN, $this->ADMIN_USER, $messageData);

      // 特定ユーザーにのみ
      $this->You->sendMessage($messageData, $this->ACCESS_TOKEN, PUSH_SELL_REGISTRATION);

      // フラグ更新
      $this->Aqours->updatePush();
    }
  }
}