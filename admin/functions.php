<?php
require_once 'config.php';

// 获取所有游戏
function getAllGames() {
    if (file_exists(GAMES_DATA_FILE)) {
        $data = file_get_contents(GAMES_DATA_FILE);
        return json_decode($data, true) ?: [];
    }
    return [];
}

// 获取单个游戏
function getGame($id) {
    $games = getAllGames();
    foreach ($games as $game) {
        if ($game['id'] === $id) {
            return $game;
        }
    }
    return null;
}

// 保存游戏
function saveGame($gameData) {
    $games = getAllGames();
    
    // 生成唯一ID（如果是新游戏）
    if (empty($gameData['id'])) {
        $gameData['id'] = createSlug($gameData['title']);
    }
    
    // 检查是否存在相同ID的游戏
    $found = false;
    foreach ($games as $key => $game) {
        if ($game['id'] === $gameData['id']) {
            $games[$key] = $gameData;
            $found = true;
            break;
        }
    }
    
    // 如果没找到，添加新游戏
    if (!$found) {
        $games[] = $gameData;
    }
    
    // 保存到文件
    file_put_contents(GAMES_DATA_FILE, json_encode($games, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // 生成游戏页面
    generateGamePage($gameData);
    
    // 更新首页和分类页
    updateGamesList();
    
    return $gameData['id'];
}

// 删除游戏
function deleteGame($id) {
    $games = getAllGames();
    $newGames = [];
    $deletedGame = null;
    
    foreach ($games as $game) {
        if ($game['id'] !== $id) {
            $newGames[] = $game;
        } else {
            $deletedGame = $game;
        }
    }
    
    file_put_contents(GAMES_DATA_FILE, json_encode($newGames, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // 删除游戏页面
    $gamePage = GAMES_PATH . '/' . $id . '.html';
    if (file_exists($gamePage)) {
        unlink($gamePage);
    }
    
    // 更新首页和分类页
    updateGamesList();
    
    return $deletedGame;
}

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

// 获取单个分类
function getCategory($id) {
    $categories = getAllCategories();
    foreach ($categories as $category) {
        if ($category['id'] === $id) {
            return $category;
        }
    }
    return null;
}

// 创建URL友好的slug
function createSlug($str) {
    // 转为小写并替换空格为短横线
    $slug = strtolower(trim($str));
    
    // 处理空白或只有特殊字符的情况
    if (empty($slug)) {
        return 'game-' . time(); // 如果为空，生成一个带时间戳的默认值
    }
    
    // 移除非字母数字字符，替换为连字符
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // 再次检查处理后是否为空（所有字符都被替换掉的情况）
    if (empty($slug)) {
        return 'game-' . time();
    }
    
    // 确保slug不以数字开头（某些系统可能有问题）
    if (is_numeric(substr($slug, 0, 1))) {
        $slug = 'game-' . $slug;
    }
    
    return $slug;
}

// 生成游戏HTML页面
function generateGamePage($game) {
    $gameId = $game['id'];
    $gameTitle = $game['title'];
    $gameDescription = $game['description'];
    $gameEmbed = $game['iframe'] ?? '';
    $gameCategory = $game['category'];
    $gameThumbnail = $game['thumbnail'];
    $gameRating = $game['rating'];
    
    // 获取分类信息
    $category = getCategory($gameCategory);
    $categoryName = $category ? $category['name'] : '未分类';
    
    // 读取游戏页面模板
    $templatePath = ADMIN_PATH . '/template/game-template.html';
    if (!file_exists($templatePath)) {
        // 如果模板不存在，创建一个新模板
        createGameTemplate();
    }
    
    $templateContent = file_get_contents($templatePath);
    
    // 提取iframe标签中的src属性
    $embedUrl = '';
    if (preg_match('/src=["\'](.*?)["\']/', $gameEmbed, $matches)) {
        $embedUrl = $matches[1];
    } else {
        // 如果无法提取，则直接使用iframe代码
        $embedUrl = $gameEmbed;
    }
    
    // 替换模板中的占位符
    $templateContent = str_replace('{GAME_TITLE}', $gameTitle, $templateContent);
    $templateContent = str_replace('{GAME_DESCRIPTION}', $gameDescription, $templateContent);
    $templateContent = str_replace('{GAME_EMBED_URL}', $embedUrl, $templateContent);
    $templateContent = str_replace('{GAME_CATEGORY}', $categoryName, $templateContent);
    $templateContent = str_replace('{GAME_RATING}', $gameRating, $templateContent);
    $templateContent = str_replace('{GAME_THUMBNAIL}', $gameThumbnail, $templateContent);
    $templateContent = str_replace('{GAME_ID}', $gameId, $templateContent);
    
    // 创建游戏HTML文件
    $gamePage = ROOT_PATH . '/games/' . $gameId . '.html';
    file_put_contents($gamePage, $templateContent);
    
    return true;
}

// 创建游戏页面模板
function createGameTemplate() {
    $templateContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{GAME_TITLE} - Free Mini Games</title>
    <meta name="description" content="Play {GAME_TITLE} online for free at Free Mini Games. {GAME_DESCRIPTION}">
    <link rel="stylesheet" href="../css/dark-theme.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Header Navigation -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="../index.html">
                    <h1>Free Mini Games</h1>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.html">Home</a></li>
                    <li><a href="../games.html" class="active">Games</a></li>
                </ul>
            </nav>
            <div class="search-bar">
                <form action="../search.html" method="get">
                    <input type="text" name="q" placeholder="Search games...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Top Ad Banner -->
    <div class="ad-banner">
        <div class="container">
            <div class="ad-container">
                <p class="ad-text">Ad Space - 728x90</p>
            </div>
        </div>
    </div>

    <!-- Game Page Content -->
    <section class="game-page">
        <div class="container">
            <!-- Game Title and Breadcrumbs -->
            <div class="game-header">
                <h1>{GAME_TITLE}</h1>
                <div class="breadcrumbs">
                    <a href="../index.html">Home</a> > <a href="../games.html">Games</a> > <a href="../games.html?category={GAME_CATEGORY}">{GAME_CATEGORY}</a> > <span>{GAME_TITLE}</span>
                </div>
            </div>

            <!-- Game Content -->
            <div class="game-content-wrapper">
                <!-- Game Main Column -->
                <div class="game-main-column">
                    <!-- Game Frame -->
                    <div class="game-frame">
                        <iframe 
                            src="{GAME_EMBED_URL}" 
                            frameborder="0" 
                            allow="gamepad *; autoplay; fullscreen" 
                            allowfullscreen
                            sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            importance="high"
                            title="{GAME_TITLE} Online Game"></iframe>
                    </div>
                    
                    <!-- Game Controls -->
                    <div class="game-controls">
                        <button class="control-btn fullscreen-btn">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                        <div class="game-info-box">
                            <span class="game-category"><i class="fas fa-tag"></i> {GAME_CATEGORY}</span>
                            <span class="game-rating"><i class="fas fa-star"></i> {GAME_RATING}</span>
                            <span class="game-plays"><i class="fas fa-gamepad"></i> 0 Plays</span>
                        </div>
                    </div>
                    
                    <!-- Game Description -->
                    <div class="game-description">
                        <h2>About {GAME_TITLE}</h2>
                        <p>{GAME_DESCRIPTION}</p>
                        
                        <div class="game-notice">
                            <p><strong>Note:</strong> Game progress is not saved when playing on Free Mini Games. For full experience with saved progress, you may want to play directly on the game provider's website.</p>
                        </div>
                    </div>
                    
                    <!-- Ad Banner -->
                    <div class="ad-container game-bottom-ad">
                        <p class="ad-text">Ad Space - 728x90</p>
                    </div>
                    
                    <!-- Similar Games -->
                    <div class="similar-games">
                        <h2>Similar Games</h2>
                        <div class="games-grid">
                            <!-- Similar games will be added here -->
                        </div>
                    </div>
                </div>
                
                <!-- Game Sidebar -->
                <div class="game-sidebar">
                    <!-- Sidebar Ad -->
                    <div class="sidebar-ad">
                        <p class="ad-text">Ad Space - 300x250</p>
                    </div>
                    
                    <!-- Top Games -->
                    <div class="sidebar-widget top-games">
                        <h3>Top {GAME_CATEGORY} Games</h3>
                        <ul>
                            <!-- Top games in the same category will be added here -->
                        </ul>
                    </div>
                    
                    <!-- Tall Ad -->
                    <div class="sidebar-ad tall-ad">
                        <p class="ad-text">Ad Space - 300x600</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom Ad Banner -->
    <div class="ad-banner">
        <div class="container">
            <div class="ad-container">
                <p class="ad-text">Ad Space - 728x90</p>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="../js/main.js"></script>
    <script src="../js/games.js"></script>
</body>
</html>
HTML;

    $templatePath = ADMIN_PATH . '/template/game-template.html';
    file_put_contents($templatePath, $templateContent);
    
    return true;
}

// 更新首页和游戏列表页
function updateGamesList() {
    // 更新首页热门游戏
    updateHomepageFeaturedGames();
    
    // 更新游戏分类页
    updateCategoryPage();
    
    return true;
}

// 更新首页热门游戏
function updateHomepageFeaturedGames() {
    $homepagePath = ROOT_PATH . '/index.html';
    
    if (!file_exists($homepagePath)) {
        return false;
    }
    
    // 获取所有游戏
    $games = getAllGames();
    
    // 显示所有游戏（按添加顺序的最新游戏排在前面）
    $featuredGames = array_reverse($games);
    
    // 读取首页内容
    $homepageContent = file_get_contents($homepagePath);
    
    // 定位游戏数据区域
    $gameDataStart = strpos($homepageContent, '<div id="game-data"');
    if ($gameDataStart === false) {
        // 如果找不到游戏数据区域，尝试添加它
        $addPoint = strpos($homepageContent, '<!-- JavaScript Files -->');
        if ($addPoint === false) {
            $addPoint = strpos($homepageContent, '</body>');
            if ($addPoint === false) {
                return false;
            }
        }
        
        // 创建新的游戏数据区域
        $gameDataHtml = '
    <!-- Game cards hidden data for infinite-scroll.js to load -->
    <div id="game-data" style="display: none;">';
        
        // 在JavaScript前插入游戏数据区域
        $homepageContent = substr_replace($homepageContent, $gameDataHtml, $addPoint, 0);
        $gameDataStart = strpos($homepageContent, '<div id="game-data"');
        
        // 更新位置，为结束标签位置
        $addPoint += strlen($gameDataHtml);
        $homepageContent = substr_replace($homepageContent, "\n    </div>\n\n", $addPoint, 0);
    }
    
    // 查找游戏数据区开始和结束位置
    $dataStart = strpos($homepageContent, '>', $gameDataStart) + 1;
    $dataEnd = strpos($homepageContent, '</div>', $dataStart);
    
    if ($dataStart === false || $dataEnd === false) {
        return false;
    }
    
    // 生成游戏卡片HTML
    $gameCardsHtml = "\n";
    
    foreach ($featuredGames as $game) {
        $category = getCategory($game['category']);
        $categoryName = $category ? $category['name'] : 'Uncategorized';
        
        $gameCardsHtml .= <<<HTML
        <!-- Game Card -->
        <div class="game-card" data-category="{$game['category']}">
            <a href="games/{$game['id']}.html">
                <div class="game-thumbnail">
                    <img src="images/games/{$game['thumbnail']}" alt="{$game['title']}">
                    <div class="game-overlay">
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
                <div class="game-info">
                    <h3>{$game['title']}</h3>
                    <div class="game-meta">
                        <span class="game-category">{$categoryName}</span>
                        <span class="game-rating"><i class="fas fa-star"></i> {$game['rating']}</span>
                    </div>
                </div>
            </a>
        </div>

HTML;
    }
    
    // 替换游戏数据内容
    $newHomepageContent = substr($homepageContent, 0, $dataStart);
    $newHomepageContent .= $gameCardsHtml;
    $newHomepageContent .= substr($homepageContent, $dataEnd);
    
    // 保存更新后的首页
    file_put_contents($homepagePath, $newHomepageContent);
    
    return true;
}

// 更新游戏分类页
function updateCategoryPage() {
    $categoryPagePath = ROOT_PATH . '/games.html';
    
    if (!file_exists($categoryPagePath)) {
        return false;
    }
    
    // 获取所有游戏
    $games = getAllGames();
    
    // 读取分类页内容
    $categoryPageContent = file_get_contents($categoryPagePath);
    
    // 定位游戏列表区域
    $gamesListStart = strpos($categoryPageContent, '<!-- 游戏列表区域 -->');
    if ($gamesListStart === false) {
        // 尝试其他可能的标记
        $gamesListStart = strpos($categoryPageContent, '<div class="games-grid">');
        if ($gamesListStart === false) {
            return false;
        }
    }
    
    // 查找游戏网格开始和结束位置
    $gridStart = strpos($categoryPageContent, '<div class="games-grid">', $gamesListStart);
    if ($gridStart === false) {
        return false;
    }
    
    // 尝试找到网格结束位置
    $gridEnd = strpos($categoryPageContent, '</div>', $gridStart + 10);
    if ($gridEnd === false) {
        return false;
    }
    
    // 尝试找到下一个主要部分
    $nextSectionStart = strpos($categoryPageContent, '<div class="pagination">', $gridStart);
    if ($nextSectionStart === false) {
        // 如果没有分页，尝试找到下一个主要部分
        $nextSectionStart = strpos($categoryPageContent, '</section>', $gridEnd);
        if ($nextSectionStart === false) {
            // 如果仍然找不到，使用网格结束位置加上一些偏移
            $nextSectionStart = $gridEnd + 6; // "</div>"的长度
        }
    }
    
    // 生成游戏卡片HTML
    $gameCardsHtml = '<div class="games-grid">' . "\n";
    
    foreach ($games as $game) {
        $category = getCategory($game['category']);
        $categoryName = $category ? $category['name'] : '未分类';
        
        $gameCardsHtml .= <<<HTML
                <!-- 游戏卡片 -->
                <div class="game-card" data-category="{$game['category']}">
                    <a href="games/{$game['id']}.html">
                        <div class="game-thumbnail">
                            <img src="images/games/{$game['thumbnail']}" alt="{$game['title']}">
                            <div class="game-overlay">
                                <div class="play-button">
                                    <i class="fas fa-play"></i>
                                </div>
                            </div>
                        </div>
                        <div class="game-info">
                            <h3>{$game['title']}</h3>
                            <div class="game-meta">
                                <span class="game-category">{$categoryName}</span>
                                <span class="game-rating"><i class="fas fa-star"></i> {$game['rating']}</span>
                            </div>
                        </div>
                    </a>
                </div>
                
HTML;
    }
    
    $gameCardsHtml .= '</div>';
    
    // 替换游戏网格内容
    $newCategoryPageContent = substr($categoryPageContent, 0, $gridStart);
    $newCategoryPageContent .= $gameCardsHtml;
    $newCategoryPageContent .= substr($categoryPageContent, $nextSectionStart);
    
    // 保存更新后的分类页
    file_put_contents($categoryPagePath, $newCategoryPageContent);
    
    return true;
}

// 上传图片
function uploadGameImage($file) {
    $targetDir = IMAGES_PATH . '/';
    $fileName = basename($file['name']);
    $targetFile = $targetDir . $fileName;
    
    // 检查文件类型
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    if($imageFileType != 'jpg' && $imageFileType != 'png' && $imageFileType != 'jpeg') {
        return ['success' => false, 'message' => '只允许JPG、JPEG和PNG文件'];
    }
    
    // 检查文件大小
    if ($file['size'] > 5000000) { // 5MB
        return ['success' => false, 'message' => '文件太大，最大5MB'];
    }
    
    // 尝试上传文件
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => true, 'filename' => $fileName];
    } else {
        return ['success' => false, 'message' => '上传失败'];
    }
}

// 验证登录状态
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// 需要登录才能访问
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}