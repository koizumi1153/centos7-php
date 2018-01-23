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
  public $components = ["Line", "Lottery"];

   /** @var string  */
    protected $USERS    = 'YouUsers';

    /** @var string  */
    protected $KINDS    = 'YouKinds';

    /** @var string  */
    protected $WORDS    = 'YouWords';

    public function initialize(array $config) {
      $this->Users    = TableRegistry::get($this->USERS);
      $this->Kinds    = TableRegistry::get($this->KINDS);
      $this->Words    = TableRegistry::get($this->WORDS);
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

    public function getKinds(){
      $query=$this->Kinds->find();
      $query->where(['deleted IS NULL']);
      $kind = $query->hydrate(false)->toArray();

      return $kind;
    }

    public function getWords($kindId, $priority=0){
      $query=$this->Words->find();
      $query->where(['kind_id' => $kindId]);
      $query->where(['priority' => $priority]);
      $words = $query->hydrate(false)->toArray();

      return $words;
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
   * 天気
   */
    public function getWeathersMessage(){

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
   * PUSH 可能ユーザー数取得
   * @return mixed
   */
    public function getPushUsersCount(){
      $query=$this->Users->find();
      $query->where(['push_flg' => ON_FLG]);
      $query->where(['deleted IS NULL']);

      $total = $query->count();
      return $total;
    }

  /**
   * PUSH可能ユーザー取得
   * @param $page
   * @return mixed
   */
    public function getPushUsers($page){
      $query=$this->Users->find()->select(['user_id']);
      $query->where(['push_flg' => ON_FLG]);
      $query->where(['deleted IS NULL']);
      $query->order(['id' => 'ASC']);
      $query->limit(LINE_MULTI_USER)->page($page);

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
    public function setLocation($userId, $latitude, $longitude){
      $now = date('Y-m-d H:i:s');
      $query=$this->Users->query();

      $query->update()
        ->set(['latitude' => $latitude])
        ->set(['longitude' => $longitude])
        ->where(['user_id' => $userId])
        ->where(['deleted IS NULL'])
        ->execute();
    }

    public function setPushMessage($data){
      $messageData = [];
      foreach($data as $key => $row){
        $text = '';
        $text .= $row['title'] ."";
        $text = <<<EOT
{$row['title']}が{$row['date']}に発売だよ。
定価は{$row['price']}円。
{$row['discription']}
EOT;
        $messageData[] = $this->Line->setTextMessage($text, $messageData);
        if(!empty($row['img'])){
          $image = AQOURS_IMG_URL.$row['img'];
          $preview = AQOURS_IMG_URL.$row['img'];
          $messageData[] = $this->Line->setImgMessage($image, $preview, $messageData);
        }
      }

      return $messageData;
    }

}