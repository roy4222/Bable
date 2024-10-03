<?php
session_start();
session_destroy();
session_start(); // 重新開始 session 以設置消息
$_SESSION['logout_message'] = "登出成功";
header("Location: index.php");
exit;
?>