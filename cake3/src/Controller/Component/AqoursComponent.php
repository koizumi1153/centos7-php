<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

class AqoursComponent extends Component
{
    protected $AQOURS_INFORMATION = 'AqoursInformation';

    public function initialize(array $config) {
      $this->Information = TableRegistry::get($this->AQOURS_INFORMATION);
    }

  /**
   * 特定日時のデータを取得
   * @param $date
   * @return mixed
   */
    public function getiInformationDate($date){
      $query=$this->Information->find()
        ->where(['date' => $date])
        ->where(['deleted IS NULL']);
      return $query->toArray();
    }

  /**
   * @param int $page
   */
    public function getInformation($page=1){
      $order = $page - 1;
      $query=$this->Information->find()
        ->where(['deleted IS NULL'])
        ->order(['date' => 'ASC'])
        ->limit(100)
        ->offset($order);

      return $query->toArray();
    }

  /**
   * 特定kindのデータを取得
   * @param $dbKind
   * @return mixed
   */
    public function getDataFromKind($dbKind){
      $query=$this->Information->find();
      $query->where(['kind' => $dbKind]);
      $query->where(['deleted IS NULL']);
      $query->order(['date' => 'ASC']);

      return $query->toArray();
    }

  /**
   * データを取得する
   * @param $items
   * @param $kind
   */
    public function setRakutenEvent($items, $kind){
      $dbKind = $this->changeRakutenKind($kind);
      $lists = $this->checkData($dbKind,$items);
      $query = $this->Information->query();
      $query->insert([
        'kind',
        'title',
        'discription',
        'jan',
        'img',
        'date',
      ]);
      if(!empty($lists)){
        foreach($lists as $item){
          $data = $this->generateData($kind, $item);
          $query->values($data);
        }
      }
    }

  /**
   * 楽天kindからdb保存用kind取得
   * @param $kind
   * @return int
   */
    public function changeRakutenKind($kind){
      $dbKind = 0;
      switch ($kind){
        case BOKK_BASE:
          $dbKind = AQOURS_KIND_BOOK;
          break;
        case CD_BASE:
          $dbKind = AQOURS_KIND_CD;
          break;
        case DVD_BASE:
          $dbKind = AQOURS_KIND_DVD;
          break;
      }

      return $dbKind;
    }

  /**
   * 登録済みのデータを削除する
   * @param $dbKind
   * @param $items
   * @return mixed
   */
    public function checkData($dbKind,$items){
      $data = $this->getDataFromKind($dbKind);
      $jans = array_column($data, 'jan', 'id');
      $titles = array_column($data, 'title', 'id');

      foreach($items as $key => $list) {
        $title = $list['title'];
        $jan   = $list['jan'];
        if (!empty($jan)) {
          if(in_array($jan, $jans)) unset($items[$key]);
        }elseif(!empty($title)){
          if(in_array($title, $titles)) unset($items[$key]);
        }
      }

      return $items;
    }

    public function generateData($kind, $item){
      $data = [];
      $data['kind'] = $kind;
      $data['title'] = $item['title'];
      $data['discription'] = "";
      $data['jan'] = "";
      $data['img'] = "";
      $data['date'] = "";

      // 説明
      if(isset($item['itemCaption'])){
        $data['discription'] = $item['itemCaption'];
      }

      // jan
      if(isset($item['jan'])){
        $data['jan'] = $item['jan'];
      }

      // img
      if(isset($item['largeImageUrl']) && isset($item['jan'])){
        $imgKind = $this->checkImg($item['largeImageUrl']);
        if($imgKind !== false){
          $this->setImg($item['largeImageUrl'], $item['jan'], $imgKind);
          $data['img'] = $item['jan'].$imgKind;
        }
      }
      // date
      if(isset($item['salesDate'])){
        $data['date'] = $item['salesDate'];
      }

      return $data;
    }

  /**
   * @param $img
   * @return bool|string
   */
    public function checkImg($img){
      $jpg = "jpg";
      if(strpos($img,$jpg) !== false){
        return '.jpg';
      }

      $jpeg = "jpeg";
      if(strpos($img,$jpeg) !== false){
        return '.jpeg';
      }

      $png = "png";
      if(strpos($img,$png) !== false){
        return '.png';
      }

      $gif = "gif";
      if(strpos($img,$gif) !== false){
        return '.gif';
      }

      return false;
    }

  /**
   * 画像保存
   * @param $img
   * @param $imgKind
   */
    public function setImg($img, $name, $imgKind){
      $imgName = $name. $imgKind;
      exec("wget $img -P AQOURS_IMG_DIR -O $imgName");
    }
}