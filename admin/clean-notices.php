<?php
// 批量移除游戏页面中的"游戏进度不会保存"提示

// 获取游戏目录
$gamesDir = __DIR__ . '/../games/';

// 获取所有HTML文件
$gameFiles = glob($gamesDir . '*.html');
$noticeCount = 0;

echo "检查游戏页面中的提示信息...\n";

foreach ($gameFiles as $file) {
    // 读取文件内容
    $content = file_get_contents($file);
    $filename = basename($file);
    
    // 检查是否包含提示文本
    if (strpos($content, '<div class="game-notice">') !== false) {
        echo "处理文件: $filename - 找到提示文本\n";
        
        // 使用正则表达式替换提示文本
        $pattern = '/<div class="game-notice">[\s\S]*?<\/div>/';
        $newContent = preg_replace($pattern, '', $content);
        
        // 保存修改后的内容
        file_put_contents($file, $newContent);
        
        $noticeCount++;
    } else {
        echo "跳过文件: $filename - 未找到提示文本\n";
    }
}

echo "\n完成! 共移除了 $noticeCount 个提示文本\n";
?> 