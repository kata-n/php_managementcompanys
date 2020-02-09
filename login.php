<?php

//共通変数・関数ファイル読み込み
require('function.php');

debug('================================');
debug('=========ログインページ===========');
debug('================================');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// ログイン画面処理
//================================
// post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります');
    
    //変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass= $_POST['pass'];
    $pass_save = (!empty($_POST['pass_save'])) ? true : false; //ショートハンド
    
    //emailの形式チェック
    validEmail($email, 'email');
    //emailの最大文字数チェック
    validMaxLen($email, 'email');
    //パスワードの半角英数字チェック
    validHalf($pass, 'pass');
    //パスワードの最大文字数チェック
    validMaxLen($pass, 'pass');
    //パスワードの最小文字数チェック
    validMinLen($pass, 'pass');
    //未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    
    if(empty($err_msg)){
        debug('バリデーションＯＫです');
        debug('SQL接続します');
        try{
            $dbh = dbConnect();
            $sql ='SELECT pass,id FROM users WHERE email = :email AND delete_flg = 0';
            $data = array(':email' => $email);
//            クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
//            クエリ結果の値を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            debug('クエリ結果の中身'.print_r($result,true));
            
            //パスワードの照合
            if(!empty($result) && password_verify($pass,array_shift($result))){
                debug('パスワードあっています');
                debug('ログイン有効期限を伸ばします');
                
//                ログイン有効期限
                $sesLimit = 60*60;
                //最終ログイン日時を現在日時に
                $_SESSION['login_date'] = time();
                
//                ログイン保持にチェックがある場合
                if($pass_save){
                    debug('ログイン保持にチェックがあります');
                    //ログイン有効期限を30日にしてセット
                    $_SESSION['login_limit'] = $sesLimit * 24 * 30;
                }else{
                    debug('ログイン保持にチェックありません');
//                    次回からログイン保持しないので、ログイン有効期限を1時間にセット
                    $_SESSION['login_limit'] = $sesLimit;
                }
//                ユーザーＩＤを格納
                $_SESSION['user_id'] = $result['id'];
                
                debug('ユーザーＩＤ'.print_r($_SESSION['user_id']));
                debug('セッション変数の中身'.print_r($_SESSION,true));
                debug('ホームへ遷移します');
                header("Location:home.php");
            }else{
                debug('パスワードが一致しません');
                $err_msg['common'] = MSG09;
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('ログイン処理終了');
?>
<?php
$siteTitle = 'ログイン';
require('head.php');
?>

<body>
    <?php
    require('header.php');
    ?>

    <div class="container">
        <section class="newuser">
            <form action="" method="post" class="loginform">
                <h2 class="heading">ログインする</h2>
                <div class="err_msg">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <div class="err_msg">
                    <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?>
                </div>
                <div class="err_msg">
                    <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
                </div>
                <label class="<?php if(!empty($err_msg['email'])) echo 'err';?> label">メールアドレス
                    <input type="text" name="email" value="system-tesuto@gmail.com" placeholder="メールアドレス（必須）">
                </label>
                <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">パスワード
                    <input type="password" name="pass" value="systemtesuto" placeholder="パスワード（必須）">
                </label>

                <label style="margin-bottom: 35px;">
                    <input type="checkbox" name="pass_save">次回ログインを省略する
                </label>
                <div class="btn-container">
                    <input type="submit" class="btn" value="ログイン">
                </div>
                <p>入力欄が空の場合は、お手数ですが下記項目を入力して下さい。</p>
                <p style="font-size:80%;">メールアドレス：system-tesuto@gmail.com</p>
                <p style="font-size:80%;">パスワード：systemtesuto</p>
                <p style="font-weight:bold;">※このページは学習公開用です。登録されている会社名等は全て架空です。</p>
            </form>
        </section>
    </div>

    <!--フッター-->
    <?php
    require('footer.php');
?>
