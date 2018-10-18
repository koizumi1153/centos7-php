<nav class="large-3 medium-4 columns" id="actions-sidebar">
</nav>
<div class="users index large-9 medium-8 columns content">
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
