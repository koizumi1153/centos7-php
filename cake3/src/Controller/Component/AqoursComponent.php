<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class AqoursComponent extends Component
{
    protected $AQOURS_INFORMATION = 'AqoursInformation';
    protected $AQOURS_BLOG = 'AqoursBlog';

    public function initialize(array $config) {
      $this->Information = TableRegistry::get($this->AQOURS_INFORMATION);
      $this->Blog = TableRegistry::get($this->AQOURS_BLOG);
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
      return $query->hydrate(false)->toArray();
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

      return $query->hydrate(false)->toArray();
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

      return $query->hydrate(false)->toArray();
    }

  /**
   * データを取得する
   * @param $items
   * @param $kind
   */
    public function setRakutenEvent($items, $kind, $keyword){
      $dbKind = $this->changeRakutenKind($kind);
      $lists = $this->checkData($dbKind,$items, $keyword);

      $query = $this->Information->query();
      $query->insert([
        'kind',
        'title',
        'discription',
        'price',
        'jan',
        'img',
        'date',
        'created'
      ]);
      if(!empty($lists)){
        foreach($lists as $item){
          if(!isset($item['title'])) continue;
          $data = $this->generateData($dbKind, $item);
          $query->values($data);
        }

        $query->execute();
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
    public function checkData($dbKind,$items, $keyword){
      $data = $this->getDataFromKind($dbKind);
      $jans = array_column($data, 'jan', 'id');
      $titles = array_column($data, 'title', 'id');

      $exclusion = AQOURS_EXCLUSION_WORDS;

      $lists = [];
      foreach($items as $key => $val) {
        $list = $val['Item'];
        if(!isset($list['title'])){
          continue;
        }

        // CD アーティスト名不一致は除去
        if($dbKind == AQOURS_KIND_CD && strpos($list['artistName'],$keyword) === false) {
          continue;
        }

        // 除外文字対応
        if(!empty($exclusion)){
          $continueFlg = false;
          foreach($exclusion as $word){
            if(strpos($list['title'],$word) !== false){
              $continueFlg = true;
            }
          }

          if($continueFlg) continue;
        }

        $title = $list['title'];
        $jan = null;
        if(isset($list['jan'])) $jan = $list['jan'];
        if (!empty($jan)) {
          if(in_array($jan, $jans)) continue;
        }elseif(!empty($title)){
          if(in_array($title, $titles)) continue;
        }

        $titles[] = $title;
        if(!empty($jan)) {
          $jans[] = $jan;
        }

        $lists[] = $list;
      }

      return $lists;
    }

  /**
   * @param $kind
   * @param $item
   * @return array
   */
    public function generateData($dbkind, $item){
      $data = [];
      $data['kind'] = $dbkind;
      $data['title'] = $item['title'];
      $data['discription'] = "";
      $data['price'] = 0;
      $data['jan'] = "";
      $data['img'] = "";
      $data['date'] = "";
      $data['created'] = date('Y-m-d H:i:s');

      // 説明
      if(isset($item['itemCaption'])){
        $data['discription'] = $item['itemCaption'];
      }

      // price
      if(isset($item['itemPrice'])){
        $data['price'] = $item['itemPrice'];
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
      exec("wget -O ".AQOURS_IMG_DIR.$imgName." $img");
    }

  /**
   * @param $dbKind
   * @return string
   */
    public function getTitle($dbKind){
      $title = "情報";
      switch($dbKind){
        case AQOURS_KIND_BOOK:
          $title = "BOOK";
          break;
        case AQOURS_KIND_CD:
          $title = "CD";
          break;
        case AQOURS_KIND_DVD:
          $title = "DVD";
          break;

        default:
          break;
      }


      return $title;
    }

    public function checkBlog(){
      $return = [];
      $linkAll = [];
      $blogs = $this->getBlog();
      if(!empty($blogs)){
        $linkAll = array_column($blogs, 'link');
      }

      $rssUrl = AQOURS_BLOG_RSS_URLS;
      $name = AQOURS_BLOG_NAMES;
      foreach($rssUrl as $key => $url){
        $blogData = array();
        $creator = $name[$key];
        $rss = simplexml_load_file($url);
        if(strpos($url, 'lineblog') !== false){
          // line
          foreach($rss as $item){
            if(isset($item->items)) continue;
            $item = (array)$item;
            $link  = $item['link'];
            if(!in_array($link,$linkAll)){
              $blogData[] = $item;
              if(!isset($return[$key])) $return[$key] = $item;
            }
          }
        }elseif(strpos($url, '.xml') !== false){
          // xml
          foreach($rss->channel->item as $item){
            $item = (array)$item
            $link  = $item['link'];
            if(!in_array($link,$linkAll)){
              $blogData[] = $item;
              if(!isset($return[$key])) $return[$key] = $item;
            }
          }
        }

        if(!empty($blogData)){
          $this->setBlog($blogData, $creator);
        }
      }

      return $return;
    }

    public function setBlog($blogData, $creator){
      $query = $this->Blog->query();
      $query->insert([
        'link',
        'title',
        'discription',
        'date',
        'creator',
        'created'
      ]);
      if(!empty($blogData)){
        foreach($blogData as $item){
          $data['link']  = $item['link'];
          $data['title'] = $item['title'];
          $data['description'] = $item['description'];
          $data['date'] = $item['date'];
          $data['creator'] = $creator;
          $data['created'] = date('Y-m-d H:i:s');
          $query->values($data);
        }

        $query->execute();
      }
    }

    public function getBlog(){
      $query = $this->Blog->find()
          ->where(['deleted IS NULL']);
      return $query->hydrate(false)->toArray();
    }
}