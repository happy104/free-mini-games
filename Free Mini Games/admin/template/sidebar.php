<div class="sidebar">
    <div class="logo">
        <a href="../index.html" target="_blank">
            <h2>Free Mini Games</h2>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li>
                <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-home"></i> <span>仪表盘</span>
                </a>
            </li>
            <li>
                <a href="add-game.php" <?php echo basename($_SERVER['PHP_SELF']) == 'add-game.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-plus"></i> <span>添加游戏</span>
                </a>
            </li>
            <li>
                <a href="manage-games.php" <?php echo basename($_SERVER['PHP_SELF']) == 'manage-games.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-gamepad"></i> <span>管理游戏</span>
                </a>
            </li>
            <li>
                <a href="updateHomepageFeaturedGames.php" <?php echo basename($_SERVER['PHP_SELF']) == 'updateHomepageFeaturedGames.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-sync"></i> <span>更新首页</span>
                </a>
            </li>
            <li>
                <a href="update-all-games.php" <?php echo basename($_SERVER['PHP_SELF']) == 'update-all-games.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-list"></i> <span>更新所有游戏</span>
                </a>
            </li>
            <li>
                <a href="settings.php" <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'class="active"' : ''; ?>>
                    <i class="fas fa-cog"></i> <span>设置</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>退出登录</span></a>
    </div>
</div>