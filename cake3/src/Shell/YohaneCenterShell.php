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
        $consumer_key='7DYuCu9prA4w1a72xa8PLDhqC';
        $consumer_secret='frwouwxUOnPiCfHWOkY1SaqtUWDJouo5yL5fNFA1zsdB1630TY';
        $api_token = "1064543763870081025-UsVYb5doFnvfJdcB99QEss0yqOk7TF";
        $api_token_secret = "KdcbYS4FsJVKWLbKQjs4xQ4fB8Wo48zYjzqQljKj0mDPZ";

        $str = <<<EOT
みなさまフォローありがとうございます。
こちらは投票期間中に投票忘れを防止するためのBOTです。

【津島善子への投票を強制するものではありません】

また、リプ等いただきましても基本的には返事をしておりません。
あらかじめご了承ください。
EOT;
#        $img = "yohane/4thcenter.jpg";
        $this->Twitter->setImgPost($str, $img, $consumer_key, $consumer_secret, $api_token, $api_token_secret);
    }
}