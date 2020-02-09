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
if(isset($_POST['staffnum'])){
    debug('POST送信があります');
    $s_id = $_POST['staffnum'];
    debug('担当者ID:'.$s_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM product WHERE staff_id = :s_id';
        $data = array(':s_id' => $s_id);
        $stmt = queryPost($dbh, $sql, $data);

        //クエリ実行
        if($stmt){
        //クエリ結果のデータを全レコード返却
            $select_result = array();
            while($row = $stmt->fetchAll()){
                $select_result[] = $row;
                debug('取得したデータ'.print_r($select_result,true));
            }
                header('Content-Type: application/json');
                echo json_encode($select_result,true);
//                echo json_decode($row,true);
                exit;
            
        }else{
            return false;
        }
    }catch (Exception $e){
        error_log('エラー発生：'. $e->getMessage());
    }
}

debug('=============Ajaxs終了============');

?>
