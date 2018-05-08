<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\LineComponent;
use App\Controller\Component\YouComponent;
use App\Controller\Component\TwitterComponent;

class AqoursLiveShopShell extends Shell
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
    $this->Twitter= new TwitterComponent(new ComponentRegistry());
  }

  /**
   * Twitter取得　番号通知PUSH
   */
  public function main()
  {
    $now = date('Y-m-d H:i:s');
    // master取得
    $master = $this->Aqours->getLiveShop();

    if(!empty($master)) {
      foreach($master as $shop) {
        // shopから時間チェック
        $start_date = $shop['start_date']->i18nFormat('yyyy-MM-dd HH:mm:ss');
        $end_date = $shop['end_date']->i18nFormat('yyyy-MM-dd HH:mm:ss');
        // 期間チェック
        if($now >= $start_date && $now < $end_date) {
          // 対象者チェック
          $screen_name = $shop['screen_name'];

          $shopNumbers = 0;
          $shopNumber = $this->Aqours->getShopNumber($shop['id']);
          if(!empty($shopNumber)){
            $shopNumbers = $shopNumber['notification_number'];
          }
          if(!empty($screen_name)) {
            // タイムライン取得
            $timeline = $this->Twitter->getUserTimeline($screen_name);
            $num = $this->Aqours->checkTweet($timeline,$now);
            // 保存した数字より大きい場合に処理を行う
            if($num > $shopNumbers) {
              if($shopNumbers != 0) {
                $shopNumber['notification_number'] = $num;
                $this->Aqours->updateShopNumber($shopNumber['id'],$num);
              }else{
                $this->Aqours->setShopNumber($shop['id'],$num);
              }

              $checkUsers = $this->Aqours->checkUserNumber($shop['id'], $num);
              $user_ids = array_unique(array_column($checkUsers,'user_id'));
              foreach($user_ids as $user_id) {
                $number_text = "現在、整理券番号".$num."番まで呼び出しています。\n\n";
                $number_text .= "https://twitter.com/".$screen_name;
                $messageData = $this->Line->setTextMessage($number_text);

                // PUSH
                $this->Line->sendPush(LINE_API_PUSH_URL, $this->ACCESS_TOKEN, $user_id, $messageData);
              }

              $this->Aqours->updateUserNumber($shop['id'], $num);
            }
          }
        }
      }
    }
  }
  
}