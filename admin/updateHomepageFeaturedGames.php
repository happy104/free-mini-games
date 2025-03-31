<?php
require_once 'functions.php';
requireLogin();

$message = '';
$error = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (updateGamesList()) {
        $message = '首页和游戏列表页已成功更新！';
    } else {
        $error = '更新失败，请检查HTML文件是否存在或是否有适当的权限。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更新首页游戏 - Free Mini Games 管理后台</title>
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
                <h1>更新首页游戏</h1>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>说明</h3>
                <p>点击下方按钮将手动更新首页和游戏列表页面。此操作会将最新添加的游戏显示在首页。</p>
                <p>通常在添加新游戏后系统会自动更新，但若遇到问题，可通过此页面手动更新。</p>
            </div>
            
            <form method="post" action="">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">更新首页游戏</button>
                    <a href="dashboard.php" class="btn btn-secondary">返回仪表盘</a>
                </div>
            </form>
            
            <div class="admin-section">
                <h2>首页更新方式说明</h2>
                <div class="info-box">
                    <h3>自动更新</h3>
                    <p>在正常情况下，系统会在添加或编辑游戏后自动更新首页和游戏列表。</p>
                    
                    <h3>手动更新的情况</h3>
                    <ul>
                        <li>自动更新失败</li>
                        <li>首页中缺少最新添加的游戏</li>
                        <li>游戏列表页面中的游戏不完整</li>
                    </ul>
                    
                    <h3>如果更新仍然失败</h3>
                    <p>检查首页文件(index.html)是否包含以下HTML注释标记，该标记用于定位游戏区域：</p>
                    <pre>&lt;!-- 热门游戏区域 --&gt;</pre>
                    <p>如果以上方法仍不能解决问题，您可能需要手动编辑首页HTML文件。</p>
                </div>
            </div>
            
            <div class="info-box" style="margin-top: 20px;">
                <h3>强制更新（如果常规更新失败）</h3>
                <p>如果常规更新方法失败，您可以尝试使用强制更新工具：</p>
                <p><a href="force-update.php" class="btn btn-warning">强制更新网站</a></p>
                <p><small>注意：强制更新将直接重写首页和分类页的游戏列表部分。</small></p>
            </div>
        </div>
    </div>
</body>
</html> 