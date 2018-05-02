<h2><?= __('整理券番号登録') ?></h2>

<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "master_add" ] ] ); ?>


<?= $this -> Form -> input ( "input", [ "type" => "text",
                                              "name" => "title",
                                              "size" => 200,
                                              "label" => "タイトル"] ); ?>

<?= $this -> Form -> input ( "input", [ "type" => "text",
                                              "name" => "date",
                                              "size" => 20,
                                              "label" => "日付",
                                              "id" => "datepicker"] ); ?>

<?= $this -> Form -> input ( "input", [ "type" => "text",
                                              "name" => "start_date",
                                              "size" => 20,
                                              "label" => "チェック開始日時",
                                              "id" => "datetimepicker1"] ); ?>

<?= $this -> Form -> input ( "input", [ "type" => "text",
                                              "name" => "end_date",
                                              "size" => 20,
                                              "label" => "チェック終了日時",
                                              "id" => "datetimepicker2"] ); ?>


<input type="submit" value="送信">

<?= $this -> Form -> end (); ?>


<div class="aqours index large-9 medium-8 columns content">
    <h3><?= __('登録イベント') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col">id</th>
                <th scope="col">title</th>
                <th scope="col">date</th>
                <th scope="col">start_date</th>
                <th scope="col">end_date</th>
                <th scope="col">screen_name</th>
                <th scope="col" class="actions">更新</th>
                <th scope="col" class="actions">削除</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($masters as $list): ?>
            <tr>
                <td><?= $this->Number->format($list['id']) ?></td>
                <td><?= $list['title'] ?></td>
                <td><?= $list['date']->i18nFormat('MM月dd日') ?></td>
                <td><?= $list['start_date']->i18nFormat('MM月dd日 H:i') ?></td>
                <td><?= $list['end_date']->i18nFormat('MM月dd日 H:i') ?></td>
                <?= $this -> Form -> create (
                                "null", [ "type" => "post",
                                          "url" => [ "controller" => "aqours",
                                                     "action" => "master_update/{$list['id']}" ] ] ); ?>
                <td>
                <input type="input" name="screen_name" required value="<?= h($list['screen_name']) ?>">

                </td>
                <td>
                    <input type="submit" value="更新">
                </td>
                <?= $this -> Form -> end (); ?>

                <td class="actions">
                  <?= $this->Form->postLink(__('Delete'), ['action' => 'master_delete', $list['id']], ['confirm' => __('削除してもよろしいでしょうか？', $list['id'])]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
  $(function () {
    $('#datepicker').datepicker({
        monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        showOtherMonths:true,
    });

    $('#datepicker1' '#datepicker2').datetimepicker({
        monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        dateFormat: 'yy-mm-dd HH:mm',
        hourGrid: 6,
        showOtherMonths:true,
    });
  });
</script>