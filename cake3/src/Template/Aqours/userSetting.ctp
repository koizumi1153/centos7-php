<h2><?= __('PUSH設定') ?></h2>

<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "update_user_push_flg" ],
                          "name" => "form-push_flg"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<?= $this -> Form -> label ( "push_flg", "PUSH全体フラグ" ); ?>
<input type="radio" name="push_flg" value="<?= OFF_FLG ?>" <?php if($setting['push_flg'] === OFF_FLG) echo "checked=checked";" ?> ONCHANGE="document.forms.form-push_flg.submit();>PUSH受け取らない
<input type="radio" name="push_flg" value="<?= ON_FLG ?>" <?php if($setting['push_flg'] === ON_FLG) echo "checked=checked";" ?> ONCHANGE="document.forms.form-push_flg.submit();>PUSH受け取る

<input type="submit" value="送信">
<?= $this -> Form -> end (); ?>


<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "update_user_setting_kind" ],
                          "name" => "form-kind"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<h3><?= __('PUSH種類設定') ?></h3>
<?php
	$kind = PUSH_KIND_DISP;
  foreach($kind as $kindId => $title) { ?>
  <?= $this -> Form -> label ( $title, $title ); ?>
<input type="radio" name="<?= $kindId; ?>" value="<?= OFF_FLG ?>" <?php if($setting['kind'][$kindId] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="document.forms.form-kind.submit();>PUSH受け取らない
<input type="radio" name="<?= $kindId; ?>" value="<?= ON_FLG ?>"  <?php if($setting['kind'][$kindId] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="document.forms.form-kind.submit();>PUSH受け取る

<?php } ?>

<input type="submit" value="送信">
<?= $this -> Form -> end (); ?>


<h3><?= __('PUSH推しメンバー設定') ?></h3>
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "update_user_setting_kind" ],
                          "name" => "form-member"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<?php
	$members = PUSH_MEMBER_IDS;
  foreach($members as $memberId => $name) { ?>
  <?= $this -> Form -> label ( $name, $name ); ?>
<input type="radio" name="<?= $memberId; ?>" value="<?= OFF_FLG ?>" <?php if($setting['member'][$memberId] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="document.forms.form-member.submit();>PUSH受け取らない
<input type="radio" name="<?= $memberId; ?>" value="<?= ON_FLG ?>"  <?php if($setting['member'][$memberId] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="document.forms.form-member.submit();>PUSH受け取る

<?php } ?>



<input type="submit" value="送信">

<?= $this -> Form -> end (); ?>
