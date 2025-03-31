<?php
/**
 * 获取所有游戏数据的API
 * 扫描HTML文件中的游戏卡片并以JSON格式返回
 */

// 启用错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置内容类型为JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// 日志函数
function logMessage($message) {
    // 将消息写入日志文件
    file_put_contents('game_api.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// 记录API调用
logMessage('API调用: get-all-games.php');

// 游戏列表
$games = [];

try {
    // 1. 从index.html文件中提取游戏卡片
    $indexFile = file_get_contents('index.html');
    if ($indexFile) {
        logMessage('成功读取index.html文件');
        
        // 使用DOM解析HTML
        $dom = new DOMDocument();
        // 禁用错误报告，避免HTML5标签引起的警告
        libxml_use_internal_errors(true);
        $dom->loadHTML($indexFile);
        libxml_clear_errors();
        
        // 查找所有游戏卡片
        $xpath = new DOMXPath($dom);
        $gameCards = $xpath->query('//div[contains(@class, "game-card")]');
        
        logMessage('找到' . $gameCards->length . '个游戏卡片在index.html中');
        
        foreach ($gameCards as $card) {
            $game = extractGameData($card, $dom);
            if ($game) {
                $games[] = $game;
            }
        }
    }
    
    // 2. 扫描游戏文件夹 (如果存在)
    $gameFolders = ['games', 'game-files', 'uploads'];
    foreach ($gameFolders as $folder) {
        if (is_dir($folder)) {
            logMessage("扫描{$folder}文件夹");
            
            // 获取文件夹中的所有HTML文件
            $htmlFiles = glob("$folder/*.html");
            foreach ($htmlFiles as $file) {
                $gameHtml = file_get_contents($file);
                if ($gameHtml) {
                    // 解析HTML
                    $gameDom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $gameDom->loadHTML($gameHtml);
                    libxml_clear_errors();
                    
                    // 查找游戏信息
                    $gameXpath = new DOMXPath($gameDom);
                    $gameCards = $gameXpath->query('//div[contains(@class, "game-card")]');
                    
                    if ($gameCards->length > 0) {
                        logMessage("在{$file}中找到" . $gameCards->length . "个游戏卡片");
                        
                        foreach ($gameCards as $card) {
                            $game = extractGameData($card, $gameDom);
                            if ($game) {
                                $games[] = $game;
                            }
                        }
                    } else {
                        // 如果没有找到游戏卡片，尝试从文件名和内容中提取游戏信息
                        $game = extractGameFromFile($file, $gameHtml, $gameDom);
                        if ($game) {
                            $games[] = $game;
                        }
                    }
                }
            }
        }
    }
    
    // 3. 从backup或特殊目录中寻找额外的游戏
    $backupFiles = ['index_backup.html', 'backup/index.html', 'data/games.json'];
    foreach ($backupFiles as $file) {
        if (file_exists($file)) {
            logMessage("检查备份文件：{$file}");
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                // 如果是JSON文件，直接解析
                $jsonData = file_get_contents($file);
                $backupGames = json_decode($jsonData, true);
                
                if (is_array($backupGames)) {
                    logMessage("从{$file}中加载了" . count($backupGames) . "个游戏");
                    $games = array_merge($games, $backupGames);
                }
            } else {
                // 如果是HTML文件，解析HTML
                $backupHtml = file_get_contents($file);
                if ($backupHtml) {
                    $backupDom = new DOMDocument();
                    libxml_use_internal_errors(true);
                    $backupDom->loadHTML($backupHtml);
                    libxml_clear_errors();
                    
                    $backupXpath = new DOMXPath($backupDom);
                    $backupCards = $backupXpath->query('//div[contains(@class, "game-card")]');
                    
                    logMessage("在{$file}中找到" . $backupCards->length . "个游戏卡片");
                    
                    foreach ($backupCards as $card) {
                        $game = extractGameData($card, $backupDom);
                        if ($game) {
                            $games[] = $game;
                        }
                    }
                }
            }
        }
    }
    
    // 去除重复的游戏(按标题)
    $uniqueGames = [];
    $gameTitles = [];
    
    foreach ($games as $game) {
        if (!empty($game['title']) && !in_array($game['title'], $gameTitles)) {
            $gameTitles[] = $game['title'];
            $uniqueGames[] = $game;
        }
    }
    
    logMessage("总共收集了" . count($games) . "个游戏，去重后有" . count($uniqueGames) . "个游戏");
    
    // 返回游戏列表
    echo json_encode($uniqueGames);
    
} catch (Exception $e) {
    logMessage("错误: " . $e->getMessage());
    echo json_encode([
        'error' => '获取游戏数据时出错',
        'message' => $e->getMessage()
    ]);
}

/**
 * 从游戏卡片DOM节点提取游戏数据
 */
function extractGameData($card, $dom) {
    $game = [
        'title' => '',
        'image' => '',
        'url' => '',
        'category' => '',
        'rating' => '4.5'
    ];
    
    // 提取标题
    $titleNode = getElementsByClassName($card, 'game-title', $dom);
    if ($titleNode && $titleNode->length > 0) {
        $game['title'] = trim($titleNode->item(0)->textContent);
    } else {
        // 尝试使用h3标签作为标题
        $h3Nodes = $card->getElementsByTagName('h3');
        if ($h3Nodes->length > 0) {
            $game['title'] = trim($h3Nodes->item(0)->textContent);
        }
    }
    
    // 提取图片
    $imgNodes = $card->getElementsByTagName('img');
    if ($imgNodes->length > 0) {
        $img = $imgNodes->item(0);
        $game['image'] = $img->getAttribute('src');
    }
    
    // 提取链接
    $linkNodes = $card->getElementsByTagName('a');
    if ($linkNodes->length > 0) {
        $link = $linkNodes->item(0);
        $game['url'] = $link->getAttribute('href');
    }
    
    // 提取分类
    $categoryNode = getElementsByClassName($card, 'game-category', $dom);
    if ($categoryNode && $categoryNode->length > 0) {
        $game['category'] = trim($categoryNode->item(0)->textContent);
    } else {
        // 尝试从data-category属性获取
        $dataCategory = $card->getAttribute('data-category');
        if ($dataCategory) {
            $game['category'] = $dataCategory;
        }
    }
    
    // 提取评分
    $ratingNode = getElementsByClassName($card, 'game-rating', $dom);
    if ($ratingNode && $ratingNode->length > 0) {
        $ratingText = trim($ratingNode->item(0)->textContent);
        $ratingValue = preg_replace('/[^0-9.]/', '', $ratingText);
        if ($ratingValue) {
            $game['rating'] = $ratingValue;
        }
    }
    
    // 提取描述
    $descNode = getElementsByClassName($card, 'game-description', $dom);
    if ($descNode && $descNode->length > 0) {
        $game['description'] = trim($descNode->item(0)->textContent);
    }
    
    // 只返回有标题的游戏
    if (!empty($game['title'])) {
        return $game;
    }
    
    return null;
}

/**
 * 根据类名获取元素
 */
function getElementsByClassName($element, $className, $dom) {
    $xpath = new DOMXPath($dom);
    return $xpath->query('.//*[contains(@class, "' . $className . '")]', $element);
}

/**
 * 从文件中提取游戏信息
 */
function extractGameFromFile($filePath, $html, $dom) {
    // 从文件名中提取游戏标题
    $fileName = pathinfo($filePath, PATHINFO_FILENAME);
    $title = str_replace(['-', '_'], ' ', $fileName);
    $title = ucwords($title);
    
    // 查找可能的图片
    $images = $dom->getElementsByTagName('img');
    $image = '';
    if ($images->length > 0) {
        // 优先选择较大的图片
        $largestArea = 0;
        foreach ($images as $img) {
            $width = $img->getAttribute('width') ?: 100;
            $height = $img->getAttribute('height') ?: 100;
            $area = $width * $height;
            
            if ($area > $largestArea) {
                $largestArea = $area;
                $image = $img->getAttribute('src');
            }
        }
    }
    
    // 确定分类
    $category = '';
    $lowerHtml = strtolower($html);
    
    if (strpos($lowerHtml, 'racing') !== false || strpos($lowerHtml, '竞速') !== false) {
        $category = 'racing';
    } elseif (strpos($lowerHtml, 'action') !== false || strpos($lowerHtml, '动作') !== false) {
        $category = 'action';
    } elseif (strpos($lowerHtml, 'puzzle') !== false || strpos($lowerHtml, '益智') !== false) {
        $category = 'puzzle';
    } elseif (strpos($lowerHtml, 'horror') !== false || strpos($lowerHtml, '恐怖') !== false) {
        $category = 'horror';
    } elseif (strpos($lowerHtml, 'sports') !== false || strpos($lowerHtml, '体育') !== false) {
        $category = 'sports';
    } elseif (strpos($lowerHtml, 'strategy') !== false || strpos($lowerHtml, '策略') !== false) {
        $category = 'strategy';
    } elseif (strpos($lowerHtml, 'adventure') !== false || strpos($lowerHtml, '冒险') !== false) {
        $category = 'adventure';
    } elseif (strpos($lowerHtml, 'casual') !== false || strpos($lowerHtml, '休闲') !== false) {
        $category = 'casual';
    }
    
    // 返回游戏信息
    return [
        'title' => $title,
        'image' => $image,
        'url' => $filePath,  // 使用文件路径作为URL
        'category' => $category,
        'rating' => '4.5',
        'fromFile' => true
    ];
}
?> 