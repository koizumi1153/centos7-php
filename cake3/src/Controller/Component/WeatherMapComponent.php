<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\TableRegistry;

class WeatherMapComponent extends Component
{
  /**
   * 都市名検索URL(現在の天気)
   * @param $city
   * @return string
   */
    public function getWeatherUrlFromCity($city){
      return WEATHER_MAP_WEATHER_URL. "?q=".$city."&units=metric&appid=".WEATHER_MAP_API;
    }

  /**
   * 都市名検索URL(天気予報)
   * @param $city
   * @return string
   */
  public function getForecastUrlFromCity($city){
      return WEATHER_MAP_FORECAST_URL. "?q=".$city."&units=metric&appid=".WEATHER_MAP_API;
    }

  /**
   * 緯度経度検索URL(現在の天気)
   * @param $latitude
   * @param $longitude
   * @return string
   */
    public function getWeatherUrlFromLatAndLon($latitude, $longitude){
      return WEATHER_MAP_WEATHER_URL. "?lat=".$latitude."&lon=".$longitude."&units=metric&appid=".WEATHER_MAP_API;
    }

  /**
   * 緯度経度検索URL(天気予報)
   * @param $latitude
   * @param $longitude
   * @return string
   */
    public function getForecastUrlFromLatAndLon($latitude, $longitude){
      return WEATHER_MAP_FORECAST_URL. "?lat=".$latitude."&lon=".$longitude."&units=metric&appid=".WEATHER_MAP_API;
    }

    public function getWeatherText($weather){
      $text = '';

      if(isset($weather['weather'][0]['id'])){
        $main = $this->getMainText($weather['weather'][0]['main']);
        $description = $this->getWeatherDescription($weather['weather'][0]['id']);
        $text .= "今の天気は".$main."で、".$description."です。\n";
      }

      if(isset($weather['wind']['deg']) && isset($weather['wind']['speed'])){
        $digger = $this->getWindDigger($weather['wind']['deg']);
        $text .= $digger."向きの風、風速".$weather['wind']['speed']."メートル。\n";
      }

      if(isset($weather['main']['temp'])){
        $text .= "温度は".$weather['main']['temp']."度です。";
      }

      return $text;
    }

    public function getMainText($main){
      $inEnglish = array('clear', 'clouds', 'rain', 'snow');
      $inJapanese = array('晴れ', 'くもり', '雨', '雪');
      $key = array_search($main,$inEnglish);
      if($key !== false){
        return $inJapanese[$key];
      }
      return $main;
    }

  /**
   * 天気詳細
   *
   * @param $id
   * @return string
   */
    public function getWeatherDescription($id){
      $description[200] = '雨が降る雷雨';
      $description[201] = '雨が降る雷雨';
      $description[202] = '豪雨による雷雨';
      $description[210] = '雷雨';
      $description[211] = '雷雨';
      $description[212] = '重い雷雨';
      $description[221] = '荒れた雷雨';
      $description[230] = '軽い霧雨で雷雨';
      $description[231] = '雷雨と霧雨';
      $description[232] = '重い霧雨で雷雨';
      $description[300] = '光度の霧雨';
      $description[301] = '霧雨';
      $description[302] = '重い霧雨の霧';
      $description[310] = '光度降雨雨';
      $description[311] = '霧雨';
      $description[312] = '強い霧雨';
      $description[313] = 'にわか雨と霧雨';
      $description[314] = '重いにわか雨と霧雨';
      $description[321] = 'にわか霧雨';
      $description[500] = '小雨';
      $description[501] = '中程度の雨';
      $description[502] = '強い雨';
      $description[503] = '非常に豪雨';
      $description[504] = '極端な雨';
      $description[511] = '雨氷';
      $description[520] = '強度のにわか雨';
      $description[521] = 'にわか雨';
      $description[522] = '激しいにわか雨';
      $description[531] = '不規則なにわか雨';
      $description[600] = '小雪';
      $description[601] = '雪';
      $description[602] = '大雪';
      $description[611] = 'みぞれ';
      $description[612] = 'にわかみぞれ';
      $description[615] = '明るい雨と雪';
      $description[616] = '雨や雪';
      $description[620] = '雷とにわか雪';
      $description[621] = 'にわか雪';
      $description[622] = '重いにわか雪';
      $description[701] = 'ミスト';
      $description[711] = '煙';
      $description[721] = 'ヘイズ';
      $description[731] = '砂、ほこり旋回する';
      $description[741] = '霧';
      $description[751] = '砂';
      $description[761] = 'ほこり';
      $description[762] = '火山灰';
      $description[771] = 'スコール';
      $description[781] = '竜巻';
      $description[800] = '晴天';
      $description[801] = '薄い雲';
      $description[802] = '雲';
      $description[803] = '曇りがち';
      $description[804] = '厚い雲';
      $description[900] = '竜巻';
      $description[901] = '熱帯低気圧';
      $description[902] = 'ハリケーン';
      $description[903] = '寒い';
      $description[904] = '暑い';
      $description[905] = '風が強い';
      $description[906] = '雹';
      $description[951] = '落ち着いた';
      $description[952] = 'そよ風';
      $description[953] = 'そよ風';
      $description[954] = '中風';
      $description[955] = '新鮮な風';
      $description[956] = '強い風';
      $description[957] = '高風、近くの暴風';
      $description[958] = 'ガール';
      $description[959] = '深刻な暴風';
      $description[960] = '嵐';
      $description[961] = '暴風雨';
      $description[962] = 'ハリケーン';

      if(isset($description[$id])){
        return $description[$id];
      }

      return '';
    }

  /**
   * 風向き計算
   *
   * @param $digree
   * @return mixed
   */
    public function getWindDigger($digree){
      $dname = ["北","北北東","北東", "東北東", "東", "東南東", "南東", "南南東", "南", "南南西", "南西", "西南西", "西", "西北西", "北西", "北北西", "北"];
      $dindex = round($digree / 22.5);

      return $dname[$dindex];
    }

}