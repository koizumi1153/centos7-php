<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class PresentComponent extends Component
{
  public function initialize(array $config)
  {
    $this->Present = TableRegistry::get('TwitterPresent');
  }

  /**
   * @param $baseId
   * @param $date
   * @return mixed
   */
  public function getDate($date){
    $query = $this->Present->find();
    $query->where(['start_date <= ' => $date]);
    $query->where(['end_date > ' => $date]);
    $query->where(['deleted IS NULL']);

    $date = $query->hydrate(false)->toArray();
    return $date;
  }

  /**
   * @param $id
   * @param array $ids
   */
  public function update($id, $ids=array()){
    $now = date('Y-m-d H:i:s');
    $query=$this->Present->query();

    $query->update()
      ->set(['updated' => $now])
      ->set(['retweet_flg' => ON_FLG])
      ->set(['retweet_ids' => implode(',', $ids)])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

   /**
    * @param $row
    */
  public function set($row){
    $user = $this->Present->newEntity();
    $user->set([
        'screen_name' => $row['screen_name'],
        'word'        => $row['word'],
        'start_date'  => $row['start_date'],
        'end_date'    => $row['end_date'],
        'kind'        => $row['kind'],
        'created'     => date('Y-m-d H:i:s')
    ]);

    $this->Present->save($user);
  }

}