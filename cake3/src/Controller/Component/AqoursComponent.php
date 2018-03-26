<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class AqoursComponent extends Component
{
    protected $AQOURS_INFORMATION = 'AqoursInformation';
    protected $AQOURS_BLOG = 'AqoursBlog';
    protected $AQOURS_BIRTHDAY = 'AqoursBirthday';
    protected $AQOURS_NEWS = 'AqoursNews';
    protected $AQOURS_CLUB2017 = 'AqoursClub2017';


  public function initialize(array $config) {
      $this->Information = TableRegistry::get($this->AQOURS_INFORMATION);
      $this->Blog = TableRegistry::get($this->AQOURS_BLOG);
      $this->Birthday = TableRegistry::get($this->AQOURS_BIRTHDAY);
      $this->News = TableRegistry::get($this->AQOURS_NEWS);
      $this->Club2017 = TableRegistry::get($this->AQOURS_CLUB2017);
  }

  /**
   * 特定日時のデータを取得
   * @param $date
   * @return mixed
   */
    public function getInformationDate($date){
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
    public function getDataFromKind($dbKind, $isDeletedFlg=true){
      $query=$this->Information->find();
      $query->where(['kind' => $dbKind]);
      $query->order(['date' => 'ASC']);

      if($isDeletedFlg){
        $query->where(['deleted IS NULL']);
      }

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
        'push',
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
        case MAGAZINE_BASE:
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
      $data = $this->getDataFromKind($dbKind, false);
      $jans = array_column($data, 'jan', 'id');
      $titles = array_column($data, 'title', 'id');

      $exclusion = AQOURS_EXCLUSION_WORDS;

      $lists = [];
      foreach($items as $key => $val) {
        $list = $val['Item'];
        if(!isset($list['title'])){
          continue;
        }

        // 本

        // CD アーティスト名不一致は除去
        if($dbKind == AQOURS_KIND_CD && strpos($list['artistName'],$keyword) === false) {
          continue;
        }

        // 除外文字対応
        if($dbKind == AQOURS_KIND_DVD && !empty($exclusion)){
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
          if(in_array($jan, $jans)){

            // 画像更新があればかける 先の日付だけ
            if(isset($list['largeImageUrl']) && $this->dateCheck($list['salesDate'])){
              $imgKind = $this->checkImg($list['largeImageUrl']);
              if($imgKind !== false){
                $this->setImg($list['largeImageUrl'], $jan, $imgKind);
              }
            }
            continue;
          }
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
      $data['push'] = PUSH_NONE;
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
      if(isset($item['largeImageUrl']) && isset($item['isbn'])){
        $imgKind = $this->checkImg($item['largeImageUrl']);
        if($imgKind !== false){
          $this->setImg($item['largeImageUrl'], $item['isbn'], $imgKind);
          $data['img'] = $item['isbn'].$imgKind;
        }
      }
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

      $sell = array(AQOURS_KIND_BOOK, AQOURS_KIND_CD, AQOURS_KIND_DVD);
      if(in_array($dbkind, $sell)){
        $data['push'] = PUSH_READY;
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

  /**
   * ブログチェック
   * @return array
   */
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
            $item = (array)$item;
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

  /**
   * ブログ情報保存
   *
   * @param $blogData
   * @param $creator
   */
    public function setBlog($blogData, $creator){
      $query = $this->Blog->query();
      $query->insert([
        'link',
        'title',
        'description',
        'date',
        'creator',
        'created'
      ]);
      if(!empty($blogData)){
        foreach($blogData as $item){
          $data['link']  = $item['link'];
          $data['title'] = $item['title'];
          $data['description'] = $item['description'];
          $data['date'] = '';
          if(isset($item['date'])) {
            $data['date'] = $item['date'];
          }elseif(isset($item['pubDate'])){
            $data['date'] = date("Y-m-d H:i:s", strtotime($item['pubDate']));
          }
          $data['creator'] = $creator;
          $data['created'] = date('Y-m-d H:i:s');
          $query->values($data);
        }

        $query->execute();
      }
    }

  /**
   * ブログ情報取得
   * @return mixed
   */
    public function getBlog(){
      $query = $this->Blog->find()
          ->where(['deleted IS NULL']);
      return $query->hydrate(false)->toArray();
    }

  /**
   * 週間情報取得
   *
   * @return mixed
   */
    public function getInformationWeek($days){
      $query=$this->Information->find()
        ->where(['date IN' => $days])
        ->where(['deleted IS NULL'])
        ->order(['date' => 'ASC']);
      return $query->hydrate(false)->toArray();
    }

  /**
   * @param int $push
   * @return mixed
   */
    public function getInformationPush($push=PUSH_READY){
      $query=$this->Information->find()
        ->where(['push' => $push]);
      return $query->hydrate(false)->toArray();
    }

  /**
   * push を送信済みに更新
   */
    public function updatePush(){
      $query = $this->Information->query();
      $query->update()
            ->set(['push' => PUSH_FINISH])
            ->where(['push' => PUSH_READY])
            ->execute();
    }

  /**
   * 特定日時のデータを取得
   * @param $date
   * @return mixed
   */
  public function getBirthday($date){
    $query=$this->Birthday->find()
      ->where(['day' => $date])
      ->where(['deleted IS NULL']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * データの存在チェック
   * @return bool
   */
  public function checkNewsInit(){
    $flg = true;
    $query=$this->News->find();
    $news = $query->hydrate(false)->toArray();

    if(empty($news)) $flg = false;
    return $flg;
  }

  /**
   * 特定のデータを取得
   * @param $ids
   * @return mixed
   */
  public function getNewsFromIds($ids){
    $query=$this->News->find()
      ->where(['id IN' => $ids])
      ->where(['deleted IS NULL']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * ニュースデータを取得
   * limit制限
   *
   * @param $offset
   * @param $limit
   * @return mixed
   */
  public function getNewsLimit($offset=0, $limit=10){
    $query=$this->News->find()
      ->where(['deleted IS NULL'])
      ->limit($limit)
      ->offset($offset)
      ->order(['id' => 'DESC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * 特定のカテゴリデータを取得
   * limit制限
   *
   * @param $category
   * @param int $offset
   * @param int $limit
   * @return mixed
   */
  public function getNewsFromCategory($category, $offset=0, $limit=10){
        $query=$this->News->find()
          ->where(['category' => $category])
          ->where(['deleted IS NULL'])
          ->limit($limit)
          ->offset($offset)
          ->order(['id' => 'DESC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * bulkinsert
   *
   * @param $contents
   */
  public function setNews($contents){
    $query = $this->News->query();
    $query->insert([
      'id',
      'category',
      'title',
      'html_body',
      'body',
      'publish_date',
      'created'
    ]);
    if(!empty($contents)){
      foreach($contents as $news){
        $news['created'] = date('Y-m-d H:i:s');
        $query->values($news);
      }
      $query->execute();
    }
  }

  /**
   * @param int $offset
   * @param int $limit
   * @return mixed
   */
  public function getClubNews2017($offset=0, $limit=10){
    $query=$this->Club2017->find()
      ->where(['deleted IS NULL'])
      ->limit($limit)
      ->offset($offset)
      ->order(['id' => 'DESC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * @param $contents
   */
  public function setClubNews2017($contents)
  {
    $query = $this->Club2017->query();
    $query->insert([
      'id',
      'publish_date',
      'title',
      'created'
    ]);
    if (!empty($contents)) {
      foreach ($contents as $news) {
        $news['created'] = date('Y-m-d H:i:s');
        $query->values($news);
      }
      $query->execute();
    }
  }

  /*
   * @param $saleDate
   */
  public function dateCheck($saleDate){
    $now = date('Y-m-d');
    $format = 'Y年m月d日';
    $date = \DateTime::createFromFormat($format, $saleDate);
    $saleDateFormat = $date->format('Y-m-d');
    if($now < $saleDateFormat) return true;

    return false;
  }

  /**
   * @param $date
   * @param $title
   * @param array $clubNews
   * @return bool
   */
  public function checkNews($date, $title, $clubNews= array()){
    $flg=false;
    foreach($clubNews as $data){
     if($data['publish_date'] == $date && $data['title'] == $title){
       $flg=true;
       break;
     }
    }

    return $flg;
  }
}