<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('================================');
debug('===========ログアウト処理=============');
debug('================================');
debugLogStart();

debug('ログアウトします。');
// セッションを削除（ログアウトする）
session_destroy();
debug('ログインページへ遷移します。');
// ログインページへ
header("Location:login.php");
