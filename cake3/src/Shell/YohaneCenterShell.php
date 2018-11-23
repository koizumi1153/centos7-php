<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\TwitterComponent;

class YohaneCenterShell extends Shell
{

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
        $base = $this->Twitter->getBase(1);
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
                        $int = rand(0,($count -1));
                        $wordId = $words[$int]['id'];
                        $useCount = $words[$int]['use_count'];

                        // word
                        $word = $words[$int]['word'];
                        if(!empty($base['url'])) $word .= "\n".$base['url'];
                        $word .= "\n\n".$baseWord;

                        // img
                        if(!empty($words[$int]['img'])) $img = $words[$int]['img'];
                    }

                    // 更新
                    if(!empty($wordId)) $this->Twitter->updateCount($wordId, $useCount);
                }
            }
        }

        if(!empty($word) || !empty($img)) {
            $this->Twitter->setImgPost($word, $img, $base['consumer_key'], $base['consumer_secret'], $base['api_token'], $base['api_token_secret']);
        }
    }
}