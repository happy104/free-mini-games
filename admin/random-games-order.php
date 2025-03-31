<?php
/**
 * 随机排序游戏数据
 * 该脚本用于手动重新生成首页随机排序的游戏列表
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

// 生成结果信息
$result = '';
$success = false;

// 处理表单提交
if (isset($_POST['generate'])) {
    try {
        // 调用更新函数
        updateHomepageFeaturedGames();
        
        // 更新分类页
        updateCategoryPage();
        
        $success = true;
        $result = '游戏数据已成功随机排序！所有用户在刷新页面时都会看到新的游戏顺序。';
    } catch (Exception $e) {
        $result = '发生错误: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>随机排序游戏 - Free Mini Games</title>
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'template/sidebar.php'; ?>
        
        <div class="admin-content">
            <div class="admin-header">
                <h1>随机排序游戏数据</h1>
                <p>使用此工具可以手动重新生成随机排序的游戏列表</p>
            </div>
            
            <div class="admin-card">
                <h2>游戏随机排序</h2>
                <p>点击下方按钮随机打乱游戏顺序。这将影响首页和分类页面上游戏的显示顺序。</p>
                <p>注意：网站已设置为自动随机排序，每次游戏数据更新时（如添加新游戏、删除游戏时）都会自动随机排序。</p>
                <p>本工具用于在没有其他更新操作的情况下，手动刷新游戏排序。</p>
                
                <?php if (!empty($result)): ?>
                <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                    <?php echo $result; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group buttons-group">
                        <button type="submit" name="generate" class="btn btn-primary">
                            <i class="fas fa-random"></i> 重新随机排序游戏
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回仪表盘
                        </a>
                    </div>
                </form>
                
                <hr>
                
                <h3>前端随机排序增强</h3>
                <p>如果您发现上面的方法不够有效（游戏顺序没有变化），可以使用下面的增强功能：</p>
                <p>此选项将添加前端随机排序代码，确保每个用户每次刷新页面时都会看到不同顺序的游戏。</p>
                
                <div id="enhancement-result" style="margin: 10px 0; display: none;"></div>
                
                <button id="apply-enhancement" class="btn btn-success">
                    <i class="fas fa-magic"></i> 启用前端随机排序增强
                </button>
                
                <script>
                document.getElementById('apply-enhancement').addEventListener('click', function() {
                    const resultEl = document.getElementById('enhancement-result');
                    resultEl.style.display = 'block';
                    resultEl.innerHTML = '<div class="spinner"><i class="fas fa-spinner fa-spin"></i> 正在应用增强功能...</div>';
                    
                    fetch('apply-randomization.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                resultEl.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                            } else {
                                resultEl.innerHTML = `<div class="alert alert-danger">错误: ${data.error}</div>`;
                            }
                        })
                        .catch(error => {
                            resultEl.innerHTML = `<div class="alert alert-danger">请求失败: ${error.message}</div>`;
                        });
                });
                </script>
            </div>
        </div>
    </div>
</body>
</html> 