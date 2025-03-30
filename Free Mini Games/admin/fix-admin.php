<?php
// 加载配置和函数
require_once 'config.php';
require_once 'functions.php';

echo "<h1>管理后台修复工具</h1>";

// 步骤1：检查必要目录
echo "<h2>1. 检查目录结构</h2>";
$directories = [
    DATA_PATH => '数据目录',
    GAMES_PATH => '游戏页面目录',
    IMAGES_PATH => '游戏图片目录',
    ADMIN_PATH . '/template' => '管理后台模板目录'
];

foreach ($directories as $dir => $description) {
    if (!file_exists($dir)) {
        echo "<p>创建 {$description}: {$dir}</p>";
        mkdir($dir, 0755, true);
    } else {
        echo "<p>{$description} 已存在: {$dir}</p>";
    }
}

// 步骤2：检查数据文件
echo "<h2>2. 检查数据文件</h2>";

// 检查分类数据
if (!file_exists(CATEGORIES_DATA_FILE) || filesize(CATEGORIES_DATA_FILE) < 10) {
    echo "<p>创建或修复分类数据文件</p>";
    $defaultCategories = [
        ['id' => 'action', 'name' => '动作游戏'],
        ['id' => 'puzzle', 'name' => '益智游戏'],
        ['id' => 'racing', 'name' => '竞速游戏'],
        ['id' => 'sports', 'name' => '体育游戏'],
        ['id' => 'strategy', 'name' => '策略游戏'],
        ['id' => 'horror', 'name' => '恐怖游戏']
    ];
    file_put_contents(CATEGORIES_DATA_FILE, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    echo "<p>分类数据文件已存在，尝试验证其内容</p>";
    $categoriesData = file_get_contents(CATEGORIES_DATA_FILE);
    $categories = json_decode($categoriesData, true);
    
    if ($categories === null || !is_array($categories) || count($categories) === 0) {
        echo "<p>分类数据文件格式错误或为空，重新创建</p>";
        $defaultCategories = [
            ['id' => 'action', 'name' => '动作游戏'],
            ['id' => 'puzzle', 'name' => '益智游戏'],
            ['id' => 'racing', 'name' => '竞速游戏'],
            ['id' => 'sports', 'name' => '体育游戏'],
            ['id' => 'strategy', 'name' => '策略游戏'],
            ['id' => 'horror', 'name' => '恐怖游戏']
        ];
        file_put_contents(CATEGORIES_DATA_FILE, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        echo "<p>分类数据文件有效，包含 " . count($categories) . " 个分类</p>";
    }
}

// 检查游戏数据
if (!file_exists(GAMES_DATA_FILE) || filesize(GAMES_DATA_FILE) < 2) {
    echo "<p>创建空的游戏数据文件</p>";
    file_put_contents(GAMES_DATA_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    echo "<p>游戏数据文件已存在，尝试验证其内容</p>";
    $gamesData = file_get_contents(GAMES_DATA_FILE);
    $games = json_decode($gamesData, true);
    
    if ($games === null) {
        echo "<p>游戏数据文件格式错误，重新创建</p>";
        // 备份原始文件
        copy(GAMES_DATA_FILE, GAMES_DATA_FILE . '.bak');
        file_put_contents(GAMES_DATA_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    } else {
        echo "<p>游戏数据文件有效，包含 " . count($games) . " 个游戏</p>";
    }
}

// 检查用户数据
if (!file_exists(USERS_DATA_FILE) || filesize(USERS_DATA_FILE) < 10) {
    echo "<p>创建默认用户数据文件</p>";
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]
    ];
    file_put_contents(USERS_DATA_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
} else {
    echo "<p>用户数据文件已存在</p>";
}

// 步骤3：检查游戏模板
echo "<h2>3. 检查游戏模板</h2>";
$templatePath = ADMIN_PATH . '/template/game-template.html';
if (!file_exists($templatePath)) {
    echo "<p>创建游戏页面模板</p>";
    createGameTemplate();
} else {
    echo "<p>游戏页面模板已存在</p>";
}

// 步骤4：检查侧边栏模板
echo "<h2>4. 检查侧边栏模板</h2>";
$sidebarPath = ADMIN_PATH . '/template/sidebar.php';
if (!file_exists($sidebarPath)) {
    echo "<p>创建侧边栏模板</p>";
    $sidebarContent = <<<'HTML'
<div class="admin-sidebar">
    <div class="admin-sidebar-header">
        <h2>Free Mini Games</h2>
        <p>管理后台</p>
    </div>
    
    <nav class="admin-sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tachometer-alt"></i> 仪表盘
                </a>
            </li>
            <li>
                <a href="add-game.php" <?php echo basename($_SERVER['PHP_SELF']) === 'add-game.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-plus-circle"></i> 添加游戏
                </a>
            </li>
            <li>
                <a href="manage-games.php" <?php echo basename($_SERVER['PHP_SELF']) === 'manage-games.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-gamepad"></i> 管理游戏
                </a>
            </li>
            <li>
                <a href="categories.php" <?php echo basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-tags"></i> 管理分类
                </a>
            </li>
            <li>
                <a href="settings.php" <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> 设置
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="admin-sidebar-footer">
        <a href="logout.php" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i> 退出登录
        </a>
    </div>
</div>
HTML;
    file_put_contents($sidebarPath, $sidebarContent);
} else {
    echo "<p>侧边栏模板已存在</p>";
}

// 步骤5：重建游戏页面
echo "<h2>5. 重建游戏页面</h2>";
$games = getAllGames();
if (count($games) > 0) {
    echo "<p>开始重建 " . count($games) . " 个游戏页面：</p>";
    echo "<ul>";
    foreach ($games as $game) {
        echo "<li>重建：" . htmlspecialchars($game['title']) . " (ID: " . $game['id'] . ")</li>";
        generateGamePage($game);
    }
    echo "</ul>";
    echo "<p>所有游戏页面已重建完成</p>";
} else {
    echo "<p>没有游戏数据需要重建</p>";
}

// 步骤6：更新首页和分类页
echo "<h2>6. 更新首页和分类页</h2>";
echo "<p>尝试更新首页热门游戏区域</p>";
if (updateHomepageFeaturedGames()) {
    echo "<p>首页热门游戏区域更新成功</p>";
} else {
    echo "<p>首页热门游戏区域更新失败，可能是首页结构有问题</p>";
}

echo "<p>尝试更新游戏分类页</p>";
if (updateCategoryPage()) {
    echo "<p>游戏分类页更新成功</p>";
} else {
    echo "<p>游戏分类页更新失败，可能是分类页结构有问题</p>";
}

// 完成
echo "<h2>修复完成</h2>";
echo "<p>管理后台修复工具已完成所有操作。现在您应该可以正常使用管理后台了。</p>";
echo "<div style='margin-top: 20px;'>";
echo "<a href='dashboard.php' style='display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>进入管理后台</a>";
echo "<a href='../index.html' style='display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>访问网站首页</a>";
echo "</div>";
?> 