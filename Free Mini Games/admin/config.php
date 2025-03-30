<?php
// 基本设置
define('SITE_TITLE', 'Free Mini Games');
define('SITE_URL', '/Free Mini Games'); // 根据您的实际路径修改

// 文件路径设置
define('ADMIN_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));
define('GAMES_PATH', ROOT_PATH . '/games');
define('IMAGES_PATH', ROOT_PATH . '/images/games');

// 数据文件
define('DATA_PATH', ADMIN_PATH . '/data');
define('GAMES_DATA_FILE', DATA_PATH . '/games.json');
define('CATEGORIES_DATA_FILE', DATA_PATH . '/categories.json');
define('USERS_DATA_FILE', DATA_PATH . '/users.json');

// 创建必要的目录
if (!file_exists(DATA_PATH)) {
    mkdir(DATA_PATH, 0755, true);
}
if (!file_exists(GAMES_PATH)) {
    mkdir(GAMES_PATH, 0755, true);
}
if (!file_exists(IMAGES_PATH)) {
    mkdir(IMAGES_PATH, 0755, true);
}

// 初始化分类数据
if (!file_exists(CATEGORIES_DATA_FILE)) {
    $defaultCategories = [
        ['id' => 'action', 'name' => '动作游戏'],
        ['id' => 'puzzle', 'name' => '益智游戏'],
        ['id' => 'racing', 'name' => '竞速游戏'],
        ['id' => 'sports', 'name' => '体育游戏'],
        ['id' => 'strategy', 'name' => '策略游戏'],
        ['id' => 'horror', 'name' => '恐怖游戏']
    ];
    file_put_contents(CATEGORIES_DATA_FILE, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 初始化游戏数据
if (!file_exists(GAMES_DATA_FILE)) {
    file_put_contents(GAMES_DATA_FILE, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 初始化用户数据（默认账号：admin/admin123）
if (!file_exists(USERS_DATA_FILE)) {
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]
    ];
    file_put_contents(USERS_DATA_FILE, json_encode($defaultUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// 开启会话
session_start();