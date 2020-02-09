<footer>
    <script src="js/vendor/jquery-2.2.2.min.js"></script>
    <script>
        $(function() {

            //入力件数の計算
            $('#js-sum').change(function() {
                //現在の累計仕訳数を取得
                var val = $(this).val();
                //前回の仕訳累計数
                <?php if(isset($result['acc_sum'])){?>
                var zen = <?php echo $result['acc_sum']; ?>;
                <?php }else{

                }?>
                //今回の仕訳数を表示
                var konkai = val - zen;
                $('#js-countview').val(konkai);
            });
            
            //お気に入り登録・削除
            var $favorite, fcompanyId;
            $favorite = $('.js-click-fov') || null;
            fcompanyId = $favorite.data('companyid') || null;
            if (fcompanyId !== undefined && fcompanyId !== null) {
                $favorite.on('click', function() {
                    var $this = $(this);
                    $.ajax({
                        type: "POST",
                        url: "ajaxfav.php",
                        data: {
                            companyid: fcompanyId
                        }
                    }).done(function(data) {
                        console.log('Ajax Success');
                        $this.toggleClass('active');
                    }).fail(function(msg) {
                        console.log('Ajax Error');
                    });
                });
            }
            
            //担当者ID取得
                $("#staffnum").on('change', function() {
            //初期化
                $('#result').html("<p></p>");
            //ajax
                var staff = $(this).val();
                console.log(staff);
                    $.ajax({
                        type: "POST",
                        url: "ajaxstaff.php",
                        dataType:'json',
                        data: {
                            staffnum: staff
                        }
                    }).done(function(data) {

                        for(var i=0; i<100; i++){

                        $('#result').append("<p class=\"staffstyle\" style=\"margin-left:30px; display:block;\">" + data[0][i].cname + "</p>");

                        }
                        console.log('Ajax Success');
                    }).fail(function(msg) {
                        console.log('Ajax Error');
                    });
                });
            
         メッセージ表示
        var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
        $jsShowMsg.slideToggle('slow');
        setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
        }
        });

        //削除の確認アラート
        $('#js-delete').on('click',function(){
            var result = window.confirm('現在表示している内容を削除します。よろしいですか?');

            if( result ) {
                console.log('OKがクリックされました');
                 var confirm = $('input[name ="deletebutton"]').attr('name','deleteon');
                console.log(confirm);
                
            }
            else {
            console.log('キャンセルがクリックされました');
                return false;
            }
        });
        
        
    </script>
</footer>
</body>

</html>
