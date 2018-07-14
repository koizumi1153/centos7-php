<style type="text/css">
.check input {
        display: none;
}
.check label{
        display: block;
        float: left;
        cursor: pointer;
        width: 60px;
        margin: 0;
        padding: 10px;
        background: #bdc3c7;
        color: #869198;
        font-size: 16px;
        text-align: center;
        line-height: 1;
        transition: .2s;
}
.check label:first-of-type{
        border-radius: 3px 0 0 3px;
}
.check label:last-of-type{
        border-radius: 0 3px 3px 0;
}
.check input[type="radio"]:checked + .switch-on {
        background-color: #a1b91d;
        color: #fff;
}
.check input[type="radio"]:checked + .switch-off {
        background-color: #e67168;
        color: #fff;
}
</style>

<div class="first">
<h2><?= __('PUSH設定') ?></h2>
<h3><?= __('PUSH設定') ?></h3>
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserPushFlg/{$userHash}" ],
                          "name" => "form-push_flg"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<div class="check">
    <input type="radio" name="push_flg" id="on" value="<?= ON_FLG ?>" <?php if($setting['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
    <label for="on" class="switch-on">ON</label>
    <input type="radio" name="push_flg" id="off" value="<?= OFF_FLG ?>" <?php if($setting['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
    <label for="off" class="switch-off">OFF</label>
</div>
<?= $this -> Form -> end (); ?>
</div>
<br />


<br />
<h3><?= __('PUSH種類設定') ?></h3>
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserSettingKind/{$userHash}" ],
                          "name" => "form-kind"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >


<?php
	$kind = PUSH_KIND_DISP;
  foreach($kind as $kindId => $title) { ?>
  <?= $this -> Form -> label ( $title, $title ); ?>

		<div class="check">
				<input type="radio" name="kinds[<?= $kindId; ?>]" id="kinds[<?= $kindId; ?>] on" value="<?= ON_FLG ?>" <?php if($setting['kind'][$kindId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
				<label for="on" class="switch-on">ON</label>
				<input type="radio" name="kinds[<?= $kindId; ?>]" id="kinds[<?= $kindId; ?>] off" value="<?= OFF_FLG ?>" <?php if($setting['kind'][$kindId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
				<label for="off" class="switch-off">OFF</label>
		</div>
<br /><br />
<?php } ?>
<?= $this -> Form -> end (); ?>
<br />
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

  		<div class="check">
  				<input type="radio" name="members[<?= $memberId; ?>]" id="members[<?= $memberId; ?>] on" value="<?= ON_FLG ?>" <?php if($setting['member'][$memberId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
  				<label for="on" class="switch-on">ON</label>
  				<input type="radio" name="members[<?= $memberId; ?>]" id="members[<?= $memberId; ?>] off" value="<?= OFF_FLG ?>" <?php if($setting['member'][$memberId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
  				<label for="off" class="switch-off">OFF</label>
  		</div>
<br /><br />
<?php } ?>
<?= $this -> Form -> end (); ?>
