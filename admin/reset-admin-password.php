<?php
// 包含配置和函数文件
require_once 'config.php';

// 创建新的管理员密码
$newPassword = 'admin123';

// 读取现有用户数据
if (file_exists(USERS_DATA_FILE)) {
    $users = json_decode(file_get_contents(USERS_DATA_FILE), true);
} else {
    // 如果用户文件不存在，创建一个新的用户数组
    $users = [];
}

$adminFound = false;

// 查找admin用户并重置密码
foreach ($users as $key => $user) {
    if ($user['username'] === 'admin') {
        $users[$key]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        $adminFound = true;
        break;
    }
}

// 如果没有找到admin用户，创建一个新的
if (!$adminFound) {
    $users[] = [
        'username' => 'admin',
        'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        'role' => 'admin'
    ];
}

// 保存更新后的用户数据
file_put_contents(USERS_DATA_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 输出结果
echo "管理员密码已重置！<br>";
echo "用户名: admin<br>";
echo "密码: $newPassword<br>";
echo "请立即使用这些凭据登录，并在登录后删除此文件。";
?> 