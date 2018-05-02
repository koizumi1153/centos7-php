<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Project You 関連のコンポーネント（主にDB操作）
 *
 * Class YouComponent
 * @package App\Controller\Component
 */
class YouComponent extends Component
{
  public $components = ["Line", "Lottery", "WeatherMap"];

  /** @var string */
  protected $USERS = 'YouUsers';

  /** @var string */
  protected $KINDS = 'YouKinds';

  /** @var string */
  protected $WORDS = 'YouWords';

  public function initialize(array $config)
  {
    $this->Users = TableRegistry::get($this->USERS);
    $this->Kinds = TableRegistry::get($this->KINDS);
    $this->Words = TableRegistry::get($this->WORDS);
  }

  /**
   * @param $userId
   * @param string $name
   */
  public function setUsers($userId, $name = '')
  {
    $user = $this->Users->newEntity();
    $user->set([
      'user_id' => $userId,
      'name' => $name,
      'created' => date('Y-m-d H:i:s')
    ]);

    $this->Users->save($user);
  }

  /**
   * @param $userId
   * @return mixed
   */
  public function getUsers($userId)
  {
    $query = $this->Users->find();
    $query->where(['user_id' => $userId]);
    $query->where(['deleted IS NULL']);

    $user = $query->first();
    return $user;
  }

  /**
   * @param $userId
   */
  public function setUserHash($userId){
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();
    $hash = md5($userId);

    $query->update()
      ->set(['hash' => $hash])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * @param $hash
   * @return mixed
   */
  public function getUserHash($hash){
    $query = $this->Users->find();
    $query->where(['hash' => $hash]);
    $query->where(['deleted IS NULL']);

    $user = $query->first()->toArray();
    return $user;

  }

  /**
   * @param $userId
   */
  public function deleteUser($userId)
  {
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();

    $query->update()
      ->set(['deleted' => $now])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * @return mixed
   */
  public function getKinds()
  {
    $query = $this->Kinds->find();
    $query->where(['deleted IS NULL']);
    $kind = $query->hydrate(false)->toArray();

    return $kind;
  }

  /**
   * @param $kindId
   * @param int $priority
   * @return mixed
   */
  public function getWords($kindId, $priority = 0)
  {
    $query = $this->Words->find();
    $query->where(['kind_id' => $kindId]);
    $query->where(['priority' => $priority]);
    $words = $query->hydrate(false)->toArray();

    return $words;
  }

  /**
   * 天気情報を 緯度・軽度から取得する
   * @param $latitude
   * @param $longitude
   * @return mixed
   */
  public function getWeathers($latitude, $longitude)
  {
    $url = $this->WeatherMap->getWeatherUrlFromLatAndLon($latitude, $longitude);
    $weather = json_decode(file_get_contents($url), true);

    return $weather;
  }

  /**
   * 天気コメントを 緯度・軽度から取得する
   */
  public function getWeathersMessage($userId)
  {
    $messageData = array();

    $userDataId = 0;
    $user = self::getUsers($userId);
    if (!empty($user)) {
      $userDataId = $user['id'];
      $latitude = $user['latitude'];
      $longitude = $user['longitude'];
    }

    if (!empty($userDataId) && !empty($latitude) && !empty($longitude)) {
      $weather = $this->getWeathers($latitude, $longitude);
      $text = $this->WeatherMap->getWeatherText($weather);
      $messageData = $this->Line->setTextMessage($text, $messageData);
    } else {
      $text = "位置情報を教えてね";
      $messageData = $this->Line->setTextMessage($text, $messageData);
    }

    return $messageData;
  }

  /**
   * 会話用 適当に返すだけ
   *
   * @return array
   */
  public function getWordsMessage($kindId = WORDS)
  {
    $messageData = array();

    $wordsMaster = self::getWords($kindId, PRIORITY_DEFAULT);
    $word = $this->Lottery->lotteryMaster($wordsMaster);
    if (!empty($word)) {
      $text = $word['word'];
      $messageData = $this->Line->setTextMessage($text, $messageData);
    }

    return $messageData;
  }

  /**
   * PUSH 可能ユーザー数取得
   * @return mixed
   */
  public function getPushUsersCount($time='')
  {
    $query = $this->Users->find();
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);

    if(!empty($time)) $query->where(['push_time' => $time]);

    $total = $query->count();
    return $total;
  }

  /**
   * PUSH可能ユーザー取得
   * @param $page
   * @return mixed
   */
  public function getPushUsers($page, $time='')
  {
    $query = $this->Users->find()->select(['user_id']);
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);
    $query->order(['id' => 'ASC']);
    $query->limit(LINE_MULTI_USER)->page($page);

    if(!empty($time)) $query->where(['push_time' => $time]);

    $users = $query->hydrate(false)->toArray();
    return $users;
  }

  /**
   * 緯度経度登録
   *
   * @param $userId
   * @param $latitude
   * @param $longitude
   */
  public function setLocation($userId, $latitude, $longitude)
  {
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();

    $query->update()
      ->set(['latitude' => $latitude])
      ->set(['longitude' => $longitude])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 情報通知メッセージ
   *
   * @param $data
   * @return array
   */
  public function setPushMessage($data)
  {
    $messageData = [];
    $sell = array(AQOURS_KIND_BOOK, AQOURS_KIND_CD, AQOURS_KIND_DVD, AQOURS_KIND_GOODS);

    foreach ($data as $key => $row) {

      if (in_array($row['kind'], $sell)) {
        $text = "{$row['title']}が{$row['date']}に発売だよ。\n\n{$row['discription']}";
      } else {
        $text = "{$row['title']}が{$row['date']}にあるよ。\n\n{$row['discription']}";
      }
      $messageData = $this->Line->setTextMessage($text, $messageData);
      if (!empty($row['img'])) {
        $image = AQOURS_IMG_URL . $row['img'];
        $preview = AQOURS_IMG_URL . $row['img'];
        $messageData = $this->Line->setImgMessage($image, $preview, $messageData);
      }
    }

    return $messageData;
  }

  /**
   * blog更新通知メッセージ
   *
   * @param $data
   * @return array
   */
  public function setPushBlogMessage($data)
  {
    $messageData = [];
    $name = AQOURS_BLOG_NAMES;
    foreach ($data as $key => $row) {
      $text = '';
      if(isset($name[$key])){
        $text .= $name[$key]."\n";
      }
      $text .= $row['title']."\n";
      $text .= $row['link'];
      $messageData = $this->Line->setTextMessage($text, $messageData);
    }
    return $messageData;
  }

  /**
   * 週間情報通知メッセージ
   *
   * @param $data
   * @return array
   */
  public function setPushMessageWeek($data, $adminFlg=false)
  {
    $messageData = [];
    $sell = array(AQOURS_KIND_BOOK, AQOURS_KIND_CD, AQOURS_KIND_DVD);

    $count = 0;
    $text = "";
    if($adminFlg) $text .= "以下の情報が登録されました。\n";
    foreach ($data as $key => $row) {

      if($count != 0) $text .= "\n\n";
      if (in_array($row['kind'], $sell)) {
        $text .= "{$row['title']}が{$row['date']}に発売だよ。";
      } else {
        $text .= "{$row['title']}が{$row['date']}にあるよ。";
      }
      $count++;
    }

    // 週間情報は1回で送信する。
    if(!empty($text)) {
      $messageData = $this->Line->setTextMessage($text, $messageData);
    }

    return $messageData;
  }


  /**
   * postback返答処理
   *
   * @param $postback
   * @return array
   */
  public function getPostBackMessage($userId, $postback){
    $messageData = [];

    $data = $postback['data'];
    if($data == POSTBACK_SELECT_PUSH_TIME){
      // push 時間変更
      if(isset($data['time'])) {
        $messageData = self::setPushTime($userId, $data['time']);
      }
    }

    return $messageData;
  }

  /**
   * @param $userId
   * @param $time
   * @return mixed
   */
  public function setPushTime($userId, $time){
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();

    $query->update()
      ->set(['push_time' => $time])
      ->set(['updated' => $now])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();

    $text = "通知時間を{$time}に設定したよ。";
    $messageData = $this->Line->setTextMessage($text);
    return $messageData;
  }

  /**
   * push_flg変更
   * @param $userId
   * @param string $flg
   * @return mixed
   */
  public function setPushFlg($userId, $flg='0'){
    $now = date('Y-m-d H:i:s');
    $query = $this->Users->query();

    $query->update()
      ->set(['push_flg' => $flg])
      ->set(['updated' => $now])
      ->where(['user_id' => $userId])
      ->where(['deleted IS NULL'])
      ->execute();

    if($flg) {
      $text = "通知を設定したよ。";
    }else {
      $text = "通知を解除したよ。";
    }
    $messageData = $this->Line->setTextMessage($text);
    return $messageData;
  }

  /**
   * @param $messageData
   * @param $access_token
   */
  public function sendMessage($messageData, $access_token){
    if(!empty($messageData)) {
      // ユーザー取得
      $userCount = $this->getPushUsersCount();
      if ($userCount > 0) {
        $allPage = ceil($userCount / LINE_MULTI_USER);
        for ($page = 1; $page <= $allPage; $page++) {
          $user = $this->getPushUsers($page);
          $userIds = array_column($user, 'user_id');

          // PUSH
          if (count($messageData) > LINE_MESSAGE_COUNT) {
            $messages = array_chunk($messageData, LINE_MESSAGE_COUNT);
            foreach ($messages as $message) {
              $this->Line->sendPush(LINE_API_MULTI_URL, $access_token, $userIds, $message);
            }
          } else {
            $this->Line->sendPush(LINE_API_MULTI_URL, $access_token, $userIds, $messageData);
          }
        }
      }
    }
  }

  /**
   * ライブ物販整理券入力画面
   * @param $userId
   * @return array
   */
  public function getAqoursLiveUrl($userId){
    $messageData = array();

    //hash確認
    $user = self::getUsers($userId);
    if(empty($user['hash'])){
      self::setUserHash($userId);
    }

    // url生成
    $text  = "こちらにて整理券番号を確認・登録してください。\n\n";
    $text .= "https://line.yohane.work/aqours/set/".md5($userId);
    $messageData = $this->Line->setTextMessage($text, $messageData);

    return $messageData;
  }

}