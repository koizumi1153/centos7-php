<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Network\Exception\NotFoundException;

/**
 * Project Aqours Controller
 *
 * Class AqoursController
 * @package App\Controller
 */
class AqoursController extends AppController
{
  public $components = ["Aqours","You" ];

  /**
   * @param string $userHash
   */
  public function setting($userHash='')
  {
    //userHashがない
    if(empty($userHash)) {
      throw new NotFoundException(__('Hash not found'));
    }else{
      $user = $this->You->getUserHash($userHash);
      if(empty($user)){
        // 存在しない場合は404エラー
        throw new NotFoundException(__('User not found'));
      }elseif(env('CAKEPHP_ENV') == "production"){
        // 本番は当日のマスターを取得する
        $master = $this->Aqours->getLiveShop();
        if(empty($master)){
          // 存在しない場合は404エラー
          throw new NotFoundException(__('Master not found'));
        }
      }
    }

    // 設定済の情報取得
    $lists = $this->Aqours->getUserLiveNumber($user['user_id']);
    $this->set('lists', $lists);
    $this->set('userHash', $userHash);
  }

  /**
   * @param string $userHash
   */
  public function add($userHash=''){
    //userHashがない
    if(empty($userHash)) {
      throw new NotFoundException(__('Hash not found'));
    }else{
      $user = $this->You->getUserHash($userHash);
      if(empty($user)){
        // 存在しない場合は404エラー
        throw new NotFoundException(__('User not found'));
      }elseif(env('CAKEPHP_ENV') == "production"){
        // 本番は当日のマスターを取得する
        $master = $this->Aqours->getLiveShop();
        if(empty($master)){
          // 存在しない場合は404エラー
          throw new NotFoundException(__('Master not found'));
        }
      }
    }

    $post = $this->request->getData();
    if(!empty($post)){
      $numbers = $post['numbers'];
      // 番号保存
      $contents = $this->Aqours->settingUserLiveNumber($user['user_id'], $numbers);
      // 保存
      if(!empty($contents)) $this->Aqours->setUserLiveNumber($contents);
    }

    // indexへ戻す
    return $this->redirect(['action' => 'setting/'.$userHash]);
  }

  /**
   * 削除
   *
   * @param string $userHash
   * @param int $id
   * @return \Cake\Http\Response|null
   */
  public function delete($userHash='', $id=0){
    //userHashがない
    if(empty($userHash)) {
      throw new NotFoundException(__('Hash not found'));
    }else{
      $user = $this->You->getUserHash($userHash);
      if(empty($user)){
        // 存在しない場合は404エラー
        throw new NotFoundException(__('User not found'));
      }elseif(env('CAKEPHP_ENV') == "production"){
        // 本番は当日のマスターを取得する
        $master = $this->Aqours->getLiveShop();
        if(empty($master)){
          // 存在しない場合は404エラー
          throw new NotFoundException(__('Master not found'));
        }
      }
    }

    if(!empty($id)){
      $this->Aqours->deleteUserLiveNumber($id);
    }

    // indexへ戻す
    return $this->redirect(['action' => 'setting/'.$userHash]);

  }
}