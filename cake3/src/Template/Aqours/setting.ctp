<h2><?= __('整理券番号登録') ?></h2>

<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "add/{$userHash}" ] ] ); ?>

<form method="post" accept-charset="utf-8" action="/aqours/add/<?= ?>">

<?php for ($num=1;$num<=LIVE_SHOP_NUMBER_MAX;$num++){ ?>

<label for="text<?= $num; ?>">整理券番号<?= $num; ?>:</label>
<input type="input" name="numbers[]" "pattern"="^[0-9]+$" <?php if($num==1):?> required <?php endif;?> id="text<?= $num; ?>">

<?php }//endfor ?>

<input type="submit" value="送信">

<?= $this -> Form -> end (); ?>


<div class="aqours index large-9 medium-8 columns content">
    <h3><?= __('登録済番号') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col">番号</th>
                <th scope="col">通知状況</th>
                <th scope="col" class="actions">削除</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lists as $list): ?>
            <tr>
                <td><?= $this->Number->format($list['number']) ?></td>
                <td><?php if($list['push_flg'] == 0){ ?>未通知<?php }else{ ?><span style="color: #ff0000; background-color: transparent">通知済</span><?php } ?></td>
                <td class="actions">
                  <?= $this->Form->postLink(__('Delete'), ['action' => 'delete/{$userHash', $list['id']], ['confirm' => __('削除してもよろしいでしょうか？', $list['id'])]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
