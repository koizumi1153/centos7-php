<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

/**
 * Project Yohane 関連のコンポーネント（主にDB操作）
 *
 * Class YohaneComponent
 * @package App\Controller\Component
 */
class YohaneComponent extends Component
{
  public $components = ["Line", "Lottery"];

   /** @var string  */
    protected $USERS    = 'YohaneUsers';

    protected $FORTUNES = 'YohaneFortunes';

    /** @var string  */
    protected $KINDS    = 'YohaneKinds';

    /** @var string  */
    protected $WORDS    = 'YohaneWords';

    /** @var string  */
    protected $MAPS     = 'YohaneMaps';

    /** @var string  */
    protected $WEATHERS = 'YohaneWeathers';

  protected $USER_FORTUNES = 'YohaneUserFortunes';

    public function initialize(array $config) {
      $this->Users    = TableRegistry::get($this->USERS);
      $this->Fortunes = TableRegistry::get($this->FORTUNES);
      $this->Kinds    = TableRegistry::get($this->KINDS);
      $this->Words    = TableRegistry::get($this->WORDS);
      $this->Maps     = TableRegistry::get($this->MAPS);
      $this->Weathers = TableRegistry::get($this->WEATHERS);
      $this->User_Fortunes = TableRegistry::get($this->USER_FORTUNES);
    }

  /**
   * @param $userId
   * @param string $name
   */
    public function setUsers($userId, $name=''){
      $user = $this->Users->newEntity();
      $user->set([
        'user_id' => $userId,
        'name'    => $name,
        'created' => date('Y-m-d H:i:s')
      ]);

      $this->Users->save($user);
    }

    public function getUsers($userId){
      $query=$this->Users->find();
      $query->where(['user_id' => $userId]);
      $query->where(['deleted IS NULL']);
      
      $user = $query->first();
      return $user;
    }

    public function deleteUser($userId){
      $now = date('Y-m-d H:i:s');
      $query=$this->Users->query();

      $query->update()
        ->set(['deleted' => $now])
        ->where(['user_id' => $userId])
        ->where(['deleted IS NULL'])
        ->execute();
    }

  public function getFortunes(){
    $query=$this->Fortunes->find();
    $query->where(['deleted IS NULL']);
    $fotunes = $query->toArray();

    return $fotunes;
  }

    public function getKinds(){
      $query=$this->Kinds->find();
      $query->where(['deleted IS NULL']);
      $kind = $query->toArray();

      return $kind;
    }

    public function getWords($kindId, $priority=0){
      $query=$this->Words->find();
      $query->where(['kind_id' => $kindId]);
      $query->where(['priority' => $priority]);
      $words = $query->toArray();

      return $words;
    }

    public function getMaps(){
      $query=$this->Maps->find();
       $query->where(['deleted IS NULL']);
      $maps = $query->toArray();

      return $maps;
    }

    public function getWeathers($day=null){
      if(empty($day)){
        $day = date('Ymd');
      }
      $query=$this->Weathers->find();
      $query->where(['day' => $day]);
       $query->where(['deleted IS NULL']);
      $query->order(['id' => 'DESC']);

      $weather = $query->first();
      return $weather;
    }

    /**
     * 占い実行
     *
     * @return array
     */
    public function getFortuneMessage($userId){
      $messageData = array();

      // 占いマスター取得
      $fortunes = self::getFortunes();
      if(!empty($fortunes)) {
        $userDataId = 0;
        $user = self::getUsers($userId);
        if(!empty($user)) $userDataId = $user['id'];

        // ユーザーの占い情報取得
        $userFortunes = self::getUserFortunes($userDataId);

        // 占い前セリフ取得
        $wordsMaster = self::getWords(FORTUNE, PRIORITY_BEFORE);
        $word = $this->Lottery->lotteryMaster($wordsMaster);
        if (!empty($word)) {
          $text = $word['word'];
          $messageData = $this->Line->setTextMessage($text, $messageData);
        }

        //null:データなし 0:当日データなし
        if(empty($userFortunes) || $userFortunes['fortunes_id'] == 0) {
          // 占い実行
          $fortune = $this->Lottery->lotteryMaster($fortunes);
          self::setUserFortunes($userDataId, $userFortunes, $fortune['id']);
        }else{
          foreach($fortunes as $row){
            if($userFortunes['fortunes_id'] ==$row['id']){
              $fortune = $row;
              break;
            }
          }
        }
        // 占い画像
        if(isset($fortune['img']) && isset($fortune['preview'])){
          $img = YOHANE_IMG_URL . $fortune['img'];
          $preview = YOHANE_IMG_URL . $fortune['preview'];

          $messageData = $this->Line->setImgMessage($img, $preview, $messageData);
        }

        // 占いタイトル
        $text = $fortune['title'];
        $messageData = $this->Line->setTextMessage($text, $messageData);

        // 占い内容
        $text = $fortune['description'];
        $messageData = $this->Line->setTextMessage($text, $messageData);

        // 占い後セリフ取得
        $wordsMaster = self::getWords(FORTUNE, PRIORITY_AFTER);
        $word = $this->Lottery->lotteryMaster($wordsMaster);
        if (!empty($word)) {
          $text = $word['word'];
          $messageData = $this->Line->setTextMessage($text, $messageData);
        }


      }
      return $messageData;
    }

  /**
   * 天気
   */
    public function getWeathersMessage(){

    }

    /**
     * おすすめスポット
     */
    public function getMapsMessage(){
      $messageData = array();

      $maps = self::getMaps();
      if(!empty($maps)){
        // Map前セリフ取得
        $wordsMaster = self::getWords(MAPS, PRIORITY_DEFAULT);
        $word = $this->Lottery->lotteryMaster($wordsMaster);
        if (!empty($word)) {
          $text = $word['word'];
          $messageData = $this->Line->setTextMessage($text, $messageData);
        }

        $map = $this->Lottery->lotteryMaster($maps);
        // おすすめ紹介
        if(!empty($map)){
          //位置情報設定
          $messageData = $this->Line->setLocationMessage($map, $messageData);

          $text = $map['description'];
          if (!empty($text)) {
            $messageData = $this->Line->setTextMessage($text, $messageData);
          }
        }

      }

      return $messageData;
    }

    /**
     * 会話用 適当に返すだけ
     *
     * @return array
     */
    public function getWordsMessage($kindId=WORDS){
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
     * @param $userId
     * @return mixed
     */
    public function getUserFortunes($userDataId){
      $query=$this->User_Fortunes->find();
      $query->where(['user_id' => $userDataId]);
      $query->where(['deleted IS NULL']);

      $userFortunes = $query->first();
      if(!empty($userFortunes)){
        $today = date('Y-m-d 00:00:00');
        $updated = $userFortunes['updated']->i18nFormat('YYYY/MM/dd HH:mm:ss');
        if($userFortunes['updated'] < $today){
          $userFortunes['fortunes_id'] = 0;
        }
      }

      return $userFortunes;
    }

    public function setUserFortunes($userDataId, $userFortunes, $fortune_id){
      $now = date('Y-m-d H:i:s');
      if(empty($userFortunes)){
        $user = $this->User_Fortunes->newEntity();
        $user->set([
          'user_id' => $userDataId,
          'fortunes_id'    => $fortune_id,
          'created' => $now,
          'updated' => $now
        ]);

        $this->User_Fortunes->save($user);
      }else{
        $query=$this->User_Fortunes->query();

        $query->update()
          ->set(['fortunes_id' => $fortune_id])
          ->set(['updated' => $now])
          ->where(['user_id' => $userDataId])
          ->where(['deleted IS NULL'])
          ->execute();
      }
    }

}