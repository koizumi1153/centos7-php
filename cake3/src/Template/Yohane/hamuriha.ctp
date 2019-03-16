<form method="post" action="">
文字数<br />
<input type="text" id="calc" value="<?php echo isset($len) ? $len : 0; ?>" disabled><br />
<br />
<?= $this -> Form -> input ( "本文", [ "type" => "textarea",
                                              "name" => "body",
                                              "rows" => '5',
                                              "cols" => "5",
                                              "id"=>"hamuriha",
                                              "default" => "" ] ); ?>
<br />
<br />

<button id="test">簡易計測</button>
<input type="submit" value="詳細計測">
</form>
<script>
    document.getElementById("test").onclick = function() {
        var str = document.getElementById('hamuriha').value;
        str.replace(/\r?\n\s/g, '');
        document.getElementById( "calc" ).value = str.length;
    }
</script>