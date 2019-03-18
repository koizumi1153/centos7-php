<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\TwitterComponent;

class YohaneCenterShell extends Shell
{
    protected $BaseId = 1; //baseId

    public function initialize() {
        // component
        $this->Twitter= new TwitterComponent(new ComponentRegistry());
    }

    public function main()
    {
        $img = '';
        $word = '';
        $wordId = 0;
        $useCount = 0;

        $now = date('Y-m-d H:i:00');
        $base = $this->Twitter->getBase($this->BaseId);
        if(!empty($base)){
            //時間チェック
            $date = $this->Twitter->getDate($base['id'], $now);
            if(!empty($date)){
                // 基本画像
                $img  = $base['img'];
                // 基本ワード
                $baseWord = $base['word'];

                //文言チェック
                $words = $this->Twitter->getWords($date['word_ids']);
                if(!empty($words)){
                    $count = count($words);
                    if($count == 1){
                        $wordId = $words[0]['id'];
                        $useCount = $words[0]['use_count'];

                        // word
                        $word = $words[0]['word'];
                        if(!empty($base['url'])) $word .= "\n".$base['url'];
                        $word .= "\n\n".$baseWord;

                        // img
                        if(!empty($words[0]['img'])) $img = $words[0]['img'];
                    }else{
                        //件数が少ないもので配列化
                        $cnt=0;
                        $minCount = 0;
                        $wordsArr = [];
                        foreach($words as $row){
                            if($cnt == 0){
                                $minCount = $row['use_count'];
                                $wordsArr[] = $row;
                            }elseif($minCount == $row['use_count']){
                                $wordsArr[] = $row;
                            }
                            $cnt++;
                        }


                        $int = rand(0,(count($wordsArr) -1));
                        $wordId = $wordsArr[$int]['id'];
                        $useCount = $wordsArr[$int]['use_count'];

                        // word
                        $word = $wordsArr[$int]['word'];
                        if(!empty($base['url'])) $word .= "\n".$base['url'];
                        $word .= "\n\n".$baseWord;

                        // img
                        if(!empty($wordsArr[$int]['img'])) $img = $wordsArr[$int]['img'];
                    }

                    // 更新
                    if(!empty($wordId)) $this->Twitter->updateCount($wordId, $useCount);

                    if(!empty($word) || !empty($img)) {
                        $result = $this->Twitter->setImgPost($word, $img, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
                        if(!empty($result->id_str)){
                            sleep(1);
                            // リツイート
                            $this->Twitter->retweet($result->id_str);
                        }
                    }
                }
            }
        }
    }

    /**
     * 文言は要調整
     * RT済みはエラーになるが…できれば除外したい。
     */
    public function getTweet($userName=''){
        $base = $this->Twitter->getBase($this->BaseId);
        $result = $this->Twitter->getUserTimeline($userName, 3, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
        if(!empty($result)) {
            foreach ($result as $row) {
                if (strpos($row->text, '選挙') !== false || strpos($row->text, '堕天ロード') !== false || strpos($row->text, '投票') !== false) {
                    if($this->Twitter->checkLog($this->BaseId,$row->id_str)) {
                        $this->Twitter->retweet($row->id_str, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
                        $this->Twitter->insertLog($this->BaseId,$row->id_str);
                    }
                }
            }
        }
    }


    /**
     * ツイートをRTした人をフォローする
     * フォロワーをフォロバする
     */
    public function follow(){
        $base = $this->Twitter->getBase($this->BaseId);
        $result = $this->Twitter->getUserTimeline($base['screen_name'],3);
        foreach($result as $row){
            $users = $this->Twitter->getRetweetUser($row->id_str, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
            if(!empty($users)){
                $cnt = 0;
                foreach($users as $user){
                    $this->Twitter->setFollow($user->user, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
                    $cnt++;
                    if(!($cnt % 100)) sleep(1);
                }
            }
        }

        sleep(1);

        $list = $this->Twitter->getFollowersList($base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
        if(!empty($list->users)){
            $cnt = 0;
            foreach($list->users as $user){
                $this->Twitter->setFollow($user, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
                $cnt++;
                if(!($cnt % 100)) sleep(1);
            }
        }
    }

}