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
                ['id' => 'action', 'name' => 'Action Games'],
                ['id' => 'puzzle', 'name' => 'Puzzle Games'],
                ['id' => 'racing', 'name' => 'Racing Games'],
                ['id' => 'sports', 'name' => 'Sports Games'],
                ['id' => 'strategy', 'name' => 'Strategy Games'],
                ['id' => 'horror', 'name' => 'Horror Games'],
                ['id' => 'adventure', 'name' => 'Adventure Games'],
                ['id' => 'casual', 'name' => 'Casual Games']
            ];
        }
        
        return $categories;
    }
    
    // 文件不存在，返回默认分类
    return [
        ['id' => 'action', 'name' => 'Action Games'],
        ['id' => 'puzzle', 'name' => 'Puzzle Games'],
        ['id' => 'racing', 'name' => 'Racing Games'],
        ['id' => 'sports', 'name' => 'Sports Games'],
        ['id' => 'strategy', 'name' => 'Strategy Games'],
        ['id' => 'horror', 'name' => 'Horror Games'],
        ['id' => 'adventure', 'name' => 'Adventure Games'],
        ['id' => 'casual', 'name' => 'Casual Games']
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
    $categoryName = $category ? $category['name'] : 'Uncategorized';
    
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
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <style>
        /* 防止内容闪烁 */
        body {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        body.css-loaded {
            opacity: 1;
        }
        
        /* 游戏加载动画 */
        .loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 100;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        
        /* 确保游戏iframe始终在上层 */
        .game-frame iframe {
            position: relative;
            z-index: 10;
        }
    </style>
    <!-- 基础样式文件 -->
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/dark-theme.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <link rel="stylesheet" href="../css/fixes.css">
    
    <!-- 头部和导航样式 -->
    <link rel="stylesheet" href="../css/header-style.css">
    <link rel="stylesheet" href="../css/nav-style.css">
    <link rel="stylesheet" href="../css/search-fix.css">
    <link rel="stylesheet" href="../css/title-fix.css">
    
    <!-- 游戏详情页样式 -->
    <link rel="stylesheet" href="../css/style-overrides.css">
    <link rel="stylesheet" href="../css/game-cards.css">
    <link rel="stylesheet" href="../css/category-fix.css">
    <link rel="stylesheet" href="../css/game-detail.css">
    <link rel="stylesheet" href="../css/loading-fix.css">
    <link rel="stylesheet" href="../css/game-detail-nav.css">
    <link rel="stylesheet" href="../css/nav-hide.css">
    <link rel="stylesheet" href="../css/header-fix.css">
    
    <!-- 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google AdSense代码 -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-2406571508028686" crossorigin="anonymous"></script>
</head>
<body class="game-detail-page">
    <!-- 头部导航 -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="../index.html">
                    <h1>Free Mini Games</h1>
                </a>
            </div>
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

    <!-- 游戏分类导航 -->
    <section class="categories-nav">
        <div class="container">
            <ul class="categories-list category-filter">
                <li><button onclick="window.location.href='../index.html'" class="category-btn"><i class="fas fa-home"></i> All Games</button></li>
                <!-- 注意：将游戏所属的分类按钮标记为active -->
                <li><button onclick="window.location.href='../index.html?category=action'" class="category-btn">Action</button></li>
                <li><button onclick="window.location.href='../index.html?category=puzzle'" class="category-btn">Puzzle</button></li>
                <li><button onclick="window.location.href='../index.html?category=racing'" class="category-btn">Racing</button></li>
                <li><button onclick="window.location.href='../index.html?category=sports'" class="category-btn">Sports</button></li>
                <li><button onclick="window.location.href='../index.html?category=strategy'" class="category-btn">Strategy</button></li>
                <li><button onclick="window.location.href='../index.html?category=horror'" class="category-btn">Horror</button></li>
                <li><button onclick="window.location.href='../index.html?category=adventure'" class="category-btn">Adventure</button></li>
                <li><button onclick="window.location.href='../index.html?category=casual'" class="category-btn">Casual</button></li>
            </ul>
        </div>
    </section>

    <!-- 游戏内容区域 -->
    <section class="game-page">
        <div class="container">
            <!-- 游戏标题和面包屑 -->
            <div class="game-header">
                <h1>{GAME_TITLE}</h1>
                <div class="breadcrumbs">
                    <a href="../index.html">Home</a>
                    <a href="../index.html">Games</a>
                    <a href="../index.html?category={GAME_CATEGORY}">{GAME_CATEGORY}</a>
                    <span>{GAME_TITLE}</span>
                </div>
            </div>

            <!-- 游戏内容 -->
            <div class="game-content-wrapper">
                <!-- 游戏主要内容区 -->
                <div class="game-main-column">
                    <!-- 游戏框架 -->
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
                        <div class="loading-indicator">
                            <i class="fas fa-spinner fa-spin fa-3x"></i>
                            <p>Loading game...</p>
                        </div>
                    </div>
                    
                    <!-- 游戏控制区 -->
                    <div class="game-controls">
                        <button class="control-btn fullscreen-btn" id="fullscreen-btn">
                            <i class="fas fa-expand"></i> Fullscreen
                        </button>
                        <div class="game-info-box">
                            <span class="game-category"><i class="fas fa-tag"></i> {GAME_CATEGORY}</span>
                            <span class="game-rating"><i class="fas fa-star"></i> {GAME_RATING}</span>
                        </div>
                    </div>
                    
                    <!-- 游戏描述 -->
                    <div class="game-description">
                        <p>{GAME_DESCRIPTION}</p>
                        
                        <p>操作方法: 根据游戏类型而定，通常使用鼠标点击或键盘方向键控制。</p>
                        
                        <p>特点:
                        <br>- 免费在线游戏，无需下载
                        <br>- 支持全屏模式
                        <br>- 流畅的游戏体验
                        <br>- 适合所有年龄段的玩家</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript文件 -->
    <script src="../js/main.js"></script>
    <script src="../js/games.js"></script>
    <script src="../js/game-nav.js"></script>
    <script src="../js/sidebar-games.js"></script>
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
    // 获取所有游戏
    $games = getAllGames();
    
    // 随机排序所有游戏
    shuffle($games);
    
    // 生成游戏数据的JSON文件
    $gameDataPath = ROOT_PATH . '/js/games-data.json';
    
    // 准备游戏数据，只包含前端需要的字段
    $gameDataForJson = [];
    foreach ($games as $game) {
        $category = getCategory($game['category']);
        $categoryName = $category ? $category['name'] : 'Uncategorized';
        
        $gameDataForJson[] = [
            'id' => $game['id'],
            'title' => $game['title'],
            'category' => $game['category'],
            'categoryName' => $categoryName,
            'rating' => $game['rating'],
            'thumbnail' => $game['thumbnail']
        ];
    }
    
    // 添加时间戳到数据中，确保内容发生变化
    $dataWithTimestamp = [
        'timestamp' => time(),
        'games' => $gameDataForJson
    ];
    
    // 保存JSON数据文件
    $jsonData = json_encode($dataWithTimestamp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($gameDataPath, $jsonData);
    
    // 确保index.html中有游戏数据加载的脚本
    $homepagePath = ROOT_PATH . '/index.html';
    
    if (!file_exists($homepagePath)) {
        return false;
    }
    
    // 读取首页内容
    $homepageContent = file_get_contents($homepagePath);
    
    // 检查是否已有<script src="js/games-loader.js"></script>
    if (strpos($homepageContent, 'games-loader.js') === false) {
        // 在JavaScript文件加载部分添加我们的新脚本
        $scriptInsertPoint = strpos($homepageContent, '<!-- JavaScript Files -->');
        if ($scriptInsertPoint === false) {
            $scriptInsertPoint = strpos($homepageContent, '</body>');
        }
        
        if ($scriptInsertPoint !== false) {
            $scriptTag = "\n<script src=\"js/games-loader.js\"></script>\n";
            $homepageContent = substr_replace($homepageContent, $scriptTag, $scriptInsertPoint, 0);
            file_put_contents($homepagePath, $homepageContent);
        }
    }
    
    // 检查并清理旧的游戏数据区域
    $gameDataStart = strpos($homepageContent, '<div id="game-data"');
    if ($gameDataStart !== false) {
        $dataStart = strpos($homepageContent, '>', $gameDataStart) + 1;
        $dataEnd = strpos($homepageContent, '</div>', $dataStart);
        
        if ($dataStart !== false && $dataEnd !== false) {
            // 清空游戏数据区域，但保留div标签
            $newHomepageContent = substr($homepageContent, 0, $dataStart);
            $newHomepageContent .= "\n        <!-- Game data moved to js/games-data.json file -->\n    ";
            $newHomepageContent .= substr($homepageContent, $dataEnd);
            file_put_contents($homepagePath, $newHomepageContent);
        }
    }
    
    return true;
}

// 更新游戏分类页
function updateCategoryPage() {
    // 由于我们现在使用同一个JSON数据源，所以分类页也从同一个数据文件加载
    // 该函数不再需要单独更新，只需确保games.html引用正确的脚本
    $categoryPagePath = ROOT_PATH . '/games.html';
    
    if (!file_exists($categoryPagePath)) {
        return false;
    }
    
    // 读取分类页内容
    $categoryPageContent = file_get_contents($categoryPagePath);
    
    // 检查是否已有<script src="js/games-loader.js"></script>
    if (strpos($categoryPageContent, 'games-loader.js') === false) {
        // 在JavaScript文件加载部分添加我们的新脚本
        $scriptInsertPoint = strpos($categoryPageContent, '<!-- JavaScript Files -->');
        if ($scriptInsertPoint === false) {
            $scriptInsertPoint = strpos($categoryPageContent, '</body>');
        }
        
        if ($scriptInsertPoint !== false) {
            $scriptTag = "\n<script src=\"js/games-loader.js\"></script>\n";
            $categoryPageContent = substr_replace($categoryPageContent, $scriptTag, $scriptInsertPoint, 0);
            file_put_contents($categoryPagePath, $categoryPageContent);
        }
    }
    
    // 清理旧的游戏卡片区域，使其只包含空的容器
    $gamesListStart = strpos($categoryPageContent, '<div class="games-grid">');
    if ($gamesListStart !== false) {
        $nextSectionStart = strpos($categoryPageContent, '<div class="pagination">', $gamesListStart);
        if ($nextSectionStart === false) {
            $nextSectionStart = strpos($categoryPageContent, '</section>', $gamesListStart);
        }
        
        if ($nextSectionStart !== false) {
            // 替换整个游戏网格内容
            $newContent = substr($categoryPageContent, 0, $gamesListStart);
            $newContent .= '<div class="games-grid" id="games-container">' . "\n";
            $newContent .= '                <!-- Game cards will be loaded from games-data.json via JavaScript -->' . "\n";
            $newContent .= '                <div class="loading-spinner">' . "\n";
            $newContent .= '                    <i class="fas fa-spinner fa-spin fa-2x"></i>' . "\n";
            $newContent .= '                    <p>Loading games...</p>' . "\n";
            $newContent .= '                </div>' . "\n";
            $newContent .= '            </div>' . "\n\n";
            $newContent .= substr($categoryPageContent, $nextSectionStart);
            
            file_put_contents($categoryPagePath, $newContent);
        }
    }
    
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