<?php
require_once 'functions.php';
requireLogin();

// 获取游戏数据
$games = getAllGames();

// 获取分类数据
$categories = getAllCategories();
$categoryMap = [];
foreach ($categories as $cat) {
    $categoryMap[$cat['id']] = $cat['name'];
}

// 处理搜索
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $filteredGames = [];
    foreach ($games as $game) {
        if (stripos($game['title'], $search) !== false || 
            stripos($game['description'], $search) !== false) {
            $filteredGames[] = $game;
        }
    }
    $games = $filteredGames;
}

// 处理分类筛选
$filterCategory = $_GET['category'] ?? '';
if (!empty($filterCategory)) {
    $filteredGames = [];
    foreach ($games as $game) {
        if ($game['category'] === $filterCategory) {
            $filteredGames[] = $game;
        }
    }
    $games = $filteredGames;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理游戏 - Free Mini Games 管理后台</title>
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
                <h1>管理游戏</h1>
            </div>
            
            <!-- 搜索和筛选 -->
            <div style="display:flex; gap:15px; margin-bottom:20px;">
                <form action="" method="get" style="flex:1; display:flex; gap:10px;">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="搜索游戏..." style="flex:1; padding:8px 12px; border:1px solid #ced4da; border-radius:4px;">
                    <select name="category" style="width:150px; padding:8px 12px; border:1px solid #ced4da; border-radius:4px;">
                        <option value="">所有分类</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filterCategory === $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary" style="padding:8px 15px;">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                </form>
                <a href="add-game.php" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fas fa-plus"></i> 添加游戏
                </a>
            </div>
            
            <?php if (!empty($search) || !empty($filterCategory)): ?>
            <div style="margin-bottom:15px;">
                <a href="manage-games.php" class="btn btn-secondary btn-small">
                    <i class="fas fa-times"></i> 清除筛选
                </a>
                <span style="margin-left:10px; color:#666;">
                    找到 <?php echo count($games); ?> 个结果
                </span>
            </div>
            <?php endif; ?>
            
            <?php if (count($games) > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>缩略图</th>
                        <th>游戏名称</th>
                        <th>分类</th>
                        <th>评分</th>
                        <th style="width:200px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($games as $index => $game): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <img src="../images/games/<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" style="width:60px; height:40px; object-fit:cover; border-radius:4px;">
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($game['title']); ?></strong>
                            <div style="font-size:0.85rem; color:#666; margin-top:3px;">
                                ID: <?php echo $game['id']; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($categoryMap[$game['category']] ?? '未分类'); ?></td>
                        <td><?php echo $game['rating']; ?>/5</td>
                        <td>
                            <a href="../games/<?php echo $game['id']; ?>.html" target="_blank" class="btn btn-small btn-secondary" style="margin-right:5px;">
                                <i class="fas fa-eye"></i> 查看
                            </a>
                            <a href="edit-game.php?id=<?php echo $game['id']; ?>" class="btn btn-small btn-secondary" style="margin-right:5px;">
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
                没有找到游戏。<a href="add-game.php">添加新游戏</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>