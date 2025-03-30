<?php
require_once 'functions.php';
requireLogin();

$message = '';
$error = '';

// 处理密码更改
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = '所有密码字段都必须填写';
    } else if ($newPassword !== $confirmPassword) {
        $error = '新密码和确认密码不匹配';
    } else if (strlen($newPassword) < 6) {
        $error = '新密码必须至少6个字符';
    } else {
        // 获取用户数据
        $users = json_decode(file_get_contents(USERS_DATA_FILE), true);
        
        // 查找当前用户
        $currentUser = null;
        $userIndex = -1;
        
        foreach ($users as $index => $user) {
            if ($user['username'] === $_SESSION['user']['username']) {
                $currentUser = $user;
                $userIndex = $index;
                break;
            }
        }
        
        if (!$currentUser || !password_verify($currentPassword, $currentUser['password'])) {
            $error = '当前密码不正确';
        } else {
            // 更新密码
            $users[$userIndex]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // 保存用户数据
            file_put_contents(USERS_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT));
            
            $message = '密码已成功更改';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>设置 - Free Mini Games 管理后台</title>
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
                <h1>设置</h1>
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
            
            <div style="max-width:600px;">
                <h2 style="margin-bottom:20px;">修改密码</h2>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="current_password">当前密码 <span class="required">*</span></label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">新密码 <span class="required">*</span></label>
                        <input type="password" id="new_password" name="new_password" required>
                        <div class="form-help">密码应至少包含6个字符</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">确认新密码 <span class="required">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn btn-primary">修改密码</button>
                    </div>
                </form>
                
                <hr style="margin:30px 0;">
                
                <h2 style="margin-bottom:20px;">系统信息</h2>
                
                <table class="admin-table">
                    <tr>
                        <th>管理后台版本</th>
                        <td>1.0.0</td>
                    </tr>
                    <tr>
                        <th>PHP 版本</th>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <th>服务器操作系统</th>
                        <td><?php echo php_uname(); ?></td>
                    </tr>
                    <tr>
                        <th>数据存储路径</th>
                        <td><?php echo DATA_PATH; ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>