<?php
require_once 'functions.php';

// 如果已登录，跳转到仪表盘
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '请输入用户名和密码';
    } else {
        // 获取用户数据
        $users = json_decode(file_get_contents(USERS_DATA_FILE), true);
        
        // 检查用户名和密码
        $user = null;
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $user = $u;
                break;
            }
        }
        
        if ($user && password_verify($password, $user['password'])) {
                       // 登录成功
            $_SESSION['user'] = [
                'username' => $user['username'],
                'role' => $user['role']
            ];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = '用户名或密码错误';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - Free Mini Games 管理后台</title>
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1>Free Mini Games 管理后台</h1>
                <p>请登录以继续</p>
            </div>
            
            <div class="login-form">
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="username">用户名</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">密码</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" style="width:100%;">登录</button>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; 2023 Free Mini Games. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>