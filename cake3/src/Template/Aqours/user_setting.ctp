<style type="text/css">
.contents {
				text-align: center;
}
.check input{
        display: none;
}

.check label{
        display: block;
        float: center;
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

.first input[type="radio"]:checked + .switch-on{
    background-color: #a1b91d;
    color: #fff;
}

.first input[type="radio"]:checked + .switch-off{
    background-color: #e67168;
    color: #fff;
}

</style>
<div class="contents">

<div class="first">
<h3><?= __('PUSH設定') ?></h3>
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "aqours",
                                     "action" => "updateUserPushFlg/{$userHash}" ],
                          "name" => "form-push_flg"] ); ?>
<input type="hidden" name="user_id" value="<?= $setting['user_id'] ?>" >

<div class="check check-flg">
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

		<div class=" kind<?= $kindId; ?>">
				<input type="radio" name="kinds[<?= $kindId; ?>]" id="kinds[<?= $kindId; ?>] on" value="<?= ON_FLG ?>" <?php if($setting['kind'][$kindId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
				<label for="on" class="switch-on">ON</label>
				<input type="radio" name="kinds[<?= $kindId; ?>]" id="kinds[<?= $kindId; ?>] off" value="<?= OFF_FLG ?>" <?php if($setting['kind'][$kindId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
				<label for="off" class="switch-off">OFF</label>
		</div>
<br />
<?php } ?>
<?= $this -> Form -> end (); ?>

<br />
<h3><?= __('PUSHメンバー設定') ?></h3>
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

  		<div class=" member<?= $memberId; ?>">
  				<input type="radio" name="members[<?= $memberId; ?>]" id="members[<?= $memberId; ?>] on" value="<?= ON_FLG ?>" <?php if($setting['member'][$memberId]['push_flg'] === ON_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
  				<label for="on" class="switch-on">ON</label>
  				<input type="radio" name="members[<?= $memberId; ?>]" id="members[<?= $memberId; ?>] off" value="<?= OFF_FLG ?>" <?php if($setting['member'][$memberId]['push_flg'] === OFF_FLG) echo "checked=checked"; ?> ONCHANGE="submit(this.form)">
  				<label for="off" class="switch-off">OFF</label>
  		</div>
<br />
<?php } ?>
<?= $this -> Form -> end (); ?>

<a target="_blank"  href="https://www.amazon.co.jp/gp/product/B07DLJBGNW/ref=as_li_tl?ie=UTF8&camp=247&creative=1211&creativeASIN=B07DLJBGNW&linkCode=as2&tag=angelnet-22&linkId=ab16ce17ba743863b19d12b1eb111f7d"><img border="0" src="//ws-fe.amazon-adsystem.com/widgets/q?_encoding=UTF8&MarketPlace=JP&ASIN=B07DLJBGNW&ServiceVersion=20070822&ID=AsinImage&WS=1&Format=_SL250_&tag=angelnet-22" ></a><img src="//ir-jp.amazon-adsystem.com/e/ir?t=angelnet-22&l=am2&o=9&a=B07DLJBGNW" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />

</div>