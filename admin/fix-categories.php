<?php
// 加载配置
require_once 'config.php';

echo "<h1>分类修复工具</h1>";

// 检查分类文件是否存在
if (!file_exists(CATEGORIES_DATA_FILE)) {
    echo "<p>分类文件不存在，正在创建...</p>";
    
    // 创建默认分类
    $defaultCategories = [
        ['id' => 'action', 'name' => '动作游戏'],
        ['id' => 'puzzle', 'name' => '益智游戏'],
        ['id' => 'racing', 'name' => '竞速游戏'],
        ['id' => 'sports', 'name' => '体育游戏'],
        ['id' => 'strategy', 'name' => '策略游戏'],
        ['id' => 'horror', 'name' => '恐怖游戏']
    ];
    
    // 确保目录存在
    if (!file_exists(DATA_PATH)) {
        mkdir(DATA_PATH, 0755, true);
        echo "<p>创建了数据目录：" . DATA_PATH . "</p>";
    }
    
    // 写入分类文件
    file_put_contents(CATEGORIES_DATA_FILE, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<p>已创建分类文件，包含 " . count($defaultCategories) . " 个分类</p>";
} else {
    echo "<p>分类文件已存在，正在检查内容...</p>";
    
    // 读取分类文件
    $data = file_get_contents(CATEGORIES_DATA_FILE);
    $categories = json_decode($data, true);
    
    if ($categories === null || !is_array($categories) || empty($categories)) {
        echo "<p>分类文件格式错误或为空，正在重新创建...</p>";
        
        // 创建默认分类
        $defaultCategories = [
            ['id' => 'action', 'name' => '动作游戏'],
            ['id' => 'puzzle', 'name' => '益智游戏'],
            ['id' => 'racing', 'name' => '竞速游戏'],
            ['id' => 'sports', 'name' => '体育游戏'],
            ['id' => 'strategy', 'name' => '策略游戏'],
            ['id' => 'horror', 'name' => '恐怖游戏']
        ];
        
        // 写入分类文件
        file_put_contents(CATEGORIES_DATA_FILE, json_encode($defaultCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "<p>已重新创建分类文件，包含 " . count($defaultCategories) . " 个分类</p>";
        
        $categories = $defaultCategories;
    } else {
        echo "<p>分类文件正常，包含 " . count($categories) . " 个分类</p>";
    }
    
    // 显示当前分类
    echo "<h2>当前分类列表：</h2>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: <strong>" . $category['id'] . "</strong> - 名称: <strong>" . $category['name'] . "</strong></li>";
    }
    echo "</ul>";
}

// 修复函数文件中可能存在的问题
echo "<h2>修复getAllCategories函数</h2>";

$functionsFile = ADMIN_PATH . '/functions.php';
if (file_exists($functionsFile)) {
    $functionsContent = file_get_contents($functionsFile);
    
    // 检查并修复getAllCategories函数
    if (strpos($functionsContent, 'function getAllCategories()') !== false) {
        echo "<p>函数文件中已找到getAllCategories函数，正在检查...</p>";
        
        // 创建改进版函数
        $improvedFunction = <<<'EOD'
// 获取所有分类
function getAllCategories() {
    if (file_exists(CATEGORIES_DATA_FILE)) {
        $data = file_get_contents(CATEGORIES_DATA_FILE);
        $categories = json_decode($data, true);
        
        // 检查解析结果
        if ($categories === null || !is_array($categories)) {
            // JSON解析失败或结果不是数组，返回默认分类
            return [
                ['id' => 'action', 'name' => '动作游戏'],
                ['id' => 'puzzle', 'name' => '益智游戏'],
                ['id' => 'racing', 'name' => '竞速游戏'],
                ['id' => 'sports', 'name' => '体育游戏'],
                ['id' => 'strategy', 'name' => '策略游戏'],
                ['id' => 'horror', 'name' => '恐怖游戏']
            ];
        }
        
        return $categories;
    }
    
    // 文件不存在，返回默认分类
    return [
        ['id' => 'action', 'name' => '动作游戏'],
        ['id' => 'puzzle', 'name' => '益智游戏'],
        ['id' => 'racing', 'name' => '竞速游戏'],
        ['id' => 'sports', 'name' => '体育游戏'],
        ['id' => 'strategy', 'name' => '策略游戏'],
        ['id' => 'horror', 'name' => '恐怖游戏']
    ];
}
EOD;

        // 用正则表达式替换原函数
        $pattern = '/\/\/ 获取所有分类\s*function getAllCategories\(\) \{[^}]*\}/s';
        if (preg_match($pattern, $functionsContent)) {
            $newFunctionsContent = preg_replace($pattern, $improvedFunction, $functionsContent);
            
            // 保存修改后的函数文件
            file_put_contents($functionsFile, $newFunctionsContent);
            echo "<p>已更新getAllCategories函数，使其更加健壮</p>";
        } else {
            echo "<p>无法匹配函数模式，请手动检查函数文件</p>";
        }
    } else {
        echo "<p>未在函数文件中找到getAllCategories函数，这是一个严重问题</p>";
    }
} else {
    echo "<p>找不到函数文件：" . $functionsFile . "</p>";
}

echo "<h2>修复完成</h2>";
echo "<p>请刷新游戏管理页面，测试分类选择功能是否正常工作</p>";
echo "<p><a href='manage-games.php'>返回管理游戏页面</a></p>";
?> 