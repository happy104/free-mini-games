<?php
require_once 'functions.php';
requireLogin();

$message = '';
$error = '';

// 获取游戏ID
$gameId = $_GET['id'] ?? '';
if (empty($gameId)) {
    header('Location: manage-games.php');
    exit;
}

// 获取游戏数据
$game = getGame($gameId);
if (!$game) {
    header('Location: manage-games.php');
    exit;
}

// 获取所有分类
$categories = getAllCategories();

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $rating = $_POST['rating'] ?? '5';
    $iframe = $_POST['iframe'] ?? '';
    
    // 验证输入
    if (empty($title) || empty($description) || empty($category) || empty($iframe)) {
        $error = '所有必填字段都必须填写';
    } else {
        // 处理缩略图上传
        $thumbnailFileName = $game['thumbnail']; // 默认使用现有的缩略图
        
        $thumbnailFile = $_FILES['thumbnail'] ?? null;
        if ($thumbnailFile && $thumbnailFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadGameImage($thumbnailFile);
            
            if ($uploadResult['success']) {
                $thumbnailFileName = $uploadResult['filename'];
            } else {
                $error = '缩略图上传失败: ' . $uploadResult['message'];
            }
        } else if ($thumbnailFile && $thumbnailFile['error'] !== UPLOAD_ERR_NO_FILE) {
            // 如果有错误且不是"没有文件"的错误
            $error = '缩略图上传失败: ';
            
            switch ($thumbnailFile['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error .= '文件太大';
                    break;
                default:
                    $error .= '未知错误';
            }
        }
        
        if (empty($error)) {
            // 更新游戏数据
            $gameData = [
                'id' => $gameId,
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'rating' => $rating,
                'iframe' => $iframe,
                'thumbnail' => $thumbnailFileName
            ];
            
            // 保存游戏
            saveGame($gameData);
            
            $message = '游戏更新成功！';
            
            // 重新获取游戏数据
            $game = getGame($gameId);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑游戏 - Free Mini Games 管理后台</title>
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
                <h1>编辑游戏</h1>
                <a href="docs/iframe-guide.html" target="_blank" style="display: inline-block; margin-top: -10px; margin-bottom: 20px; color: #3498db; text-decoration: none;">
                    <i class="fas fa-info-circle"></i> 查看游戏嵌入指南（解决黑屏问题）
                </a>
                <p>正在编辑: <?php echo htmlspecialchars($game['title']); ?></p>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">游戏名称 <span class="required">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($game['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">游戏描述 <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($game['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category">游戏分类 <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">选择分类</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $game['category'] === $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="rating">游戏评分</label>
                    <input type="number" id="rating" name="rating" min="1" max="5" step="0.1" value="<?php echo htmlspecialchars($game['rating']); ?>">
                    <div class="form-help">评分范围: 1.0 - 5.0</div>
                </div>
                
                <div class="form-group">
                    <label for="thumbnail">游戏缩略图</label>
                    <div style="margin-bottom:10px;">
                        <img src="../images/games/<?php echo htmlspecialchars($game['thumbnail']); ?>" alt="缩略图预览" style="max-width:200px; max-height:150px; border-radius:4px;">
                    </div>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png">
                    <div class="form-help">如果不上传新图片，将保留现有图片。推荐尺寸: 300x200 像素</div>
                </div>
                
                <div class="form-group">
                    <label for="iframe">嵌入代码 (iframe) <span class="required">*</span></label>
                    <textarea id="iframe" name="iframe" rows="3" required><?php echo htmlspecialchars($game['iframe']); ?></textarea>
                    <div class="form-help">粘贴游戏的iframe嵌入代码</div>
                </div>
                
                <div class="game-preview">
                    <h3>预览嵌入代码</h3>
                    <div class="preview-container" id="iframe-preview">
                        <?php echo $game['iframe']; ?>
                    </div>
                    <div class="iframe-validation-result" id="iframe-validation-result"></div>
                    <button type="button" class="btn" id="test-iframe">测试iframe加载</button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存更改</button>
                    <a href="manage-games.php" class="btn btn-secondary">返回游戏列表</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const iframeField = document.getElementById('iframe');
        const previewContainer = document.getElementById('iframe-preview');
        const validationResult = document.getElementById('iframe-validation-result');
        const testButton = document.getElementById('test-iframe');
        
        iframeField.addEventListener('input', function() {
            updatePreview();
        });
        
        // 测试iframe加载
        testButton.addEventListener('click', function() {
            testIframeLoading();
        });
        
        function updatePreview() {
            const iframeCode = iframeField.value.trim();
            if (iframeCode) {
                previewContainer.innerHTML = iframeCode;
                // 清除之前的验证结果
                validationResult.innerHTML = '';
                validationResult.className = 'iframe-validation-result';
            } else {
                previewContainer.innerHTML = '<p style="text-align:center;padding-top:120px;color:#666;">在上方输入iframe代码后将显示预览</p>';
            }
        }
        
        function testIframeLoading() {
            const iframe = previewContainer.querySelector('iframe');
            if (!iframe) {
                validationResult.innerHTML = '请先添加有效的iframe代码';
                validationResult.className = 'iframe-validation-result validation-error';
                return;
            }
            
            // 显示测试中提示
            validationResult.innerHTML = '<div class="testing"><i class="fas fa-spinner fa-spin"></i> 正在测试iframe加载，请稍候...</div>';
            validationResult.className = 'iframe-validation-result validation-testing';
            
            // 提取iframe src
            const src = iframe.src;
            if (!src || src === 'about:blank') {
                validationResult.innerHTML = 'iframe缺少有效的src属性';
                validationResult.className = 'iframe-validation-result validation-error';
                return;
            }
            
            // 测试iframe加载
            let isLoaded = false;
            let iframeTimeout;
            
            // 监听加载事件
            iframe.addEventListener('load', function onLoad() {
                isLoaded = true;
                clearTimeout(iframeTimeout);
                
                // 检查iframe内容
                try {
                    // 尝试访问iframe内容，这可能会因跨域限制而失败
                    const iframeContent = iframe.contentWindow.document;
                    validationResult.innerHTML = `
                        <div class="validation-success">
                            <i class="fas fa-check-circle"></i> iframe加载成功！游戏可以正常显示。
                        </div>
                    `;
                    validationResult.className = 'iframe-validation-result validation-success';
                } catch (e) {
                    // 跨域错误，但不一定意味着iframe无法加载
                    validationResult.innerHTML = `
                        <div class="validation-warning">
                            <i class="fas fa-exclamation-triangle"></i> iframe已加载，但存在跨域限制。<br>
                            这通常不是问题，游戏仍然可以正常显示，<br>但无法通过JavaScript访问iframe内容。
                        </div>
                    `;
                    validationResult.className = 'iframe-validation-result validation-warning';
                }
                
                // 移除事件监听器
                iframe.removeEventListener('load', onLoad);
            });
            
            // 监听错误事件
            iframe.addEventListener('error', function onError() {
                clearTimeout(iframeTimeout);
                validationResult.innerHTML = `
                    <div class="validation-error">
                        <i class="fas fa-times-circle"></i> iframe加载失败！<br>
                        请检查源URL是否有效，或者源网站是否允许在iframe中嵌入。
                    </div>
                `;
                validationResult.className = 'iframe-validation-result validation-error';
                
                // 移除事件监听器
                iframe.removeEventListener('error', onError);
            });
            
            // 设置超时
            iframeTimeout = setTimeout(function() {
                if (!isLoaded) {
                    validationResult.innerHTML = `
                        <div class="validation-warning">
                            <i class="fas fa-exclamation-triangle"></i> iframe加载时间过长。<br>
                            这可能表明游戏源响应缓慢或者存在其他问题。<br>
                            建议测试以下几点：<br>
                            1. 确认游戏源URL是否正确<br>
                            2. 检查网络连接<br>
                            3. 确认游戏源允许在iframe中嵌入<br>
                            4. 尝试使用其他游戏源
                        </div>
                    `;
                    validationResult.className = 'iframe-validation-result validation-warning';
                }
            }, 10000); // 10秒超时
            
            // 刷新iframe以触发加载事件
            const currentSrc = iframe.src;
            iframe.src = 'about:blank';
            setTimeout(function() {
                iframe.src = currentSrc;
            }, 100);
        }
    });
    </script>
    
    <style>
    .iframe-validation-result {
        margin-top: 15px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .validation-testing {
        background-color: #f8f9fa;
    }
    
    .validation-success {
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 4px;
    }
    
    .validation-warning {
        background-color: #fff3cd;
        color: #856404;
        padding: 10px;
        border-radius: 4px;
    }
    
    .validation-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
    }
    
    .testing {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    #test-iframe {
        margin-top: 10px;
        background-color: #17a2b8;
        color: white;
    }
    </style>
</body>
</html>