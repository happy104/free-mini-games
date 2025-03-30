<?php
require_once 'functions.php';

// 清除会话
session_start();
$_SESSION = [];
session_destroy();

// 重定向到登录页面
header('Location: index.php');
exit;