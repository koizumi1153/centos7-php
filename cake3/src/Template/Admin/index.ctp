<nav class="large-3 medium-4 columns" id="actions-sidebar">
</nav>
<div class="users index large-9 medium-8 columns content">
    <h3><?= date('Y年m月',strtotime($month)) ?></h3>

  <?= $this->Form->create (); ?>
  <input type="text" name="month" id="datepicker" value="<?= $month ?>">
  <?= $this->Form->end(); ?>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('kind') ?></th>
                <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                <th scope="col"><?= $this->Paginator->sort('discription') ?></th>
                <th scope="col"><?= $this->Paginator->sort('date') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $info): ?>
            <tr>
                <td><?= $this->Number->format($info->id) ?></td>
                <td><?= $this->Number->format($info->kind) ?></td>
                <td><?= h($info->title) ?></td>
                <td><?= h($info->discription) ?></td>
                <td><?= h($info->date) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
