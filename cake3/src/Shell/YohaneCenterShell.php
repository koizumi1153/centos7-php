<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Controller\ComponentRegistry;
use App\Controller\Component\TwitterComponent;

class AqoursBlogShell extends Shell
{

    public function initialize() {
        // component
        $this->Twitter= new TwitterComponent(new ComponentRegistry());
    }

    public function main()
    {

        $str="小林愛香と一緒に同じ夢を見て、同じ夢を叶えよう！！\n\n#津島善子センター計画\n#小林愛香センター計画\n#津島善子センタープロジェクト\n";
        $img = "yohane/4thcenter.jpg";
        $this->Twitter->setImgPost($str, $img);
    }
}