<?php
// 加载必要的文件
require_once 'config.php';
require_once 'functions.php';

// 获取游戏数据
$games = getAllGames();

// 重新生成每个游戏的页面
foreach ($games as $game) {
    echo "重新生成游戏: " . $game['title'] . "<br>";
    generateGamePage($game);
}

echo "所有游戏页面已重新生成!";
?> 