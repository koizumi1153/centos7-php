<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\TwitterComponent;
use App\Controller\Component\PresentComponent;

class PresentShell extends Shell
{
    public function initialize() {
        // component
        $this->Twitter= new TwitterComponent(new ComponentRegistry());
        $this->Present= new PresentComponent(new ComponentRegistry());
    }

    public function main()
    {
        $now = date('Y-m-d H:i:s');

        //時間チェック
        $presents = $this->Present->getDate($now);
        if(!empty($presents)){
            foreach($presents as $present) {
                $ids = array();
                //kindチェック
                if ($present['kind'] == PRESENT_ONCE && $present['retweet_flg'] == ON_FLG) {
                    break;
                } elseif($present['kind'] == PRESENT_ALL && $present['retweet_flg'] == ON_FLG) {
                    $ids = explode(',', $present['retweet_ids']);
                }

                //フォロー処理を入れる
                $this->Twitter->setFollowScreenName($present['screen_name']);

                //最新のタイムラインを取得する(10件)
                $timeLines = $this->Twitter->getUserTimeline($present['screen_name'], 10);

                //タイムラインチェック
                foreach($timeLines as $timeLine){
                    //リツイートしたものは除外する
                    if(!in_array($timeLine->id_str, $ids)){
                        if (strpos($timeLine->text, $present['word']) !== false){
                            $this->Twitter->retweet($timeLine->id_str);
                            $ids[] = $timeLine->id_str;
                            $this->Present->update($present['id'],$ids);
                        }
                    }
                }
                sleep(1);
            }
        }
    }


}