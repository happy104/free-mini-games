<?php
// 创建目录（如果不存在）
$dataDir = __DIR__ . '/admin/data';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// 创建新的管理员账号
$user = [
    [
        'username' => 'admin2',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'admin'
    ]
];

// 保存到文件
file_put_contents($dataDir . '/users.json', json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "新管理员账号已创建！<br>";
echo "用户名: admin2<br>";
echo "密码: 123456<br>";
echo "请使用这些凭据登录，然后删除此文件。";
?>