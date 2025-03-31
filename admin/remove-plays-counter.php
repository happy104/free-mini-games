<?php
/**
 * 批量移除游戏播放量计数器
 * 该脚本用于从所有游戏页面中移除"0 Plays"计数器
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

// 设置执行时间限制
set_time_limit(300); // 5分钟

// 获取所有游戏文件
$gameFiles = glob(GAMES_PATH . '/*.html');
$totalGames = count($gameFiles);
$updatedGames = 0;
$errors = [];

// 创建日志目录
$logDir = ADMIN_PATH . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// 创建日志文件
$logFile = $logDir . '/remove-plays-counter-' . date('Y-m-d-H-i-s') . '.log';
file_put_contents($logFile, "开始移除游戏播放量计数器: " . date('Y-m-d H:i:s') . "\n");

// 处理每个游戏文件
foreach ($gameFiles as $gameFile) {
    $gameName = basename($gameFile, '.html');
    
    try {
        // 读取游戏页面内容
        $content = file_get_contents($gameFile);
        
        // 检查是否包含播放量计数器
        if (strpos($content, '<span class="game-plays">') !== false) {
            // 使用正则表达式移除播放量计数器
            $pattern = '/<span class="game-plays">.*?<\/span>/s';
            $newContent = preg_replace($pattern, '', $content);
            
            // 保存修改后的内容
            file_put_contents($gameFile, $newContent);
            
            // 记录日志
            file_put_contents($logFile, "已更新: {$gameName}.html\n", FILE_APPEND);
            $updatedGames++;
        } else {
            // 记录日志
            file_put_contents($logFile, "无需更新: {$gameName}.html (未找到播放量计数器)\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        // 记录错误
        $errors[] = "{$gameName}: " . $e->getMessage();
        file_put_contents($logFile, "错误: {$gameName}.html - " . $e->getMessage() . "\n", FILE_APPEND);
    }
}

// 记录完成信息
$endTime = date('Y-m-d H:i:s');
file_put_contents($logFile, "更新完成: {$endTime}\n", FILE_APPEND);
file_put_contents($logFile, "总计游戏: {$totalGames}\n", FILE_APPEND);
file_put_contents($logFile, "已更新: {$updatedGames}\n", FILE_APPEND);
file_put_contents($logFile, "错误: " . count($errors) . "\n", FILE_APPEND);

// 输出结果HTML
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>移除游戏播放量计数器 - Free Mini Games</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'template/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>移除游戏播放量计数器</h1>
            </div>
            
            <div class="result-summary">
                <h2>更新结果</h2>
                <p>更新开始时间: <?php echo date('Y-m-d H:i:s', filectime($logFile)); ?></p>
                <p>更新完成时间: <?php echo $endTime; ?></p>
                
                <div class="stats-container">
                    <div class="stat-item">
                        <strong>总计游戏:</strong> <?php echo $totalGames; ?>
                    </div>
                    <div class="stat-item">
                        <strong>已更新:</strong> <?php echo $updatedGames; ?>
                    </div>
                    <div class="stat-item">
                        <strong>错误数:</strong> <?php echo count($errors); ?>
                    </div>
                </div>
                
                <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <h3>错误详情</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-primary">返回仪表盘</a>
                    <a href="<?php echo str_replace(ADMIN_PATH, '', $logFile); ?>" class="btn btn-secondary" target="_blank">查看完整日志</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 