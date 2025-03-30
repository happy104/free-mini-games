<?php
require_once 'functions.php';
requireLogin();

// 获取游戏数据
$games = getAllGames();
$totalGames = count($games);

// 获取分类数据
$categories = getAllCategories();
$totalCategories = count($categories);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>仪表盘 - Free Mini Games 管理后台</title>
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
                <h1>仪表盘</h1>
                <p>欢迎回来，<?php echo htmlspecialchars($_SESSION['user']['username']); ?>！</p>
            </div>
            
            <!-- 统计数据 -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="color: #4a69bd;">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <div class="stat-info">
                        <h3>游戏总数</h3>
                        <div class="stat-value"><?php echo $totalGames; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #6a89cc;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3>分类总数</h3>
                        <div class="stat-value"><?php echo $totalCategories; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #f6b93b;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3>今日访问</h3>
                        <div class="stat-value">--</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="color: #78e08f;">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="stat-info">
                        <h3>游戏点赞</h3>
                        <div class="stat-value">--</div>
                    </div>
                </div>
            </div>
            
            <!-- 快捷操作 -->
            <div class="dashboard-actions">
                <a href="add-game.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> 添加新游戏
                </a>
                <a href="manage-games.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> 管理游戏列表
                </a>
            </div>
            
            <!-- 最近添加的游戏 -->
            <div>
                <h2 style="margin-bottom: 15px;">最近添加的游戏</h2>
                
                <?php if (count($games) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>游戏名称</th>
                            <th>分类</th>
                            <th>评分</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // 显示最新添加的5个游戏
                        $recentGames = array_reverse($games);
                        $recentGames = array_slice($recentGames, 0, 5);
                        
                        foreach ($recentGames as $game): 
                            $category = getCategory($game['category']);
                            $categoryName = $category ? $category['name'] : '未分类';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($game['title']); ?></td>
                            <td><?php echo htmlspecialchars($categoryName); ?></td>
                            <td><?php echo $game['rating']; ?></td>
                            <td>
                                <a href="edit-game.php?id=<?php echo $game['id']; ?>" class="btn btn-small btn-secondary">
                                    <i class="fas fa-edit"></i> 编辑
                                </a>
                                <a href="delete-game.php?id=<?php echo $game['id']; ?>" class="btn btn-small btn-danger" onclick="return confirm('确定要删除这个游戏吗？')">
                                    <i class="fas fa-trash"></i> 删除
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="alert alert-warning">
                    还没有添加任何游戏。 <a href="add-game.php">立即添加</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>