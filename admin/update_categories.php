<?php
/**
 * 分类更新工具
 * 该脚本用于确保后台和前端分类保持一致
 */

require_once 'config.php';
require_once 'functions.php';

// 验证登录状态
session_start();
if (!isLoggedIn()) {
    echo '<p>请先登录后台</p>';
    echo '<p><a href="index.php">返回登录页面</a></p>';
    exit;
}

// 获取当前分类
$categories = getAllCategories();

// 检查是否需要更新分类文件
$needUpdateCategoryFile = false;

// 确保包含所有必要的分类
$requiredCategories = [
    'action' => 'Action Games',
    'puzzle' => 'Puzzle Games',
    'racing' => 'Racing Games',
    'sports' => 'Sports Games',
    'strategy' => 'Strategy Games',
    'horror' => 'Horror Games',
    'adventure' => 'Adventure Games',
    'casual' => 'Casual Games'
];

// 检查现有分类是否包含所有必要分类
$existingCategoryIds = [];
foreach ($categories as $category) {
    $existingCategoryIds[$category['id']] = $category['name'];
}

// 添加缺失的分类
foreach ($requiredCategories as $id => $name) {
    if (!isset($existingCategoryIds[$id])) {
        $categories[] = ['id' => $id, 'name' => $name];
        $needUpdateCategoryFile = true;
        echo "<p>添加缺失的分类: {$name}</p>";
    }
}

// 如果需要，更新分类文件
if ($needUpdateCategoryFile) {
    file_put_contents(CATEGORIES_DATA_FILE, json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<p>分类文件已更新</p>";
} else {
    echo "<p>分类文件已包含所有必要分类，无需更新</p>";
}

// 检查游戏模板
$templatePath = ADMIN_PATH . '/template/game-template.html';
if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);
    
    // 检查模板是否包含所有分类按钮
    $missingCategoryButtons = [];
    foreach ($requiredCategories as $id => $name) {
        if (strpos($templateContent, "category={$id}") === false) {
            $missingCategoryButtons[] = $id;
        }
    }
    
    if (!empty($missingCategoryButtons)) {
        echo "<p>警告：游戏模板中缺少以下分类按钮：" . implode(', ', $missingCategoryButtons) . "</p>";
        echo "<p>请手动更新游戏模板中的分类导航部分</p>";
    } else {
        echo "<p>游戏模板中的分类按钮是完整的</p>";
    }
} else {
    echo "<p>警告：找不到游戏模板文件</p>";
}

// 获取所有游戏并检查它们的分类
$games = getAllGames();
$gamesNeedingUpdate = [];

// 检查每个游戏的分类是否有效
foreach ($games as $game) {
    $categoryId = $game['category'] ?? '';
    if (!empty($categoryId) && !isset($existingCategoryIds[$categoryId])) {
        $gamesNeedingUpdate[] = $game['id'];
        echo "<p>警告：游戏 \"{$game['title']}\" 使用了无效的分类 \"{$categoryId}\"</p>";
    }
}

if (!empty($gamesNeedingUpdate)) {
    echo "<p>建议：更新上述游戏的分类，以确保它们显示在正确的分类下</p>";
} else {
    echo "<p>所有游戏都使用了有效的分类</p>";
}

// 显示完成信息
echo "<h2>分类同步完成</h2>";
echo "<p>现在，后台和前端都应该显示相同的8个分类：</p>";
echo "<ul>";
foreach ($requiredCategories as $id => $name) {
    echo "<li>{$name} (ID: {$id})</li>";
}
echo "</ul>";
echo "<p><a href=\"dashboard.php\">返回仪表盘</a></p>";
?> 