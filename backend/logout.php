<?php
//セッションを開始する
session_start();
//セッション変数を初期化する
$_SESSION = [];
//セッション名を取得する
$_session_name = session_name();
//Cookieを削除する
if(isset($_COOKIE[$session_name])){
    setcookie($session_name,'',time()-3600);
}
//セッションデータを破棄する
session_destroy();
//index.phpへリダイレクト
header('Location:index.php');
exit;
