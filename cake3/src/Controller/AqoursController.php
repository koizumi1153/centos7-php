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
    $shopId = 0;
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
        $shopId = $master['id'];
      }
    }

    // 設定済の情報取得
    $lists = $this->Aqours->getUserLiveNumber($user['user_id']);
    $this->set('lists', $lists);
    $this->set('userHash', $userHash);
    $this->set('shopId', $shopId);
    $this->set('title', '整理番号 確認・変更');
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
      $shopId  = $post['shopId'];
      // 番号保存
      $contents = $this->Aqours->settingUserLiveNumber($user['user_id'], $numbers, $shopId);
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


  /**
   * master管理用
   */
  public function master(){
    $masters = $this->Aqours->getLiveShopAll();
    $this->set('masters', $masters);
    $this->set('title', '設定 管理画面');
  }

  /**
   * 追加
   * @return \Cake\Http\Response|null
   */
  public function masterAdd(){
    $post = $this->request->getData();
    if(!empty($post)){

      // 保存
      if(!empty($post)) $this->Aqours->setLiveShop($post);
    }

    // masterへ戻す
    return $this->redirect(['action' => 'master']);
  }

  /**
   * 変更
   *
   * @return \Cake\Http\Response|null
   */
  public function masterUpdate($id=0){
    $post = $this->request->getData();
    if(!empty($post)){

      $screenName = $post['screen_name'];
      // 保存
      if(!empty($post)) $this->Aqours->updateLiveShop($id,$screenName);
    }
    // masterへ戻す
    return $this->redirect(['action' => 'master']);
  }

  /**
   * 削除
   *
   * @return \Cake\Http\Response|null
   */
  public function masterDelete($id=0){
    $post = $this->request->getData();
    if(!empty($id)){
      // 保存
      if(!empty($post)) $this->Aqours->deleteLiveShop($id);
    }
    // masterへ戻す
    return $this->redirect(['action' => 'master']);
  }
}