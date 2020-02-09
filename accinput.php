<?php
//共通変数・関数ファイル読み込み
require('function.php');

debug('===============================');
debug('=========仕訳数入力ページ========');
debug('===============================');
debugLogStart();


//================================
// 画面処理
//================================
//検索ボタンが押された場合
if(isset($_POST['serch'])){
 debug('検索のPOST送信があります');

//変数に情報を代入
    $code = $_POST['code'];

//バリデーション
    validRequired($code, 'code');
    validint($code, 'code');
    
    if(empty($err_msg)){
        debug('バリデーションOKです');
        debug('入力されたコードから会社情報を取得します');
        //例外処理
        try {
        //DB接続
        $dbh = dbConnect();
        //SQL作成
        $sql = 'SELECT p.id, p.cname, i.acc_sum, i.acc_input, i.acc_now, i.update_date FROM input As i INNER JOIN product As p ON i.code = p.code WHERE p.code = :code ORDER BY i.update_date DESC LIMIT 1';
        $data = array(':code' => $code);
        debug('SQL：'.$sql);
        debug('流し込みデータ：'.print_r($data,true));
        // クエリ実行
        $stmt = querypost($dbh, $sql, $data);
        // クエリ成功の場合
        if($stmt){
        // クエリ結果のデータを１レコード返却
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('クエリ結果の中身'.print_r($result,true));
        }
            if(empty($result)){
                debug('該当コードがありません');
                $err_msg['common'] = MSG12;
            }
    }catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
 }
}

if(isset($_POST['inputbutton'])){
    debug('登録ボタンが押されPOST送信があります');
    
    //変数に情報を代入
    $code = $_POST['code'];
    $acc_now = $_POST['acc_now']; //現在の累計の仕訳件数
    $acc_input = $_POST['acc_input']; //今回の仕訳入力数
    
    debug('入力された会社コード：'.$code);

    //バリデーション
    validRequired($code, 'code');
    validint($code, 'code');
    
    if(empty($err_msg)){
        debug('バリデーションOKです');
        debug('仕訳情報をインサートします');
        try{
            $dbh = dbConnect();
            $sql = 'INSERT INTO input (code, acc_sum, acc_input, acc_now, update_date) VALUES(:code, :a_s, :a_i, :a_n, :date)';
            $data = array(':code' => $code, ':a_s' => $acc_now, ':a_i' => $acc_input, ':a_n' => $acc_now, ':date' => date('Y-m-d H:i:s'));
//            クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            
            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg_success'] = SUC05;
                debug('ホームへ遷移します');
                header("Location:home.php");
            }
        }catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
}
}
}

if(isset($_POST['deleteon'])){
    debug('仕訳データを削除します');
    
    //変数に情報を代入
    $code = $_POST['code'];
    
        try {
        //DB接続
        $dbh = dbConnect();
        //SQL作成
        $sql = 'DELETE FROM input WHERE input.code = :code ORDER BY input.update_date DESC LIMIT 1';
        $data = array(':code' => $code);
        debug('SQL：'.$sql);
        debug('流し込みデータ：'.print_r($data,true));
        // クエリ実行
        $stmt = querypost($dbh, $sql, $data);
        // クエリ成功の場合
        if($stmt){
        // クエリ結果のデータを１レコード返却
//        $result = $stmt->fetch(PDO::FETCH_ASSOC);
//        debug('クエリ結果の中身'.print_r($result,true));
        }
            if(!empty($result)){
                debug('該当コードがありません');
                $err_msg['common'] = MSG12;
        }
    }catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
}
}

debug('仕訳入力ページ処理終了');
?>

<?php
//ヘッダ
$siteTitle = '仕訳入力数登録画面';
require('head.php');
?>

<body>

    <?php
    require('header.php');
?>

<!--メッセージを表示-->
   <p id="js-show-msg" style="display:none;" class="msg-bord">
       <?php echo getSessionFlash('msg_success'); ?>
   </p>
   
    <div class="container">
        <main class="main">
            <section>
                <h2 class="heading">仕訳数を登録する</h2>
                <div class="err_msg">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <form action="" method="POST">
                    <label class="<?php if(!empty($err_msg['code'])) echo 'err';?> label">会社コード(4ケタ)を入力してください
                        <input type="text" name="code" value="<?php if(!empty($_POST['code'])) echo $_POST['code']?>" class="incode">
                    </label>
                    <div class="btn-container">
                        <input type="submit" class="btn" value="検索" name="serch">
                    </div>
                    <label class="leslabel"><?php if(!empty($result['cname'])) echo '会社名：'.$result['cname']?>
                    </label>
                    <label class="leslabel"><?php if(!empty($result['update_date'])) echo '前回仕訳件数登録日：'.$result['update_date']?>
                    </label>
                    <label class="leslabel"><?php if(!empty($result['acc_input'])) echo '前回登録した仕訳件数：'.$result['acc_input']?>
                    </label>
                    <label class="leslabel"><?php if(!empty($result['acc_sum'])) echo '現在の累計仕訳件数：'.$result['acc_sum']?>
                    </label>

                    <div class="btn-container">
                       <?php if(empty($result)) $class="hide ";?>
                        <input type="submit" id="js-delete" class="btn <?php if(isset($class)) echo $class; ?>" value="訂正(削除)する" name="deletebutton" style="margin-top: 30px;">
                    </div>
                    
                    <label class="leslabel err_msg"><?php if(!empty($result['cname'])) echo '新しく入力した分を含めた累計仕訳数を入力してください';?>
                        <?php if(empty($result)) $class="hide ";?>
                        <input type="text" id="js-sum" name="acc_now" class="<?php if(isset($class)) echo $class; ?>">
                    </label>
                    <label class="leslabel"><?php if(!empty($result['cname'])) echo '今回の仕訳件数：'//.$result['acc_input']?>
                    </label>
                    <label class="leslabel">
                        <?php if(empty($result)) $class="hide ";?>
                        <input type="text" name="acc_input" value="" class="<?php if(isset($class)) echo $class; ?>" id="js-countview">
                    </label>
                    
                    <div class="btn-container">
                        <input type="submit" class="btn <?php if(isset($class)) echo $class; ?>" value="登録" name="inputbutton">
                    </div>

                </form>
            </section>
        </main>
        <!--サイドバー-->
        <?php
        require('sidebar.php');
        ?>
    </div>
    <!--フッター-->
    <?php
    require('footer.php');
    ?>
