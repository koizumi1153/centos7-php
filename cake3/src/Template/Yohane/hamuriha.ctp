<form method="post" action="">
文字数<br />
<input type="text" id="calc" value="<?php echo isset($len) ? $len : 0; ?>" disabled><br />
<br />
<?= $this -> Form -> input ( "本文", [ "type" => "textarea",
                                              "name" => "body",
                                              "rows" => '5',
                                              "cols" => "5",
                                              "id"=>"hamuriha",
                                              "value" => $body,
                                              ] ); ?>
<br />
<br />
  <button type="submit" >PHP計測</button><br />
  ※改行や空白は文字数としてカウントしない<br />
</form>
<br />
  <button id="test">JS計測</button><br />
※改行や空白も文字数としてカウントする<br />

<script>
    document.getElementById("test").onclick = function() {
        var str = document.getElementById('hamuriha').value;
        document.getElementById( "calc" ).value = str.length;
    }
</script>