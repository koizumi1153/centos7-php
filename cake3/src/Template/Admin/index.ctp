<?php
$this->layout = false;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
      <?= $title ?>
  </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('cake.css') ?>
    <?= $this->Html->css('jquery-ui.min.css') ?>
    <?= $this->Html->script('jquery-3.3.1.min.js') ?>
    <?= $this->Html->script('jquery-ui.min.js') ?>

    <?= $this->Html->css('jquery-ui-timepicker-addon.css') ?>
    <?= $this->Html->script('jquery-ui-timepicker-addon.js') ?>
    <?= $this->Html->script('jquery-ui-timepicker-ja.js') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
<div class="users index columns content">
    <h3><?= date('Y年m月',strtotime($month)) ?></h3>

  <?= $this->Form->create (); ?>
  <input type="text" name="month" id="datepicker" value="<?= $month ?>">
  <input type="submit" value="送信">
  <?= $this->Form->end(); ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('日付') ?></th>
                <th scope="col"><?= $this->Paginator->sort('タイトル') ?></th>
                <th scope="col"><?= $this->Paginator->sort('種類') ?></th>
                <th scope="col"><?= $this->Paginator->sort('内容') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $info): ?>
            <tr>
              <td><?= h($info['date']) ?></td>
              <td><?= h($info['title']) ?></td>
                <td><?= h(DISP_KINDS[$info['kind']]) ?></td>
                <td><?= nl2br($info['discription']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
