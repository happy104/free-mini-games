<?php
/**
 * 更新所有游戏页面脚本
 * 此脚本重新生成所有游戏页面，确保它们都使用最新的标准化模板
 */

require_once 'functions.php';
requireLogin();

// 定义时间限制
set_time_limit(300); // 5分钟

// 获取所有游戏
$games = getAllGames();
$totalGames = count($games);
$updatedGames = 0;
$errors = [];

// 日志目录处理
$logDir = ADMIN_PATH . '/logs';
$logFile = $logDir . '/update-games.log';

// 如果目录不存在，尝试创建
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0755, true)) {
        // 如果无法创建目录，使用系统临时目录
        $logFile = sys_get_temp_dir() . '/fmg_update.log';
    }
}

// 记录开始时间
$startTime = microtime(true);
logMessage("开始更新所有游戏页面。总游戏数：{$totalGames}");

// 循环处理每个游戏
foreach ($games as $game) {
    try {
        // 记录正在处理的游戏
        logMessage("正在处理游戏：{$game['title']} (ID: {$game['id']})");
        
        // 生成游戏页面
        $result = generateGamePage($game);
        
        if ($result) {
            $updatedGames++;
            logMessage("游戏页面更新成功：{$game['title']}");
        } else {
            $errors[] = "无法更新游戏：{$game['title']} (ID: {$game['id']})";
            logMessage("游戏页面更新失败：{$game['title']}", 'ERROR');
        }
    } catch (Exception $e) {
        // 捕获任何错误
        $errors[] = "处理游戏时出错：{$game['title']} - " . $e->getMessage();
        logMessage("处理游戏时出错：{$game['title']} - " . $e->getMessage(), 'ERROR');
    }
}

// 更新首页和分类页
$homeResult = updateHomepageFeaturedGames();
$categoryResult = updateCategoryPage();

// 记录结束时间
$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 2);

// 记录结果
logMessage("游戏页面更新完成。成功：{$updatedGames}，失败：" . count($errors) . "，总用时：{$executionTime}秒");
logMessage("首页更新结果：" . ($homeResult ? '成功' : '失败'));
logMessage("分类页更新结果：" . ($categoryResult ? '成功' : '失败'));

// 记录错误详情
if (!empty($errors)) {
    logMessage("错误详情：", 'ERROR');
    foreach ($errors as $error) {
        logMessage("- {$error}", 'ERROR');
    }
}

// 记录日志的函数
function logMessage($message, $level = 'INFO') {
    global $logFile;
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
    
    // 尝试写入日志
    try {
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    } catch (Exception $e) {
        // 日志写入失败，但不影响主要功能
    }
    
    // 如果是命令行模式，也输出到控制台
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

// 输出结果
$message = "所有游戏页面更新完成！";
$alert = "alert-success";

if ($updatedGames < $totalGames) {
    $message .= " 但有" . ($totalGames - $updatedGames) . "个游戏更新失败。";
    $alert = "alert-warning";
}

if (!$homeResult || !$categoryResult) {
    $message .= " 首页或分类页更新失败，请手动检查。";
    $alert = "alert-warning";
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更新所有游戏 - Free Mini Games 管理后台</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- 侧边栏 -->
        <?php include 'template/sidebar.php'; ?>
        
        <!-- 主要内容区域 -->
        <div class="admin-content">
            <div class="admin-header">
                <h1>更新所有游戏页面</h1>
            </div>
            
            <div class="alert <?php echo $alert; ?>">
                <?php echo $message; ?>
            </div>
            
            <div class="admin-card">
                <h2>更新摘要</h2>
                <p>总游戏数：<?php echo $totalGames; ?></p>
                <p>成功更新：<?php echo $updatedGames; ?></p>
                <p>失败数量：<?php echo count($errors); ?></p>
                <p>执行时间：<?php echo $executionTime; ?> 秒</p>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="admin-card error-list">
                <h2>错误详情</h2>
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <a href="dashboard.php" class="btn btn-primary">返回仪表盘</a>
                <a href="manage-games.php" class="btn">管理游戏</a>
            </div>
        </div>
    </div>
</body>
</html> 