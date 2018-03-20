<?php
return [
  //////////
  /// LINE BOT用共通定数
  //////////

  // api url
  define('LINE_API_URL', 'https://api.line.me/v2/bot/message/reply'),
  define('LINE_API_PUSH_URL', 'https://api.line.me/v2/bot/message/push'), // 単一ユーザーPUSH
  define('LINE_API_MULTI_URL','https://api.line.me/v2/bot/message/multicast'), // 複数ユーザーPUSH
  define('LINE_MULTI_USER', '150'), //複数ユーザーID数
  define('LINE_MESSAGE_COUNT', '5'), //メッセージ送信可能数
  define('LINE_MESSAGE_LENGTH', 998), //メッセージ長さ

  // action
  define('ACTION_POST_BACK',  'postback'),        // ポストバックアクション
  define('ACTION_MESSAGE',    'message'),         // メッセージアクション
  define('ACTION_URI',        'uri'),             // URIアクション
  define('ACTION_DATE_TIME',  'datetimepicker'),  // 日時選択アクション

  //postback time
  define('SELECT_DATE','date'),         // 例：2017-06-18
  define('SELECT_TIME','time'),         // 例：00:00
  define('SELECT_DATETIME','datetime'), // 例：2017-06-18T06:15

  //postback.data
  define('POSTBACK_SELECT_PUSH_TIME','select_time'),  //push時間

  //kind
  define('FORTUNE',      1), //占い
  define('WEATHERS',     2), //占い
  define('MAPS',         3), //観光 地図案内
  define('WORDS',      999), //その他

  define('PUSH',      1000), //push通知可否変更
  define('PUSHON',    1001), //push通知ON
  define('PUSHOFF',   1002), //push通知OFF
  define('PUSHTIME',  1003), //push通知時間変更

  //priority
  define('PRIORITY_DEFAULT', 0), // 全て
  define('PRIORITY_BEFORE',  1), // 前
  define('PRIORITY_AFTER',   2), // 後ろ

  // 共通フラグ 0 or 1
  define('OFF_FLG', 0),
  define('ON_FLG',  1),

  //////////
  /// YOHANE関連
  //////////
  define('YOHANE_IMG_URL', 'https://line.yohane.work/img/'),// 画像置き場

  //////////
  /// 楽天API関連
  //////////
  //baseurl
  define('RAKUTEN_BASE_URL', 'https://app.rakuten.co.jp/services/api/_KIND_/Search/20170404?format=json'),
  //総合
  define('TOTAL_BASE', 'BooksTotal'),
  //楽天ブックス書籍検索
  define('BOKK_BASE','BooksBook'),
  //楽天ブックスCD検索
  define('CD_BASE','BooksCD'),
  //楽天ブックスDVD Blu-Ray検索
  define('DVD_BASE','BooksDVD'),
  //楽天ブックス雑誌検索
  define('MAGAZINE_BASE','BooksMagazine'),

  //////////
  /// Google Books API関連
  //////////
  //baseurl
  define('GOOGLE_BASE_URL', 'https://www.googleapis.com/books/v1/volumes?q=_KEYWORD_&country=JP'),

  //////////
  /// Aqours関連
  //////////
  define('AQOURS_IMG_DIR', '/var/www/cake/cake3/webroot/img/aqours/'), //画像置き場指定
  define('AQOURS_IMG_URL', 'https://line.yohane.work/img/aqours/'),// 画像置き場


  //検索キーワード
  define('AQOURS_KEYWORDS',['ラブライブ!サンシャイン!!','Aqours','CYaRon','AZALEA','Guilty Kiss',
    '高海 千歌','伊波杏樹',
    '桜内 梨子','逢田梨香子',
    '松浦 果南','諏訪ななか',
    '黒澤 ダイヤ','小宮有紗',
    '渡辺 曜','斉藤朱夏',
    '津島 善子','小林愛香',
    '国木田 花丸','高槻かなこ',
    '小原 鞠莉','鈴木愛奈',
    '黒澤 ルビィ','降幡愛',
    "電撃G'sマガジン"]),

  define('AQOURS_EXCLUSION_WORDS', ['先着特典','通常版','通常盤']),

  // 検索種別
  define('AQOURS_RAKUTEN_KIND', [BOKK_BASE,CD_BASE,DVD_BASE,MAGAZINE_BASE]),

  define('AQOURS_KIND_BOOK',  1), //本の情報
  define('AQOURS_KIND_CD',    2), //CDの情報
  define('AQOURS_KIND_DVD',   3), //DVD&Blu-rayの情報
  define('AQOURS_KIND_EVENT', 4), //イベントの情報
  define('AQOURS_KIND_TV',    5), //TVの情報
  define('AQOURS_KIND_RADIO', 6), //ラジオ・ネットラジオ・ニコ生などの情報
  define('AQOURS_KIND_TICKET',7), //チケットの情報
  define('AQOURS_KIND_GOODS', 8), //グッズなどの販売情報

  define('PUSH_NONE', 0),   // PUSH不要
  define('PUSH_READY', 1),  // PUSH準備
  define('PUSH_FINISH', 2), // PUSH完了

  define('AQOURS_BLOG_RSS_URLS', [
    'https://lineblog.me/anju_inami/index.rdf',
    'https://lineblog.me/kobayashi_aika/index.rdf',
    'http://rssblog.ameba.jp/shuka-saito/rss20.xml'
  ]),

  define('AQOURS_BLOG_NAMES', [
    '伊波杏樹〜日々精進。〜',
    '小林愛香オフィシャルブログ',
    '斉藤朱夏オフィシャルブログ「しゅか通信」'
  ]),

  //////////
  /// 天気API関連
  //////////
  define('WEATHER_MAP_API','59b30fe63228c4f874f3786f71d36c3d'), //APIキー
  define('WEATHER_MAP_WEATHER_URL', 'http://api.openweathermap.org/data/2.5/weather'),  // 現在の天気
  define('WEATHER_MAP_FORECAST_URL','http://api.openweathermap.org/data/2.5/forecast'), // 天気予報

  //////////
  // スクレイピング用
  //////////
  define('SCRAPING_URL_SUNSHINE_BASE','http://www.lovelive-anime.jp/uranohoshi/'),
  define('SCRAPING_URL_SUNSHINE_CD',      'news41.php'),
  define('SCRAPING_URL_SUNSHINE_BD',      'news42.php'),
  define('SCRAPING_URL_SUNSHINE_ANIME',   'news43.php'),
  define('SCRAPING_URL_SUNSHINE_RADIO',   'news44.php'),
  define('SCRAPING_URL_SUNSHINE_EVENT',   'news45.php'),
  define('SCRAPING_URL_SUNSHINE_BOOK',    'news46.php'),
  define('SCRAPING_URL_SUNSHINE_GOODS',   'news47.php'),
  define('SCRAPING_URL_SUNSHINE_GAME',    'news48.php'),
  define('SCRAPING_URL_SUNSHINE_MEDIA',   'news50.php'),
  define('SCRAPING_URL_SUNSHINE_TOURISM', 'news51.php'),
  define('SCRAPING_URL_SUNSHINE_OTHER',   'news49.php'),

  //カテゴリcategory
  define('SCRAPING_CATEGORY_SUNSHINE_CD',       '41'),
  define('SCRAPING_CATEGORY_SUNSHINE_BD',       '42'),
  define('SCRAPING_CATEGORY_SUNSHINE_ANIME',    '43'),
  define('SCRAPING_CATEGORY_SUNSHINE_RADIO',    '44'),
  define('SCRAPING_CATEGORY_SUNSHINE_EVENT',    '45'),
  define('SCRAPING_CATEGORY_SUNSHINE_BOOK',     '46'),
  define('SCRAPING_CATEGORY_SUNSHINE_GOODS',    '47'),
  define('SCRAPING_CATEGORY_SUNSHINE_GAME',     '48'),
  define('SCRAPING_CATEGORY_SUNSHINE_MEDIA',    '50'),
  define('SCRAPING_CATEGORY_SUNSHINE_TOURISM',  '51'),
  define('SCRAPING_CATEGORY_SUNSHINE_OTHER',    '49'),

  define('SCRAPING_URL_SUNSHINE_LIST', [
    SCRAPING_CATEGORY_SUNSHINE_CD     => SCRAPING_URL_SUNSHINE_CD,
    SCRAPING_CATEGORY_SUNSHINE_BD     => SCRAPING_URL_SUNSHINE_BD,
    SCRAPING_CATEGORY_SUNSHINE_ANIME  => SCRAPING_URL_SUNSHINE_ANIME,
    SCRAPING_CATEGORY_SUNSHINE_RADIO  => SCRAPING_URL_SUNSHINE_RADIO,
    SCRAPING_CATEGORY_SUNSHINE_EVENT  => SCRAPING_URL_SUNSHINE_EVENT,
    SCRAPING_CATEGORY_SUNSHINE_BOOK   => SCRAPING_URL_SUNSHINE_BOOK,
    SCRAPING_CATEGORY_SUNSHINE_GOODS  => SCRAPING_URL_SUNSHINE_GOODS,
    SCRAPING_CATEGORY_SUNSHINE_GAME   => SCRAPING_URL_SUNSHINE_GAME,
    SCRAPING_CATEGORY_SUNSHINE_MEDIA  => SCRAPING_URL_SUNSHINE_MEDIA,
    SCRAPING_CATEGORY_SUNSHINE_TOURISM=> SCRAPING_URL_SUNSHINE_TOURISM,
    SCRAPING_CATEGORY_SUNSHINE_OTHER  => SCRAPING_URL_SUNSHINE_OTHER,
  ]),
];//defien
