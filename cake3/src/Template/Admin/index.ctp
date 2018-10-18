<?php
$this->layout = false;
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
      情報通知 月間予定
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
  <style>
  .displayNone {
  display: none;
  }

  .accordion {
  margin: 0 0 10px;
  padding: 10px;
  border: 1px solid #ccc;
  }

  .switch {
  font-weight: bold;
  }

  .open {
  text-decoration: underline;
  }
  </style>
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
                <th scope="col"><?= $this->Paginator->sort('日付:タイトル') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $info): ?>
            <tr>
              <td>
                <div class="accordion">
                  <p class="switch"><?= h($info['date']) ?>:
                  <?= h($info['title']) ?></p>
                  <div class="contentWrap displayNone">
                  <?= h(DISP_KINDS[$info['kind']]); ?><br /><br />
                  <?= nl2br($info['discription']); ?>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<script>
    (function($) {
        // 読み込んだら開始
        $(function() {

            // アコーディオン
            var accordion = $(".accordion");
            accordion.each(function () {
                var noTargetAccordion = $(this).siblings(accordion);
                $(this).find(".switch").click(function() {
                    $(this).next(".contentWrap").slideToggle();
                    $(this).toggleClass("open");
                    noTargetAccordion.find(".contentWrap").slideUp();
                    noTargetAccordion.find(".switch").removeClass("open");
                });
            });

        });
    })(jQuery);
</script>