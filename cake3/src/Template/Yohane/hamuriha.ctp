<form method="post" action="">
文字数<br />
<input type="text" id="calc" value="<?php echo isset($len) ? $len : 0; ?>" disabled><br />
<br />
<?= $this -> Form -> input ( "本文", [ "type" => "textarea",
                                              "name" => "body",
                                              "rows" => '5',
                                              "cols" => "5",
                                              "id"=>"hamuriha",
                                              "value" => "<?= $body; ?>"
                                              ] ); ?>
<br />
<br />


<button onclick="this.form.submit();">計測</button>
</form>
