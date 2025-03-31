<?php
// 直接包含functions.php文件
require_once 'functions.php';

// 设置错误报告
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>强制更新网站页面</h2>";

echo "<h3>1. 更新首页游戏列表</h3>";
$result1 = updateHomepageFeaturedGames();
if ($result1) {
    echo "<p style='color:green'>首页更新成功!</p>";
} else {
    echo "<p style='color:red'>首页更新失败，请检查错误信息。</p>";
}

echo "<h3>2. 更新游戏分类页面</h3>";
$result2 = updateCategoryPage();
if ($result2) {
    echo "<p style='color:green'>游戏分类页更新成功!</p>";
} else {
    echo "<p style='color:red'>游戏分类页更新失败，请检查错误信息。</p>";
}

if ($result1 && $result2) {
    echo "<h3 style='color:green'>所有页面更新成功!</h3>";
    echo "<p>新添加的游戏现在应该已经显示在首页和游戏分类页面上了。</p>";
} else {
    echo "<h3 style='color:red'>部分更新失败!</h3>";
    echo "<p>请检查错误信息，并确保HTML文件存在且可写入。</p>";
}

echo "<p><a href='dashboard.php'>返回管理后台</a></p>";
?> 