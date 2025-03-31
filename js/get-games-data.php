<?php
/**
 * 游戏数据提供脚本
 * 用于确保游戏数据不被缓存，每次请求都获得新的随机排序
 */

// 设置HTTP头，禁止缓存
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// 游戏数据文件路径
$jsonFile = __DIR__ . '/games-data.json';

if (file_exists($jsonFile)) {
    // 读取JSON数据
    $jsonData = file_get_contents($jsonFile);
    
    // 解析为PHP数组
    $data = json_decode($jsonData, true);
    
    // 如果存在games键，说明是新格式
    if (isset($data['games'])) {
        // 随机打乱游戏数组
        shuffle($data['games']);
        
        // 更新时间戳
        $data['timestamp'] = time();
    } else {
        // 旧格式，直接打乱
        shuffle($data);
        
        // 包装成新格式
        $data = [
            'timestamp' => time(),
            'games' => $data
        ];
    }
    
    // 输出JSON数据
    echo json_encode($data);
} else {
    // 文件不存在
    http_response_code(404);
    echo json_encode(['error' => 'Game data file not found']);
} 