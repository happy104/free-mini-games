<?php
require_once 'functions.php';
requireLogin();

// 获取游戏ID
$gameId = $_GET['id'] ?? '';

if (!empty($gameId)) {
    // 删除游戏
    $deletedGame = deleteGame($gameId);
    
    if ($deletedGame) {
        $_SESSION['message'] = "游戏 '{$deletedGame['title']}' 已成功删除";
    } else {
        $_SESSION['error'] = "未找到ID为 '{$gameId}' 的游戏";
    }
}

// 重定向回游戏列表
header('Location: manage-games.php');
exit;