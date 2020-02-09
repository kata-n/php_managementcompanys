<?php
//共通変数・関数ファイル読み込み
require('function.php');

debug('================================');
debug('=========会社登録・編集ページ==========');
debug('================================');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面表示用データ取得
//================================
//GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから会社データを取得
$dbFormData = (!empty($p_id)) ? getCompany($p_id) : '';
// 新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false :true;
//DBから担当者データを取得
$dbstaffData = getStaff();
debug('担当者データ'.print_r($dbstaffData,true));

//================================
// パラメータ改ざんチェック
//================================
if(!empty($p_id) && empty($dbFormData)){
    debug('URLのGETパラメータが不正な為、ホームへ遷移します');
    header('Locarion:home.php'); //ホームへ
}


//================================
// POST送信時処理
//================================
if(!empty($_POST)){
    debug('POST送信があります');
    debug('POST情報'.print_r($_POST,true));
    
    //編集にユーザー情報を代入
    $cname = $_POST['cname'];
    $code = $_POST['code'];
    $tel = $_POST['tel'];
    $addr = $_POST['addr'];
    $fstate = $_POST['fstate'];
    $staff_id = $_POST['staff_id'];

    //更新の場合は、DBの情報と入力が異なる場合にバリデーションをおこなう
  if(empty($dbFormData)){
    //未入力チェック
    validRequired($cname, 'cname');
    //最大文字数チェック
    validMaxLen($cname, 'cname');
    //未入力チェック
    validRequired($code, 'code');
    validRequired($fstate, 'fstate');
    //電話番号形式チェック
    validTel($tel, 'tel');
    //最大文字数チェック
    validMaxLen($cname, 'cname', 100);
  }else{
    if($dbFormData['cname'] !== $cname){
      //未入力チェック
      validRequired($cname, 'cname');
      //最大文字数チェック
      validMaxLen($cname, 'cname');
    }
    if($dbFormData['cname'] !== $cname){
      //最大文字数チェック
      validMaxLen($cname, 'cname', 40);
    }
    if($dbFormData['tel'] != $tel){
    //電話番号形式チェック
    validTel($tel, 'tel');
    }
  }

    if(empty($err_msg)){
        debug('バリデーションOKです');
        
//        例外処理
        try{
            //DB接続
            $dbh = dbConnect();
//            SQL文を作成
//            編集画面の場合はUPDATE文、新規登録の場合はINSERT文を生成します
            if($edit_flg){
                debug('DB更新です');
                $sql = 'UPDATE product SET cname = :cname, code = :code, tel = :tel, addr = :addr, fstate = :fstate, staff_id = :staff WHERE id = :p_id';
                $data = array(':cname' => $cname, ':code' => $code, ':tel' => $tel, ':addr' => $addr, ':fstate' => $fstate, ':staff' => $staff_id, ':p_id' => $p_id);
            }else{
                debug('DB新規登録です');
                $sql = 'INSERT INTO product (cname, code, tel, addr, fstate, staff_id) values (:cname, :code, :tel, :addr, :fstate, :staff)';
                $data =array(':cname' => $cname, ':code' => $code, ':tel' =>$tel, ':addr' => $addr, ':fstate' => $fstate, ':staff' => $staff_id);
            }
            debug('SQL:'.$sql);
            debug('流し込みデータ：'.print_r($data,true));
            //クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
            
            //仕訳件数inputテーブルにコードを登録
            $sql2 = 'INSERT INTO input (code) values (:code)';
            $data2 = array(':code' => $code);
            $stmt = queryPost($dbh, $sql2, $data2);
            
            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg_success'] = SUC04;
                debug('ホームへ遷移します');
                header("Location:home.php");
            }
        } catch (Exception $e) {
            error_log('エラー発生:' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了');

?>

<?php
//画面表示
    $siteTitle = (!$edit_flg) ? '会社新規登録': '会社情報編集';
    require("head.php");
?>

<body>
    <!--ヘッダー-->
    <?php
    require('header.php');
    ?>

    <!--コンテンツ-->
    <div class="container">
        <main class="main">
            <section>
                <h2 class="heading"><?php echo (!$edit_flg) ? '会社を新規登録する' : '会社情報を更新する'; ?>
                </h2>
                <i class="fa fa-star fa-2x icn js-click-fov <?php if(favorite($dbFormData['id'])){ echo 'active'; } ?>" aria-hidden="true" data-companyid="<?php echo sanitize($dbFormData['id']); ?>"></i>
                <form action="" method="post" class="registform">
                    <div class="err_msg">
                        <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                    </div>
                    <label class="<?php if(!empty($err_msg['cname'])) echo 'err';?> label">会社名(40字以内)
                        <input type="text" name="cname" value="<?php echo getFormData('cname'); ?>">
                    </label>
                    <label class="<?php if(!empty($err_msg['code'])) echo 'err';?> label">会社コード(半角数字4ケタ)
                        <input type="text" name="code" value="<?php echo getFormData('code'); ?>">
                    </label>
                    <label class="<?php if(!empty($err_msg['addr'])) echo 'err';?> label">納税地(100字まで)
                        <input type="text" name="addr" value="<?php echo getFormData('addr'); ?>">
                    </label>
                    <label class="<?php if(!empty($err_msg['fstate'])) echo 'err';?> label">決算月
                        <input type="number" min="1" max="12" name="fstate" value="<?php echo getFormData('fstate'); ?>">月
                    </label>
                    
                    <label class="<?php if(!empty($err_msg['staff'])) echo 'err';?>">
                        担当者
                        <select name="staff_id" id="" class="staffselect">
                            <option value="0" <?php if(getFormData('staff_id') ==0){'selected';}?>>選択してください
                            </option>
                            <?php foreach($dbstaffData as $key => $val){?>
                            <option value="<?php echo $val['id']?>"<?php if(getFormData('staff_id') == $val['id']){ echo 'selected';}?>>
                            <?php echo $val['staff_name'];?>
                            </option>
                            <?php
                            }
                            ?>
                        </select>
                    </label>
                    
                    <label class="<?php if(!empty($err_msg['tel'])) echo 'err';?> label">電話番号(ハイフンなし)
                        <div class="err_msg">
                            <?php if(!empty($err_msg['tel'])) echo $err_msg['tel'];?>
                        </div>
                        <input type="text" name="tel" value="<?php echo getFormData('tel'); ?>">
                    </label>
                    <div class="btn-container">
                        <input type="submit" class="btn" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>">
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
