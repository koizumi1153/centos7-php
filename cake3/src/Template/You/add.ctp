
<?= $this -> Form -> create (
                "null", [ "type" => "post",
                          "url" => [ "controller" => "you",
                                     "action" => "add" ] ] ); ?>


<?= $this -> Form -> input ( "textbox", [ "type" => "text",
                                              "name" => "title",
                                              "size" => 250,
                                              "label" => "タイトル",
                                              "default" => "タイトル" ] ); ?>

<?= $this -> Form -> input ( "area", [ "type" => "textarea",
                                            "cols" => 20,
                                            "rows" => 3,
                                            "label" => "説明",
                                            "name" => "discription" ] ); ?>

<?= $this -> Form -> input  ( "select4",
                                     [ "type" => "select",
                                       "name" => "kind",
                                       "options" => [ [ "value" => AQOURS_KIND_BOOK,
                                                        "text" => "本&雑誌の情報 " ],
                                                      [ "value" => AQOURS_KIND_CD,
                                                        "text" => "CDの情報", ],
                                                      [ "value" => AQOURS_KIND_CD,
                                                        "text" => "DVD & Blu-rayの情報", ],
                                                      [ "value" => AQOURS_KIND_EVENT,
                                                        "text" => "イベント情報", ],
                                                      [ "value" => AQOURS_KIND_TV,
                                                        "text" => "TV出演情報", ],
                                                      [ "value" => AQOURS_KIND_RADIO,
                                                        "text" => "ラジオ・ネットラジオ・ニコ生などの情報",
                                                        "selected" => true ],
                                                      [ "value" => AQOURS_KIND_TICKET,
                                                        "text" => "チケットの情報", ],
                                                      [ "value" => AQOURS_KIND_GOODS,
                                                        "text" => "グッズの情報", ],
                                                        ]]
                                        ); ?>

<label for="textbox">日付</label>
<input type="text" name="date" id="datepicker">

<input type="submit" value="送信">

<?= $this -> Form -> end (); ?>
<script>
  $(function () {
    $('#datepicker').datepicker({
        monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        dateFormat: 'yy年mm月dd日',
        firstDay: 1,
        showOtherMonths:true,

    });
  });
</script>