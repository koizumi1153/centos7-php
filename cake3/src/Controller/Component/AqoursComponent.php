<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class AqoursComponent extends Component
{
    protected $AQOURS_INFORMATION = 'AqoursInformation';
    protected $AQOURS_BLOG        = 'AqoursBlog';
    protected $AQOURS_CHECK_BLOG  = 'AqoursCheckBlog';
    protected $AQOURS_BIRTHDAY    = 'AqoursBirthday';
    protected $AQOURS_NEWS        = 'AqoursNews';
    protected $AQOURS_CLUB        = 'AqoursClub2018';
    protected $AQOURS_RADIO       = 'AqoursRadio';
    protected $AQOURS_MEDIA       = 'AqoursMedia';
    protected $AQOURS_LANTIS      = 'AqoursLantis';
    protected $AQOURS_NICO        = 'AqoursNico';

    //scraping
    protected $AQOURS_SCRAPING      = "AqoursScraping";
    protected $AQOURS_SCRAPING_DATA = "AqoursScrapingData";

    // push関連
    protected $PUSH_KIND    = "AqoursUserPushKind";
    protected $PUSH_MEMBER  = "AqoursUserPushMember";

    /**
     * @param array $config
     */
    public function initialize(array $config) {
      $this->Information    = TableRegistry::get($this->AQOURS_INFORMATION);
      $this->Blog           = TableRegistry::get($this->AQOURS_BLOG);
      $this->CheckBlog      = TableRegistry::get($this->AQOURS_CHECK_BLOG);
      $this->Birthday       = TableRegistry::get($this->AQOURS_BIRTHDAY);
      $this->News           = TableRegistry::get($this->AQOURS_NEWS);
      $this->Club           = TableRegistry::get($this->AQOURS_CLUB);

      $this->Media          = TableRegistry::get($this->AQOURS_MEDIA);
      $this->Radio          = TableRegistry::get($this->AQOURS_RADIO);

      $this->LiveShop       = TableRegistry::get("AqoursLiveShop");
      $this->UserLiveNumber = TableRegistry::get("AqoursUserLiveNumber");
      $this->LiveShopNumber = TableRegistry::get("AqoursLiveShopNumber");
      $this->Lantis         = TableRegistry::get($this->AQOURS_LANTIS);
      $this->Nico           = TableRegistry::get($this->AQOURS_NICO);

      $this->Scraping       = TableRegistry::get($this->AQOURS_SCRAPING);
      $this->ScrapingData   = TableRegistry::get($this->AQOURS_SCRAPING_DATA);

      $this->PushKind       = TableRegistry::get($this->PUSH_KIND);
      $this->PushMember     = TableRegistry::get($this->PUSH_MEMBER);
    }

  /**
   * 特定日時のデータを取得
   * @param $date
   * @return mixed
   */
    public function getInformationDate($date){
      $query=$this->Information->find()
        ->where(['date' => $date])
        ->where(['deleted IS NULL'])
        ->order(['kind' => 'ASC']);
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
   * bulkinsert
   * @param $lists
   */
    public function setInfo($lists){
      $list['push_date'] = '';
      $query = $this->Information->query();
      $query->insert([
        'kind',
        'title',
        'discription',
        'price',
        'jan',
        'img',
        'date',
        'push_date',
        'push',
        'member_ids',
        'created'
      ]);
      if(!empty($lists)){
        foreach($lists as $data){
          // push_date追加
          if(isset($data['date'])){
            $data['push_date'] =mb_substr($data['date'], 0, 4) . '-'. mb_substr($data['date'], 5, 2). '-'. mb_substr($data['date'], 8, 2);
          }

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
    public function checkBlog($master){
      $return = [];
      $linkAll = [];
      $blogs = $this->getBlog();
      if(!empty($blogs)){
        $linkAll = array_column($blogs, 'link');
      }

      $url = $master['url'];
      $blogData = array();
      $creator = $master['title'];
      $rss = simplexml_load_file($url);
      if(strpos($url, 'lineblog') !== false){
        // line
        foreach($rss as $item){
          if(isset($item->items)) continue;
          $item = (array)$item;
          $link  = $item['link'];
          if(!in_array($link,$linkAll)){
            $blogData[] = $item;
            $item['creator'] = $creator;
            $return[] = $item;
          }
        }
      }elseif(strpos($url, '.xml') !== false){
        // xml
        foreach($rss->channel->item as $item){
          $item = (array)$item;
          $link  = $item['link'];
          if(!in_array($link,$linkAll)){
            $blogData[] = $item;
            $item['creator'] = $creator;
            $return[] = $item;
          }
        }
      }

      if(!empty($blogData)){
        $this->setBlog($blogData, $creator);
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
                          ->where(['deleted IS NULL'])
                          ->limit(100);
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
   * ニュースデータを取得
   * limit制限
   *
   * @return mixed
   */
  public function getNewsAll(){
    $query=$this->News->find()
      ->where(['deleted IS NULL'])
      ->order(['id' => 'ASC']);
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
  public function getClubNews($offset=0, $limit=10){
    $query=$this->Club->find()
      ->where(['deleted IS NULL'])
      ->limit($limit)
      ->offset($offset)
      ->order(['id' => 'DESC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * @param $contents
   */
  public function setClubNews($contents)
  {
    $query = $this->Club->query();
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

  /**
   * @param $data
   */
  public function updateNews($data){
    $now = date('Y-m-d H:i:s');
    $query=$this->News->query();

    $query->update()
      ->set(['updated' => $now])
      ->set(['title' => $data['title']])
      ->set(['html_body' => $data['html_body']])
      ->set(['body' => $data['body']])
      ->set(['publish_date' => $data['publish_date']])
      ->where(['id' => $data['id']])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * タイトルで取得
   *
   * @return mixed
   */
  public function getMediaFromTitle($title){
    $query=$this->Media->find()
      ->where(['title' => $title])
      ->where(['deleted IS NULL']);
    return $query->first()->toArray();
  }

  /**
   * 番号更新
   *
   * @param $id
   * @param $number
   */
  public function updateMedia($id, $number){
    $now = date('Y-m-d H:i:s');
    $query=$this->Media->query();

    $query->update()
      ->set(['updated' => $now])
      ->set(['number' => $number])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 月の何周目かを取得する関数
   *
   * @param $date
   * @return float
   */
  public function getWeek($date){
    $time = strtotime($date);
    $week = intval(date('d',$time));
    $week_day = 7;
/*
    $saturday = 6;
    $week_day = 7;
    $w = intval(date('w',$time));
    $d = intval(date('d',$time));
    if ($w!=$week) {
      $w = ($week - $w) + $d;
    } else { // 土曜日の場合を修正
      $w = $d;
    }
    */
    return ceil($week/$week_day);
  }

  /**
   * その月の第一月曜日を取得する
   *
   * @param $yyyymm
   * @return false|string
   */
  public function getFirstMonday($yyyymm){
    $date = date('Ymd', strtotime($yyyymm . '01 this week'));
    if(substr($date, 0, 6) !== $yyyymm){
      $date = date('Ymd', strtotime($date . ' 1 week'));
    }
    return $date;
  }

  /**
   * 全取得
   * @return mixed
   */
  public function getRadio(){
    $query=$this->Radio->find()
      ->where(['deleted IS NULL']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * 保存
   * @param $contents
   */
  public function setLiveShop($contents){
    $query = $this->LiveShop->query();
    $query->insert([
      'id',
      'title',
      'date',
      'start_date',
      'end_date',
      'screen_name',
      'created'
    ]);
    //設定
    $contents['created'] = date('Y-m-d H:i:s');
    $query->values($contents);
    $query->execute();
  }

  /**
   * @param $id
   * @param $screenName
   */
  public function updateLiveShop($id, $screenName){
    $now = date('Y-m-d H:i:s');
    $query = $this->LiveShop->query();

    $query->update()
      ->set(['screen_name' => $screenName])
      ->set(['updated' => $now])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 削除
   * @param $id
   */
  public function deleteLiveShop($id){
    $now = date('Y-m-d H:i:s');
    $query = $this->LiveShop->query();

    $query->update()
      ->set(['deleted' => $now])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 取得当日のマスターを取得する
   * @return mixed
   */
  public function getLiveShop(){
    $date = date('Y-m-d');
    $query=$this->LiveShop->find()
      ->where(['date' => $date])
      ->where(['deleted IS NULL'])
      ->order(['date' => 'ASC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * 全てのマスターを取得する
   * @return mixed
   */
  public function getLiveShopAll(){
    $date = date('Y-m-d');
    $query=$this->LiveShop->find()
      ->where(['deleted IS NULL'])
      ->order(['date' => 'ASC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * ユーザーの当日の情報を取得する
   *
   * @param $userId
   * @return mixed
   */
  public function getUserLiveNumber($userId){
    $date = date('Y-m-d');
    $query=$this->UserLiveNumber->find()
      ->where(['user_id' => $userId])
      ->where(['date' => $date])
      ->where(['deleted IS NULL'])
      ->order(['date' => 'ASC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * フォームデータを元に整形する
   * @param $userId
   * @param $numbers
   * @return array
   */
  public function settingUserLiveNumber($userId, $numbers, $shopId=0){
    $result = array();

    $body['date'] = date('Y-m-d');
    $body['user_id'] = $userId;
    $body['push_flg'] = 0;
    $body['shop_id'] = $shopId;
    $body['created'] = date('Y-m-d H:i:s');

    foreach($numbers as $number){
      if(!empty($number)) {
        $body['number'] = $number;
        $result[] = $body;
      }
    }

    return $result;
  }

  /**
   * bulk insert
   * @param $contents
   */
  public function setUserLiveNumber($contents){
    $query = $this->UserLiveNumber->query();
    $query->insert([
      'id',
      'shop_id',
      'user_id',
      'date',
      'number',
      'created'
    ]);
    if(!empty($contents)){
      foreach($contents as $data){
        $query->values($data);
      }
      $query->execute();
    }
  }

  /**
   * 削除
   * @param $id
   */
  public function deleteUserLiveNumber($id){
    $now = date('Y-m-d H:i:s');
    $query = $this->UserLiveNumber->query();

    $query->update()
      ->set(['deleted' => $now])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 整理券番号の最大値を取得
   *
   * @param $timeline
   */
  public function checkTweet($timeline,$now){
    $num = 0;

    $today = date('Y-m-d 00:00:00', strtotime($now));
    if(!empty($timeline)) {
      foreach ($timeline as $tweet) {
        $created_at = $tweet->created_at;
        // 投稿日Check
        if($created_at > $today){
          $text = $tweet->text;

          //「整理券番号」が含まれているか
          if(strpos($text,LIVE_SHOP_TICKET) !== false){
            $num = self::checkNums($text,$num);
          }
        }
      }
    }

    return $num;
  }

  /**
   * 一番大きい数字を返す
   *
   * @param $text
   * @param $num
   * @return mixed
   */
  public function checkNums($text,$num){
    $matches = array();

    $search = array('、',',');//[,][、]を変更する
    $text = mb_convert_kana(str_replace($search, '', $text), 'n');
    preg_match_all('/[\d]+/',$text,$matches );

    if(!empty($matches)){
      if(isset($matches[0])) {
        foreach ($matches[0] as $nums) {
          if ($num < $nums) {
            $num = $nums;
          }
        }
      }
    }

    return $num;
  }

  /**
   * 保存
   * @param $contents
   */
  public function setShopNumber($shopId, $number=0){
    $contents = array();
    $contents['shop_id'] = $shopId;
    $contents['date'] = date('Y-m-d');
    $contents['notification_number'] = $number;
    $contents['created'] = date('Y-m-d H:i:s');

    $query = $this->LiveShopNumber->query();
    $query->insert([
      'id',
      'shop_id',
      'date',
      'notification_number',
      'created'
    ]);
    //設定
    $query->values($contents);
    $query->execute();
  }

  /**
   * @param $id
   * @param $number
   */
  public function updateShopNumber($id, $number){
    $now = date('Y-m-d H:i:s');
    $query = $this->LiveShopNumber->query();

    $query->update()
      ->set(['notification_number' => $number])
      ->set(['updated' => $now])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 削除
   * @param $id
   */
  public function deleteShopNumber($id){
    $now = date('Y-m-d H:i:s');
    $query = $this->LiveShopNumber->query();

    $query->update()
      ->set(['deleted' => $now])
      ->where(['id' => $id])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * 取得当日のマスターを取得する
   * @return mixed
   */
  public function getShopNumber($shopId){
    $query=$this->LiveShopNumber->find()
      ->where(['shop_id' => $shopId])
      ->where(['deleted IS NULL']);
    $result = $query->first();
    if(!empty($result)){
      $result = $result->toArray();
  }

    return $result;
  }

  /**
   * 特定shop_idかつnum以下でpush_flg=0のデータを取得する
   *
   * @param $shopId
   * @param $num
   * @return mixed
   */
  public function checkUserNumber($shopId, $num){
    $query=$this->UserLiveNumber->find()
      ->where(['number <= ' => $num])
      ->where(['shop_id' => $shopId])
      ->where(['push_flg' => 0])
      ->where(['deleted IS NULL'])
      ->order(['date' => 'ASC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * push_flgを更新する
   *
   * @param $shopId
   * @param $num
   */
  public function updateUserNumber($shopId, $num){
    $now = date('Y-m-d H:i:s');
    $query = $this->UserLiveNumber->query();

    $query->update()
      ->set(['push_flg' => 1])
      ->set(['updated' => $now])
      ->where(['number <= ' => $num])
      ->where(['shop_id' => $shopId])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * @param int $offset
   * @param int $limit
   * @return mixed
   */
  public function getLantis($offset=0, $limit=10){
    $query=$this->Lantis->find()
      ->where(['deleted IS NULL'])
      ->limit($limit)
      ->offset($offset)
      ->order(['id' => 'DESC']);
    return $query->hydrate(false)->toArray();
  }

  /**
   * @param $contents
   */
  public function setLantis($contents)
  {
    $query = $this->Lantis->query();
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

  /**
   * 存在チェック　true:有る,false:無い
   * @param $title
   * @return bool
   */
  public function checkInfoTitle($title, $kind = AQOURS_KIND_RADIO){
    $query=$this->Information->find();
    $query->where(['title' => $title]);
    $query->where(['kind' => $kind]);
    $query->where(['deleted IS NULL']);

    $data = $query->hydrate(false)->toArray();
    if(!empty($data)){
      return true;
    }else{
      false;
    }
  }

  /**
   * 生放送情報取得
   * @return mixed
   */
  public function getNico(){
    $query=$this->Nico->find();
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * スクレイピング
   * @param $kind
   * @param null $id
   * @return mixed
   */
  public function getScraping($kind, $id=''){
    $query = $this->Scraping->find();

    $query->where(['kind' => $kind]);
    if(!empty($id)) $query->where(['id' => $id]);
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * スクレイピングデータ
   * @param $scrapingId
   * @param $id
   * @return mixed
   */
  public function getScrapingData($scrapingId, $id=''){
    $query = $this->ScrapingData->find();

    $query->where(['scraping_id' => $scrapingId]);
    if(!empty($id)) $query->where(['id' => $id]);
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * 更新する情報配列
   * @param $datas
   */
  public function setScrapingData($datas){
    foreach($datas as $data){
      if(!empty($data['id'])){
        self::updateScrapingData($data);
      }else{
        self::insertScrapingData($data);
      }
    }
  }

  /**
   * @param $data
   */
  public function insertScrapingData($data){
    $query = $this->ScrapingData->query();
    $query->insert([
      'id',
      'scraping_id',
      'url',
      'title',
      'contents_data',
      'link_num',
      'created'
    ]);
    if (!empty($data)) {
      $data['created'] = date('Y-m-d H:i:s');
      $query->values($data);
      $query->execute();
    }
  }

  /**
   * @param $data
   */
  public function updateScrapingData($data){
    $now = date('Y-m-d H:i:s');
    $query = $this->ScrapingData->query();

    $query->update()
      ->set(['contents_data' => $data['contents_data']])
      ->set(['link_num' => $data['link_num']])
      ->set(['updated' => $now])
      ->where(['id' => $data['id']])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * urlで判別
   * @param $url
   * @param $data
   */
  public function checkUrlData($url, $data){
    $result = [];
    foreach($data as $row){
      if($row['url'] == $url){
        $result = $row;
        break;
      }
    }

    return $result;
  }

  /**
   * タイトルで判別
   * @param $title
   * @param $data
   * @return array
   */
  public function checkTitleData($title, $data){
    $result = [];
    foreach($data as $row){
      if($row['title'] == $title){
        $result = $row;
        break;
      }
    }

    return $result;
  }

  /**
   * 初期化
   * @param $scrapingId
   * @param $url
   * @param $title
   */
  public function initUrlData($scrapingId, $url, $title){
    $result['scraping_id'] = $scrapingId;
    $result['url'] = $url;
    $result['title'] = $title;
    $result['contents_data'] = '';
    $result['link_num'] = 0;

    return $result;
  }

  /**
   * push_kind 初期化
   *
   * @param $usersId
   */
  public function initPushKind($usersId){
    $kinds = PUSH_KIND;
    $data['users_id'] = $usersId;
    $data['push_flg'] = ON_FLG;

    $query = $this->PushKind->query();
    $query->insert([
      'id',
      'users_id',
      'kind',
      'push_flg',
      'created'
    ]);
    foreach($kinds as $kind){
      $data['kind'] = $kind;
      $data['created'] = date('Y-m-d H:i:s');
      $query->values($data);
    }
    $query->execute();
  }

  /**
   * push_member 初期化
   *
   * @param $usersId
   */
  public function initPushMember($usersId){
    $data['users_id'] = $usersId;
    $data['push_flg'] = ON_FLG;
    $memberIds = PUSH_MEMBER_IDS;

    $query = $this->PushMember->query();
    $query->insert([
      'id',
      'users_id',
      'member_id',
      'push_flg',
      'created'
    ]);
    foreach($memberIds as $memberId => $name){
      $data['member_id'] = $memberId;
      $data['created'] = date('Y-m-d H:i:s');
      $query->values($data);
    }
    $query->execute();
  }

  /**
   * @param $data
   */
  public function updatePushKind($data){
    $now = date('Y-m-d H:i:s');
    $query = $this->PushKind->query();

    $query->update()
      ->set(['users_id' => $data['users_id']])
      ->set(['kind'     => $data['kind']])
      ->set(['push_flg' => $data['push_flg']])
      ->set(['updated'  => $now])
      ->where(['id'     => $data['id']])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * @param $data
   */
  public function updatePushMember($data){
    $now = date('Y-m-d H:i:s');
    $query = $this->PushMember->query();

    $query->update()
      ->set(['users_id' => $data['users_id']])
      ->set(['member_id'=> $data['member_id']])
      ->set(['push_flg' => $data['push_flg']])
      ->set(['updated'  => $now])
      ->where(['id'     => $data['id']])
      ->where(['deleted IS NULL'])
      ->execute();
  }

  /**
   * @param $kind
   * @return mixed
   */
  public function getPushKindUser($kind){
    $query=$this->PushKind->find();
    $query->where(['kind' => $kind]);
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * @param $memberId
   * @return mixed
   */
  public function getPushMemberUser($membersId){
    $query=$this->PushMember->find();
    $query->where(['member_id IN' => [$membersId]]);
    $query->where(['push_flg' => ON_FLG]);
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * @return mixed
   */
  public function getCheckBlog(){
    $query=$this->CheckBlog->find();
    $query->where(['deleted IS NULL']);

    return $query->hydrate(false)->toArray();
  }

  /**
   * @param $kind
   * @return int
   */
  public function getPushKind($kind){
    $pushKind = 0;

    $kinds = PUSH_KIND_CATEGORY;
    foreach($kinds as $kindId => $val){
      if(in_array($kind, $val)){
        $pushKind = $kindId;
        break;
      }
    }

    return $pushKind;
  }

  /**
   * ユーザーのpush設定取得
   *
   * @param $user
   * @return mixed
   */
  public function getUserSettings($user){
    $usersId = $user['id'];
    $result['user_id'] = $user['user_id'];
      // 大元PUSHフラグ
    $result['push_flg'] = $user['push_flg'];

    //種別
    $kind = PUSH_KIND_DISP;
    foreach($kind as $kindId => $kinds) {
      $result['kind'][$kindId] = self::findPushKindUser($kindId, $usersId);
    }

    foreach(PUSH_MEMBER_IDS as $memberId => $name) {
      // 暗号毎
      $result['member'][$memberId] = self::findPushMemberUser($memberId, $usersId);
    }

   return $result;
  }


  /**
   * @param $kind
   * @return mixed
   */
  public function findPushKindUser($kind, $usersId){
    $query=$this->PushKind->find();
    $query->where(['kind' => $kind]);
    $query->where(['users_id' => $usersId]);
    $query->where(['deleted IS NULL']);

    return $query->first()->toArray();
  }

  /**
   * @param $memberId
   * @return mixed
   */
  public function findPushMemberUser($membersId, $usersId){
    $query=$this->PushMember->find();
    $query->where(['member_id' => $membersId]);
    $query->where(['users_id' => $usersId]);
    $query->where(['deleted IS NULL']);

    return $query->first()->toArray();
  }

  /**
   * @param $usersId
   * @param $kinds
   */
  public function updateUserPushKind($usersId, $kinds){
    foreach($kinds as $kind => $pushFlg){
      $pushKind = self::findPushKindUser($kind, $usersId);
      if($pushKind['push_flg'] != $pushFlg){
        $pushKind['push_flg'] = $pushFlg;
        self::updatePushKind($pushKind);
      }
    }
  }

  /**
   * @param $usersId
   * @param $members
   */
  public function updateUserPushMember($usersId, $members){
    foreach($members as $membersId => $pushFlg){
      $pushMember = self::findPushMemberUser($membersId, $usersId);
      if($pushMember['push_flg'] != $pushFlg){
        $pushMember['push_flg'] = $pushFlg;
        self::updatePushMember($pushMember);
      }
    }
  }
}