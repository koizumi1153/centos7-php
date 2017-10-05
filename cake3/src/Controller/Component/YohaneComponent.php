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

    public function initialize(array $config) {
      $this->Users    = TableRegistry::get($this->USERS);
      $this->Fortunes = TableRegistry::get($this->FORTUNES);
      $this->Kinds    = TableRegistry::get($this->KINDS);
      $this->Words    = TableRegistry::get($this->WORDS);
      $this->Maps     = TableRegistry::get($this->MAPS);
      $this->Weathers = TableRegistry::get($this->WEATHERS);
    }

  /**
   * @param $userId
   * @param string $name
   */
    public function setUsers($userId, $name=''){
      $user = $this->Users->newEntity();
      $user->set([
        'user_id' => $userId,
        'name'    => $name
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
    public function getFortuneMessage(){
      $messageData = array();

      // 占いマスター取得
      $fortunes = self::getFortunes();
      if(!empty($fortunes)) {

        // 占い前セリフ取得
        $wordsMaster = self::getWords(FORTUNE, PRIORITY_BEFORE);
        $word = $this->Lottery->lotteryMaster($wordsMaster);
        if (!empty($word)) {
          $text = $word['word'];
          $messageData = $this->Line->setTextMessage($text, $messageData);
        }

        // 占い実行
        $fortune = $this->Lottery->lotteryMaster($fortunes);
        // 占い画像
        if(isset($fortune['img']) && isset($fortune['preview'])){
          $messageData = $this->Line->setImgMessage($fortune['img'], $fortune['preview'], $messageData);
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

    public function getWeathersMessage(){

    }

    public function getMapsMessage(){

    }

    public function getWordsMessage(){

    }

}