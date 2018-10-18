<?php
/**
 * Created by PhpStorm.
 * User: koizumi
 * Date: 2018/09/28
 * Time: 12:55
 */

namespace App\Controller;


class AdminController extends AppController
{
    public $components = ["Aqours"];

    public function index()
    {
        $post = $this->request->getData();
        // デフォルト今月の予定表示
        $month = date('Y-m');
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        if(isset($post['month'])){
            //攻撃チェック
            if(mb_strlen($post['month']) == 7 && preg_match("\d{4}-\d{2}", $post['month'])) {
                $startDate = $post['month'] . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                $month = $post['month'];
            }
        }

        $list = $this->Aqours->getInformationLists($startDate, $endDate);
        $this->set('list', $list);
        $this->set('month', $month);
    }
}