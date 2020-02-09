<?php
//ログ
ini_set('log_errors','on');
//ログの出力ファイル先
ini_set('error_log','php.log');

//================================
// セッション
//================================
//セッションファイルの置き場所を変更する
session_save_path("/var/tmp");
//ガーベージコレクションが消去するファイル日時を変更
ini_set('session.gc_maxlifetime',60*60*24*30);
//ブラウザを閉じても削除されない様にクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime',60*10); //10分
//セッションを使う
session_start();
//現在のセッションIDを新しく生成したものと置き換える
session_regenerate_id();

//================================
// デバッグ
//================================
//デバッグフラグ
$debug_flg = false;
//デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//画面表示処理開始ログ掃き出し関数
function debugLogStart(){
    debug('================================');
    debug('セッションID'.session_id());
    debug('セッション変数の中身'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプは'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}

//================================
// 定数
//================================
//エラーメッセージを定数に設定
define('MSG01','入力必須です');
define('MSG02','emailの形式で入力してください');
define('MSG03','パスワードと再入力が一致しません');
define('MSG04','半角英数字のみで入力してください');
define('MSG05','パスワードは六文字以上で入力してください');
define('MSG06','255文字以内で入力してください');
define('MSG07','システムエラーが発生しました。');
define('MSG08','既に登録されたEmailの為、登録できません。');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','電話番号の形式が違います');
define('MSG11','数字以外は入力できません');
define('MSG12','入力されたコードは登録されていません');
define('SUC04','更新を行いました。');
define('SUC05','仕訳数を登録しました！！');

//================================
// グローバル変数
//================================
//エラーメッセージ格納用の配列
$err_msg = array();

//================================
// バリデーション関数
//================================
//エラーメッセージを配列に格納

//未入力
function validRequired($str, $key){
    if($str === ''){
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}

//emailの形式
function validEmail($str, $key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}

//最大文字数チェック
function validmaxlen($str, $key, $max = 256){
    if(mb_strlen($str) > $max){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}

//最小文字数チェック
function validminlen($str, $key, $min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    }
}

//数字のみのチェック
function validint($str, $key){
    if(!ctype_digit($str)){
        global $err_msg;
        $err_msg[$key] = MSG11;
    }
}

//半角チェック
function validHalf($str, $key){
    if(!preg_match("/^[a-zA-Z0-9]+$/", $key)){
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}

//フォーム再入力のチェック
function validMatch($str1, $str2, $key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}

//emailの重複チェック
function validEmailDup($email){
    global $err_msg;
//    例外処理
    try {
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
//     クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
//     クエリ実行結果の値を取得する
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty(array_shift($result))){
            $err_msg['common'] = MSG08;
        }
    } catch (Exception $e){
        error_log('エラー発生：'. $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}

//電話番号形式チェック
function validTel($str,$key){
    if(!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG10;
    }
}
//================================
// DB関係
//================================
//DB接続関数
function dbConnect(){
//    データベースへ接続準備
    $dbn = 'mysql:dbname=***********;host=*************;charset=utf8';
    $user = '*************';
    $password = '**************';
    $options = array(
    // SQL実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
//PDOオブジェクト作成
    $dbh = new PDO($dbn, $user, $password, $options);
    return $dbh;
}

//SQL実行関数
    function queryPost($dbh,$sql, $data){
        //クエリー作成
        $stmt = $dbh->prepare($sql);
        if(!$stmt->execute($data)){
            debug('クエリ失敗');
            $err_msg['common'] = MSG07;
            return false;
        }
        debug('クエリ成功');
        return $stmt;
    }

//会社情報の取得
function getCompany($p_id){
    debug('会社情報を取得します');
    debug('会社ID:'.$p_id);
    //例外処理
    try {
//      DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM product WHERE id = :p_id';
        $data = array(':p_id' => $p_id);
//      クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if($stmt){
      // クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：'. $e->getMessage());
    }
}

//登録したすべての会社情報を取得
function companyDataAll(){
    debug('登録されたすべての会社情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM product';
        $data = array();
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //クエリ結果のデータを全レコード返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生：'. $e->getMessage());
    }
}

//決算月のデータ取得
//PHPから決算月を取得
$fstate = date('m', strtotime('-2 month'));
$f_id = ((int)$fstate);

function companyFstate($f_id){
    debug('決算申告月の会社情報を取得します');
    try {
        $dbh = dbConnect();
        $sql = 'SELECT * FROM product WHERE fstate = :f_id';
        $data = array(':f_id' => $f_id);
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //クエリ結果のデータを全レコード返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生：'. $e->getMessage());
    }
}

function favorite($p_id){
    debug('お気に入り情報があるか確認します');
    debug('会社ID:'.$p_id);
//    例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM favorite WHERE company_id = :p_id';
        $data = array('p_id' => $p_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt->rowCount()){
            debug('お気に入りです');
            return true;
        }else{
            debug('時に気に入っていません');
            return false;
        }
    } catch (Exception $e){
        error_log('エラー発生:' . $e->getMessage());
    }
}

function getfavo(){
    debug('お気に入り情報を取得します');
//    例外処理
    try {
        //DBへ接続
        $dbh = dbConnect();
        //SQL接続
        $sql = 'SELECT * FROM favorite AS f LEFT JOIN product AS p ON f.company_id = p.id';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql,$data);
        
        if($stmt){
            //クエリ結果の全データを返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}

//担当者データ取得
function getStaff(){
    debug('担当者データを取得します');
    try{
        //DB接続
        $dbh = dbConnect();
        //SQL
        $sql = "SELECT * FROM staff WHERE delete_flg = 0";
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //取得したデータ全返却
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e){
        error_log('エラー発生'. $e->getMessage());
    }
}

//================================
// GETパラメータ付与
//================================
function appendGetParam($arr_del_key =array()){
    if(!empty($_GET)){
        $str = '?';
        foreach($_GET as $key => $val){
            if(!in_array($key,$arr_del_key,true)){
            $str .= $key.'=' .$val.'&';
        }
    }
    $str = mb_substr($str, 0 , -1, "UTF-8");
    return $str;
    }
}

//================================
// その他
//================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}

//フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
//    ユーザーデータが場合
    if(!empty($dbFormData)){
    //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
      //POSTにデータがある場合
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }
            }else{
//      POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
            return sanitize($method[$str]);
            }else{
            return sanitize($dbFormData[$str]);
            }
            }
            }else{
             if(isset($method[$str])){
             return sanitize($method[$str]);
             }
    }
}

//session一回だけ取得する
function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}
