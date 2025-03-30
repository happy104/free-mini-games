<?php
require_once 'functions.php';
requireLogin();

$success = false;
$message = '';

try {
    // 更新首页
    $homeResult = updateHomepageFeaturedGames();
    // 更新分类页
    $categoryResult = updateCategoryPage();
    
    if ($homeResult && $categoryResult) {
        $success = true;
        $message = '所有页面已成功更新！现在显示所有游戏。';
    } else {
        $message = '更新过程中出现错误。';
        if (!$homeResult) {
            $message .= ' 首页更新失败。';
        }
        if (!$categoryResult) {
            $message .= ' 分类页更新失败。';
        }
    }
} catch (Exception $e) {
    $message = '更新过程中发生错误: ' . $e->getMessage();
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
                <h1>更新所有游戏显示</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <div class="admin-section">
                <p>这个页面用于立即更新首页和分类页面，确保所有游戏都能正确显示。</p>
                
                <form method="post" action="">
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">再次更新</button>
                        <a href="dashboard.php" class="btn btn-secondary">返回仪表盘</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 