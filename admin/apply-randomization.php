<?php
/**
 * 立即应用游戏随机排序
 * 此脚本重新生成游戏数据并立即应用随机排序
 */

require_once 'config.php';
require_once 'functions.php';

// 验证登录状态
session_start();
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '请先登录']);
    exit;
}

// 更新游戏数据
try {
    // 调用更新函数
    updateHomepageFeaturedGames();
    updateCategoryPage();
    
    // 确保PHP脚本能提供随机数据
    $jsDir = ROOT_PATH . '/js';
    if (!file_exists($jsDir . '/get-games-data.php')) {
        // 复制脚本到正确位置
        copy(__DIR__ . '/temp/get-games-data.php', $jsDir . '/get-games-data.php');
    }
    
    // 更新.htaccess文件
    $htaccessPath = ROOT_PATH . '/.htaccess';
    $htaccessContent = "";
    if (file_exists($htaccessPath)) {
        $htaccessContent = file_get_contents($htaccessPath);
    }
    
    // 如果.htaccess中没有游戏数据的缓存控制规则，添加它
    if (strpos($htaccessContent, 'games-data.json') === false) {
        $cacheControl = "
# 禁止缓存游戏数据JSON文件
<FilesMatch \"games-data\\.json$\">
    Header set Cache-Control \"no-store, no-cache, must-revalidate, max-age=0\"
    Header set Pragma \"no-cache\"
    Header set Expires \"0\"
</FilesMatch>
";
        file_put_contents($htaccessPath, $htaccessContent . $cacheControl);
    }
    
    // 返回成功信息
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => '游戏随机排序已应用，请刷新首页查看效果']);
} catch (Exception $e) {
    // 返回错误信息
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} 