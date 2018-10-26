<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\AqoursComponent;
use App\Controller\Component\YouComponent;

class AqoursPushUserInitShell extends Shell
{

  public function initialize() {
    // component
    $this->Aqours  = new AqoursComponent(new ComponentRegistry());
    $this->You = new YouComponent(new ComponentRegistry());
  }

  /**
   * 現在登録済みユーザーの
   * aqours_user_push_kind
   * aqours_user_push_member
   * のフラグを1(ON)にしたデータを作成する
   *
   * @return bool|int|null|void
   */
  public function main()
  {
    $max = 10;
    for($page=1;$page<=$max;$page++) {
      $users = $this->You->getAllUsers($page);
      if(!empty($users)){
        $userIds = array_column($users, 'id');
        foreach($userIds as $usersId){
          // kind
          $this->Aqours->initPushKind($usersId);

          // member
          $this->Aqours->initPushMember($usersId);
        }
      }else{
         break;
      }
    }
  }

    /**
     * @param int $kind
     */
  public function add($kind = 105){
      $max = 10;
      for($page=1;$page<=$max;$page++) {
          $users = $this->You->getAllUsers($page);
          if(!empty($users)){
              $userIds = array_column($users, 'id');
              foreach($userIds as $usersId){
                  $this->Aqours->setPushKind($usersId, $kind);
              }
          }else {
              break;
          }
      }
  }

}