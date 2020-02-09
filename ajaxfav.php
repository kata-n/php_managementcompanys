<?php
//共通変数・関数ファイル読み込み
require('function.php');

debug('================================');
debug('=============Ajax機能============');
debug('================================');
debugLogStart();

//================================
// Ajax処理
//================================

//postがあり、ログインしている場合
if(isset($_POST['companyid'])){
    debug('POST送信があります');
    $c_id = $_POST['companyid'];
    debug('会社ID:'.$c_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM favorite WHERE company_id = :c_id';
        $data = array(':c_id' => $c_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
            $resultCount = $stmt->rowCount();
            debug('レコード数:'.$resultCount);
            //レコードが一件でもある場合
            if(!empty($resultCount)){
                //レコードを削除する
                debug('レコード削除です');
                $sql = 'DELETE FROM favorite WHERE company_id = :c_id';
                $data = array(':c_id' => $c_id);
                //クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
            }else{
                debug('レコード追加です');
                $sql = 'INSERT INTO favorite (company_id, create_date) VALUES (:c_id, :date)';
                $data = array(':c_id' => $c_id, ':date' => date('Y-m-d H:i:s'));
//             クエリ実行
                $stmt = queryPost($dbh, $sql, $data);
            }
        } catch (Exception $e) {
            error_log('エラー発生：' . $e->getMessage());
        }
    }
debug('=============Ajaxs終了============');
?>
