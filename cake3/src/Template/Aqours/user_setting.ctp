<style>
.cb-enable, .cb-disable, .cb-enable span, .cb-disable span { background: url(/img/switch.gif) repeat-x; display: block; float: left; }
.cb-enable span, .cb-disable span { line-height: 30px; display: block; background-repeat: no-repeat; font-weight: bold; }
.cb-enable span { background-position: left -90px; padding: 0 10px; }
.cb-disable span { background-position: right -180px;padding: 0 10px; }
.cb-disable.selected { background-position: 0 -30px; }
.cb-disable.selected span { background-position: right -210px; color: #fff; }
.cb-enable.selected { background-position: 0 -60px; }
.cb-enable.selected span { background-position: left -150px; color: #fff; }
.switch label { cursor: pointer; }
.switch input { display: none; }
</style>

<h2><?= __('PUSH設定') ?></h2>

<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserPushFlg/{$userHash}" ],
                          "name" => "form-push_flg"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

  <?= $this -> Form -> label ( "push_flg", "push全体設定" ); ?>
<input type="radio" name="push_flg" class="cb-enable" value="<?= OFF_FLG ?>" <?php if($setting['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取らない
<input type="radio" name="push_flg" class="cb-disable" value="<?= ON_FLG ?>" <?php if($setting['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取る

<?= $this -> Form -> end (); ?>


<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserSettingKind/{$userHash}" ],
                          "name" => "form-kind"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<h3><?= __('PUSH種類設定') ?></h3>
<?php
	$kind = PUSH_KIND_DISP;
  foreach($kind as $kindId => $title) { ?>
  <?= $this -> Form -> label ( $title, $title ); ?>
<input type="radio" name="kinds[<?= $kindId; ?>]" class="cb-enable" value="<?= OFF_FLG ?>" <?php if($setting['kind'][$kindId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取らない
<input type="radio" name="kinds[<?= $kindId; ?>]" class="cb-disable" value="<?= ON_FLG ?>"  <?php if($setting['kind'][$kindId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取る
<br /><br />
<?php } ?>

<?= $this -> Form -> end (); ?>


<h3><?= __('PUSH推しメンバー設定') ?></h3>
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserSettingMember/{$userHash}" ],
                          "name" => "form-member"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<?php
	$members = PUSH_MEMBER_IDS;
  foreach($members as $memberId => $name) { ?>
  <?= $this -> Form -> label ( $name, $name ); ?>
<input type="radio" name="members[<?= $memberId; ?>]" class="cb-enable" value="<?= OFF_FLG ?>" <?php if($setting['member'][$memberId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取らない
<input type="radio" name="members[<?= $memberId; ?>]" class="cb-disable" value="<?= ON_FLG ?>"  <?php if($setting['member'][$memberId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">PUSH受け取る
<br /><br />
<?php } ?>

<?= $this -> Form -> end (); ?>

<script>
$(document).ready( function(){
    $(".cb-enable").click(function(){
        var parent = $(this).parents('.switch');
        $('.cb-disable',parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox',parent).attr('checked', true);
    });
    $(".cb-disable").click(function(){
        var parent = $(this).parents('.switch');
        $('.cb-enable',parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox',parent).attr('checked', false);
    });
});
</script>