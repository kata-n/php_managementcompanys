<?php
//変数初期化
//$_POST = null;

//共通変数・関数ファイル読み込み
require('function.php');
debug('================================');
debug('=========ユーザー登録=============');
debug('================================');

if(!empty($_POST)){
//    変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

//    バリデーションチェック
//    未入力
    validRequired($email,'email');
    validRequired($pass,'pass');
    validRequired($pass_re,'pass_re');

    if(empty($err_msg)){
        debug('未入力OK');
//    email形式チェック
        validEmail($email,'email');
        validmaxlen($email,'email');
        validEmailDup($email);

//        パスワードのチェック
        validHalf($pass, 'pass');
        validmaxlen($pass, 'pass');
        validminlen($pass, 'pass');

//    パスワード再入力のチェック
        validmaxlen($pass_re, 'pass_re');
        validminlen($pass_re, 'pass_re');
        
        if(empty($err_msg)){
            //パスワードと再入力が合致しているか
            validMatch($pass, $pass_re, 'pass_re');
            
            if(empty($err_msg)){
            debug('バリエーションOKです');
//                データベースへ登録
                try{
                    $dbh = dbConnect();
                    $sql = 'INSERT INTO users (email,pass, login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
                    $data = array(':email' => $email, ':pass' => password_hash($pass,PASSWORD_DEFAULT),'login_time'=>date('Y-m-d H:i:s'),'create_date' => date('Y-m-d H:i:s'));
//                    クエリ実行
                    $stmt =queryPost($dbh, $sql, $data);
                    
                    if($stmt){
                        $sesLimit = 60*60; //1時間
//                      最終ログイン日時を現在日時に
                        $_SESSION['login_date'] = time();
                        $_SESSION['login_limit'] = $sesLimit;
//                      ユーザーIDを格納
                        $_SESSION['user_id'] = $dbh->lastInsertId();
                        
                        debug('セッション変数の中身'.print_r($_SESSION,true));
                        header("Location:home.php");
                        
                    } else{
                        error_log('クエリ失敗です');
                        $err_msg['common'] = MSG07;
                    }
                } catch (Exception $e){
                    error_log('クエリ失敗しました' . $e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}

?>
<!--画面表示-->
<?php
    $siteTitle = "管理画面";
    require('head.php');
?>

<body>

    <!--画面表示-->
    <?php
    require('header.php');
?>
    <div class="container">
        <section class="newuser">
            <form action="" method="post" class="newuserform">
                <h2 class="heading">新規ユーザー登録</h2>
                <p style="font-size:80%;">初めての方はこちらからメールアドレスとパスワードの登録を行ってください。</p>
                <div class="err_msg">
                    <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                </div>
                <label class="<?php if(!empty($err_msg['email'])) echo 'err';?>">
                    <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']?>" placeholder="メールアドレス(必須)">
                </label>
                <div class="err_msg">
                    <?php if(!empty($err_msg['email'])) echo $err_msg['email'];?>
                </div>
                <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
                    <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']?>" placeholder="パスワード(必須)">
                </label>
                <div class="err_msg">
                    <?php if(!empty($err_msg['pass'])) echo $err_msg['pass'];?>
                </div>
                <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err';?>">
                    <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>" placeholder="パスワード再入力(必須)">
                </label>
                <div class="err_msg">
                    <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];?>
                </div>
                <div class="btn-container">
                    <input type="submit" class="btn" value="登録する">
                </div>
            </form>
        </section>
    </div>

    <!--フッター-->
    <?php
    require('footer.php');
?>
