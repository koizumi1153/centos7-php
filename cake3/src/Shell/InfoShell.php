<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;

class InfoShell extends Shell
{

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
  }

  public function main()
  {
      echo "start\n";
      $this->loadModel('AqoursInformation');
      // 100件づつ取得
      for($i=0;$i<5;$i++){
          $updateList = [];
          $limit = 100;
          $page = ($limit - 1) * $i;
          $query = $this->AqoursInformation->find()
                        ->where(['deleted IS NULL'])
                        ->order(['id' => 'ASC'])
                        ->limit($limit)
                        ->offset($page);
          $list = $query->hydrate(false)->toArray();
          if(empty($list)) break;
          foreach($list as $info){
              if(empty($info['push_date']) && mb_strlen($info['date'] == 11)){
                  $id = $info['id'];
                  $pushDate = mb_substr($info['date'], 0, 4) . '-'. mb_substr($info['date'], 5, 2). '-'. mb_substr($info['date'], 8, 2);

                  //更新されるべき内容
                  $updateQuery = $this->AqoursInformation->query();
                  $updateQuery->update()
                              ->set(['push_date' => $pushDate])
                              ->where(['id' => $id])
                              ->execute();
              }
          }
          echo "sleep\n";
        sleep(1);
      }
      echo "end\n";
  }
}